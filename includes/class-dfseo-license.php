<?php
/**
 * DadsFam SEO — License Manager Integration
 *
 * Matches the exact contract of dfem-license-manager-v2.3.3.php:
 *
 *  VERIFY  POST https://www.dadsfam.co.za/wp-json/dfem-licenses/v1/verify
 *          Body (JSON): { license_key, site_url, plugin_ver }
 *          Response:    { valid: bool, message, product, expires, lock_token }
 *
 *  FORCE-LOCK  POST /wp-json/dflm/v1/force-lock  (registered on THIS site)
 *              Body (form): { key }
 *              Action: clear cache immediately so premium gates drop.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_License {

	// Option / transient keys
	const OPTION_KEY        = 'dfseo_license_key';
	const OPTION_STATUS     = 'dfseo_license_status_data';
	const OPTION_LOCK_TOKEN  = 'dfseo_license_lock_token';
	const OPTION_LAST_CHECK  = 'dfseo_license_last_checked';
	const TRANSIENT_KEY     = 'dfseo_license_status';
	const TRANSIENT_HOURS   = 12;

	// ─── Boot ────────────────────────────────────────────────────────────────

	public function __construct() {
		add_action( 'admin_post_dfseo_activate_license',   [ $this, 'handle_activate' ] );
		add_action( 'admin_post_dfseo_deactivate_license', [ $this, 'handle_deactivate' ] );
		add_action( 'rest_api_init',                       [ $this, 'register_rest_routes' ] );
		add_action( 'wp_ajax_dfseo_recheck_license',       [ $this, 'ajax_recheck' ] );
	}

	// ─── AJAX: manual re-check ───────────────────────────────────────────────

	public function ajax_recheck(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'dadsfam-seo' ) ] );
		}

		$key = self::get_key();
		if ( empty( $key ) ) {
			wp_send_json_error( [ 'message' => __( 'No licence key on file.', 'dadsfam-seo' ) ] );
		}

		$result = $this->remote_verify( $key );

		// Network failure: do NOT change status — keep whatever we had
		if ( isset( $result['_network_error'] ) ) {
			wp_send_json_error( [
				'message'    => __( 'Could not reach the licence server right now. Your current status is unchanged — premium stays active.', 'dadsfam-seo' ),
				'network'    => true,
			] );
		}

		update_option( self::OPTION_STATUS,     $result );
		update_option( self::OPTION_LAST_CHECK, time() );
		$this->set_cache( $result );
		if ( ! empty( $result['lock_token'] ) ) {
			update_option( self::OPTION_LOCK_TOKEN, sanitize_text_field( $result['lock_token'] ) );
		}

		$valid = ! empty( $result['valid'] ) && $result['valid'] === true;
		wp_send_json_success( [
			'valid'   => $valid,
			'message' => $valid
				? __( 'Licence confirmed active ✓', 'dadsfam-seo' )
				: ( $result['message'] ?? __( 'Licence is not active.', 'dadsfam-seo' ) ),
		] );
	}

	// ─── REST: Force-Lock endpoint ───────────────────────────────────────────
	// The license server (dadsfam.co.za) calls this to instantly revoke access.
	// Path must match the fallback in dfem-license-manager ping_lock:
	//   else { $path = '/wp-json/dflm/v1/force-lock'; }

	public function register_rest_routes(): void {
		register_rest_route( 'dflm/v1', '/force-lock', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_force_lock' ],
			'permission_callback' => '__return_true', // Key verified inside handler
		] );
	}

	public function handle_force_lock( WP_REST_Request $request ): WP_REST_Response {
		$incoming_key  = sanitize_text_field( (string) ( $request->get_param( 'key' ) ?? '' ) );
		$stored_key    = self::get_key();
		$stored_token  = (string) get_option( self::OPTION_LOCK_TOKEN, '' );

		// Reject if the key doesn't match what we have stored
		if ( empty( $incoming_key ) || $incoming_key !== $stored_key ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Key mismatch.' ], 403 );
		}

		// Clear the license cache immediately — all premium gates will drop
		delete_transient( self::TRANSIENT_KEY );
		update_option( self::OPTION_STATUS, [ 'valid' => false, 'message' => 'License suspended by server.' ] );

		return new WP_REST_Response( [ 'success' => true, 'message' => 'License cache cleared. Premium features suspended.' ], 200 );
	}

	// ─── Premium check ───────────────────────────────────────────────────────

	public static function is_active(): bool {
		$status = self::get_cached_status();
		// Server returns { valid: true } on success
		return ! empty( $status['valid'] ) && $status['valid'] === true;
	}

	// ─── Activate (admin-post handler) ───────────────────────────────────────

	public function handle_activate(): void {
		check_admin_referer( 'dfseo_license_action' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
		}

		$key    = sanitize_text_field( wp_unslash( $_POST['dfseo_license_key'] ?? '' ) );
		$result = $this->remote_verify( $key );

		update_option( self::OPTION_KEY,        $key );
		update_option( self::OPTION_STATUS,     $result );
		update_option( self::OPTION_LAST_CHECK, time() );
		$this->set_cache( $result );

		if ( ! empty( $result['lock_token'] ) ) {
			update_option( self::OPTION_LOCK_TOKEN, sanitize_text_field( $result['lock_token'] ) );
		}

		if ( ! empty( $result['valid'] ) && $result['valid'] === true ) {
			wp_safe_redirect( add_query_arg( 'dfseo_notice', 'license_activated', admin_url( 'admin.php?page=dfseo-license' ) ) );
		} else {
			wp_safe_redirect( add_query_arg( [
				'dfseo_notice' => 'license_error',
				'dfseo_msg'    => rawurlencode( $result['message'] ?? __( 'Unknown error.', 'dadsfam-seo' ) ),
			], admin_url( 'admin.php?page=dfseo-license' ) ) );
		}
		exit;
	}

	// ─── Deactivate (admin-post handler) ─────────────────────────────────────

	public function handle_deactivate(): void {
		check_admin_referer( 'dfseo_license_action' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
		}

		delete_option( self::OPTION_KEY );
		delete_option( self::OPTION_STATUS );
		delete_option( self::OPTION_LOCK_TOKEN );
		delete_option( self::OPTION_LAST_CHECK );
		$this->clear_cache();

		wp_safe_redirect( add_query_arg( 'dfseo_notice', 'license_deactivated', admin_url( 'admin.php?page=dfseo-license' ) ) );
		exit;
	}

	// ─── Cron re-verify ──────────────────────────────────────────────────────

	public function verify_license_cron(): void {
		$key = self::get_key();
		if ( empty( $key ) ) {
			return;
		}
		$result = $this->remote_verify( $key );

		// Network errors should not lock out an active site — preserve existing status
		if ( isset( $result['_network_error'] ) ) {
			return;
		}

		update_option( self::OPTION_STATUS,     $result );
		update_option( self::OPTION_LAST_CHECK, time() );
		$this->set_cache( $result );

		if ( ! empty( $result['lock_token'] ) ) {
			update_option( self::OPTION_LOCK_TOKEN, sanitize_text_field( $result['lock_token'] ) );
		}
	}

	// ─── Remote verify call ───────────────────────────────────────────────────

	public function remote_verify( string $key ): array {
		if ( empty( $key ) ) {
			return [ 'valid' => false, 'message' => __( 'No license key provided.', 'dadsfam-seo' ) ];
		}

		$response = wp_remote_post(
			DFSEO_LICENSE_URL,
			[
				'timeout'     => 15,
				'sslverify'   => true,
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => wp_json_encode( [
					'license_key' => $key,
					'site_url'    => home_url(),
					'plugin_ver'  => DFSEO_VERSION,
				] ),
			]
		);

		if ( is_wp_error( $response ) ) {
			// Tag network errors so cron doesn't clear an active licence
			return [ 'valid' => false, 'message' => $response->get_error_message(), '_network_error' => true ];
		}

		// Server reachable but returned an error code (500, 503, 404, etc.) —
		// treat as transient so we NEVER deactivate on a server hiccup.
		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return [
				'valid'          => false,
				/* translators: %d: HTTP status code */
				'message'        => sprintf( __( 'Licence server returned status %d. Will retry automatically.', 'dadsfam-seo' ), $code ),
				'_network_error' => true,
			];
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Non-JSON / malformed response (HTML error page, empty body, etc.) —
		// also transient, never deactivate on it.
		if ( ! is_array( $body ) || ! isset( $body['valid'] ) ) {
			return [
				'valid'          => false,
				'message'        => __( 'Unexpected response from licence server. Will retry automatically.', 'dadsfam-seo' ),
				'_network_error' => true,
			];
		}

		// Reject keys registered for a different DadsFam product
		if ( ! empty( $body['product'] ) && $body['product'] !== DFSEO_PRODUCT_CODE ) {
			return [ 'valid' => false, 'message' => __( 'This key belongs to a different DadsFam product.', 'dadsfam-seo' ) ];
		}

		return $body;
	}

	// ─── Cache helpers ────────────────────────────────────────────────────────

	private static function get_cached_status(): array {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		// Re-hydrate from DB option (avoids a network call on every page load)
		$stored = get_option( self::OPTION_STATUS, [] );
		if ( is_array( $stored ) && ! empty( $stored ) ) {
			set_transient( self::TRANSIENT_KEY, $stored, HOUR_IN_SECONDS * self::TRANSIENT_HOURS );
			return $stored;
		}
		return [ 'valid' => false ];
	}

	private function set_cache( array $data ): void {
		set_transient( self::TRANSIENT_KEY, $data, HOUR_IN_SECONDS * self::TRANSIENT_HOURS );
	}

	private function clear_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
	}

	// ─── Info helpers ─────────────────────────────────────────────────────────

	public static function get_check_info(): array {
		$last = (int) get_option( self::OPTION_LAST_CHECK, 0 );
		$next = (int) wp_next_scheduled( 'dfseo_license_cron' );
		return [
			'last_ts'    => $last,
			'last_date'  => $last ? wp_date( 'd M Y H:i', $last )           : __( 'Not yet checked', 'dadsfam-seo' ),
			'last_human' => $last ? human_time_diff( $last ) . ' ago'        : __( 'Not yet checked', 'dadsfam-seo' ),
			'next_ts'    => $next,
			'next_date'  => $next ? wp_date( 'd M Y H:i', $next )           : __( 'Not scheduled', 'dadsfam-seo' ),
			'next_human' => $next ? 'in ' . human_time_diff( $next )         : __( 'Not scheduled', 'dadsfam-seo' ),
			'cron_ok'    => $next > 0,
		];
	}

	public static function get_key(): string {
		return (string) get_option( self::OPTION_KEY, '' );
	}

	public static function get_status_data(): array {
		return (array) get_option( self::OPTION_STATUS, [] );
	}

	public static function masked_key(): string {
		$key = self::get_key();
		if ( strlen( $key ) < 12 ) {
			return $key;
		}
		return substr( $key, 0, 4 ) . str_repeat( '•', max( 0, strlen( $key ) - 8 ) ) . substr( $key, -4 );
	}
}
