<?php
/**
 * DadsFam SEO — Instant Indexing
 *
 * FREE:
 *   IndexNow — instantly notifies Bing, Yandex, DuckDuckGo, Seznam, Naver…
 *   Auto-submit on publish/update/delete.
 *   Manual bulk URL submission.
 *
 * PREMIUM:
 *   Google Indexing API (service-account JSON) for direct Google notification.
 *   A simple Google API key can also be stored for future use.
 *
 * History stores { url, time, google_response, bing_response } per entry.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Indexing {

	const OPT_INDEXNOW_KEY  = 'dfseo_indexnow_key';
	const OPT_GOOGLE_KEY    = 'dfseo_google_indexing_key';   // simple API key
	const OPT_GOOGLE_JSON   = 'dfseo_google_indexing_json';  // service account JSON (premium)
	const OPT_AUTO_SUBMIT   = 'dfseo_indexing_auto_submit';
	const OPT_LOG           = 'dfseo_indexing_log';
	const LOG_LIMIT         = 100;
	const INDEXNOW_ENDPOINT = 'https://api.indexnow.org/indexnow';

	public function __construct() {
		add_action( 'template_redirect',                    [ $this, 'serve_verification_file' ] );
		add_action( 'transition_post_status',               [ $this, 'on_status_change' ], 10, 3 );
		add_action( 'before_delete_post',                   [ $this, 'on_delete_post' ] );
		add_action( 'rest_api_init',                        [ $this, 'register_rest' ] );
		add_action( 'wp_ajax_dfseo_generate_indexnow_key',  [ $this, 'ajax_generate_key' ] );
		add_action( 'wp_ajax_dfseo_bulk_index_submit',      [ $this, 'ajax_bulk_submit' ] );
		add_action( 'wp_ajax_dfseo_clear_indexing_log',     [ $this, 'ajax_clear_log' ] );

		if ( ! get_option( self::OPT_INDEXNOW_KEY ) ) {
			update_option( self::OPT_INDEXNOW_KEY, $this->generate_key() );
		}
	}

	// ─── Key helpers ─────────────────────────────────────────────────────────

	public function generate_key(): string {
		return substr( bin2hex( random_bytes( 32 ) ), 0, 32 );
	}

	public function get_key(): string {
		return (string) get_option( self::OPT_INDEXNOW_KEY, '' );
	}

	public function serve_verification_file(): void {
		$key  = $this->get_key();
		$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
		if ( empty( $key ) || $path !== $key . '.txt' ) return;
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo esc_html( $key );
		exit;
	}

	// ─── Auto-submit on publish / delete ──────────────────────────────────────

	public function on_status_change( string $new, string $old, WP_Post $post ): void {
		if ( get_option( self::OPT_AUTO_SUBMIT, '1' ) !== '1' ) return;
		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) return;
		if ( ! in_array( $post->post_type, $this->watched_types(), true ) ) return;
		$url = get_permalink( $post );
		if ( ! $url ) return;
		if ( $new === 'publish' ) {
			$this->submit( [ $url ], 'URL_UPDATED' );
		} elseif ( $old === 'publish' && $new !== 'publish' ) {
			$this->submit( [ $url ], 'URL_DELETED' );
		}
	}

	public function on_delete_post( int $post_id ): void {
		if ( get_option( self::OPT_AUTO_SUBMIT, '1' ) !== '1' ) return;
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, $this->watched_types(), true ) ) return;
		if ( $post->post_status !== 'publish' ) return;
		$url = get_permalink( $post );
		if ( $url ) $this->submit( [ $url ], 'URL_DELETED' );
	}

	private function watched_types(): array {
		return array_filter( get_post_types( [ 'public' => true ] ), fn( $t ) => $t !== 'attachment' );
	}

	// ─── Core submission (handles both engines, logs per-URL) ─────────────────

	/**
	 * Submit one or more URLs to selected engines.
	 * Returns array of results per URL.
	 *
	 * @param string[] $urls
	 * @param string   $type  'URL_UPDATED' or 'URL_DELETED'
	 * @param bool     $do_google  submit to Google API
	 * @param bool     $do_bing    submit to Bing/IndexNow
	 */
	public function submit( array $urls, string $type = 'URL_UPDATED', bool $do_google = true, bool $do_bing = true ): array {
		$results = [];
		foreach ( $urls as $url ) {
			$url  = esc_url_raw( trim( $url ) );
			if ( ! $url ) continue;

			$g_result = 'N/A';
			$b_result = 'N/A';

			if ( $do_google && dfseo_is_premium() ) {
				$gr       = $this->submit_to_google( $url, $type );
				$g_result = isset( $gr['code'] ) ? (string) $gr['code'] : ( $gr['error'] ?? 'error' );
			}

			if ( $do_bing ) {
				$br       = $this->submit_to_indexnow( $url );
				$b_result = isset( $br['code'] ) ? (string) $br['code'] : 'error';
			}

			$this->log( $url, $g_result, $b_result );
			$results[] = compact( 'url', 'g_result', 'b_result' );
		}
		return $results;
	}

	// ─── IndexNow (Bing + network) ────────────────────────────────────────────

	public function submit_to_indexnow( string $url ): array {
		$key     = $this->get_key();
		$key_url = home_url( '/' . $key . '.txt' );

		$response = wp_remote_get(
			add_query_arg( [
				'url'         => rawurlencode( $url ),
				'key'         => $key,
				'keyLocation' => rawurlencode( $key_url ),
			], self::INDEXNOW_ENDPOINT ),
			[ 'timeout' => 8 ]
		);

		$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );

		// Also ping Bing directly (non-blocking)
		wp_remote_get(
			add_query_arg( [
				'url'         => rawurlencode( $url ),
				'key'         => $key,
				'keyLocation' => rawurlencode( $key_url ),
			], 'https://www.bing.com/indexnow' ),
			[ 'timeout' => 5, 'blocking' => false ]
		);

		return [ 'code' => $code, 'success' => ( $code === 200 || $code === 202 ) ];
	}

	// ─── Google Indexing API (PREMIUM) ────────────────────────────────────────

	public function submit_to_google( string $url, string $type = 'URL_UPDATED' ): array {
		$simple_key = get_option( self::OPT_GOOGLE_KEY, '' );
		$json_key   = get_option( self::OPT_GOOGLE_JSON, '' );

		// If we only have a simple API key, attempt it (mirrors SiteSEO behaviour — may return N/A for most pages)
		if ( empty( $json_key ) && ! empty( $simple_key ) ) {
			$response = wp_remote_post(
				'https://indexing.googleapis.com/v3/urlNotifications:publish?key=' . urlencode( $simple_key ),
				[ 'timeout' => 10, 'headers' => [ 'Content-Type' => 'application/json' ],
				  'body' => wp_json_encode( [ 'url' => $url, 'type' => $type ] ) ]
			);
			$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
			return [ 'code' => $code, 'success' => $code === 200, 'body' => null ];
		}

		if ( empty( $json_key ) ) {
			return [ 'code' => null, 'error' => 'N/A', 'success' => false ];
		}

		$token = $this->google_access_token( $json_key );
		if ( ! $token ) {
			return [ 'code' => null, 'error' => 'auth_failed', 'success' => false ];
		}

		$response = wp_remote_post(
			'https://indexing.googleapis.com/v3/urlNotifications:publish',
			[
				'timeout' => 10,
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				],
				'body' => wp_json_encode( [ 'url' => $url, 'type' => $type ] ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return [ 'code' => 0, 'error' => $response->get_error_message(), 'success' => false ];
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		return [ 'code' => $code, 'success' => $code === 200, 'body' => json_decode( wp_remote_retrieve_body( $response ), true ) ];
	}

	private function google_access_token( string $json_key_raw ): ?string {
		$key = json_decode( $json_key_raw, true );
		if ( ! is_array( $key ) || empty( $key['private_key'] ) || empty( $key['client_email'] ) ) return null;
		if ( ! function_exists( 'openssl_sign' ) ) return null;

		$now    = time();
		$header = $this->base64url( (string) wp_json_encode( [ 'alg' => 'RS256', 'typ' => 'JWT' ] ) );
		$claims = $this->base64url( (string) wp_json_encode( [
			'iss'   => $key['client_email'],
			'scope' => 'https://www.googleapis.com/auth/indexing',
			'aud'   => 'https://oauth2.googleapis.com/token',
			'exp'   => $now + 3600,
			'iat'   => $now,
		] ) );

		$sig = '';
		if ( ! @openssl_sign( "$header.$claims", $sig, $key['private_key'], OPENSSL_ALGO_SHA256 ) ) return null;
		$jwt = "$header.$claims." . $this->base64url( $sig );

		$response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
			'timeout' => 10,
			'body'    => [ 'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt ],
		] );
		if ( is_wp_error( $response ) ) return null;
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return $body['access_token'] ?? null;
	}

	private function base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	// ─── Log ─────────────────────────────────────────────────────────────────

	private function log( string $url, string $google_response, string $bing_response ): void {
		$log = (array) get_option( self::OPT_LOG, [] );
		array_unshift( $log, [
			'url'             => $url,
			'time'            => current_time( 'timestamp' ),
			'google_response' => $google_response,
			'bing_response'   => $bing_response,
		] );
		update_option( self::OPT_LOG, array_slice( $log, 0, self::LOG_LIMIT ) );
	}

	public function get_log(): array {
		return (array) get_option( self::OPT_LOG, [] );
	}

	// ─── AJAX handlers ───────────────────────────────────────────────────────

	public function ajax_generate_key(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
		$key = $this->generate_key();
		update_option( self::OPT_INDEXNOW_KEY, $key );
		wp_send_json_success( [ 'key' => $key, 'file_url' => home_url( '/' . $key . '.txt' ) ] );
	}

	public function ajax_bulk_submit(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$raw       = sanitize_textarea_field( wp_unslash( $_POST['urls'] ?? '' ) );
		$urls      = array_filter( array_map( 'esc_url_raw', array_map( 'trim', explode( "\n", $raw ) ) ) );
		$type      = sanitize_text_field( $_POST['action_type'] ?? 'URL_UPDATED' );
		$do_google = ! empty( $_POST['do_google'] );
		$do_bing   = ! empty( $_POST['do_bing'] );

		if ( empty( $urls ) ) {
			wp_send_json_error( [ 'message' => __( 'No valid URLs provided.', 'dadsfam-seo' ) ] );
		}

		$results = $this->submit( $urls, $type, $do_google, $do_bing );
		wp_send_json_success( [ 'results' => $results, 'count' => count( $results ) ] );
	}

	public function ajax_clear_log(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
		delete_option( self::OPT_LOG );
		wp_send_json_success();
	}

	// ─── REST ────────────────────────────────────────────────────────────────

	public function register_rest(): void {
		register_rest_route( 'dfseo/v1', '/indexing/log', [
			'methods'             => 'GET',
			'callback'            => fn() => new WP_REST_Response( $this->get_log(), 200 ),
			'permission_callback' => fn() => current_user_can( 'manage_options' ),
		] );
	}
}
