<?php
/**
 * Redirect Manager — 301/302/307 redirects + 404 monitoring (premium).
 *
 * Free:    Basic 301 redirect processing from options.
 * Premium: Full redirect manager UI, 404 log, regex redirects, bulk import.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Redirects {

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'process_redirects' ], 1 );
		add_action( 'rest_api_init',     [ $this, 'register_rest' ] );
	}

	// ─── Process redirects ───────────────────────────────────────────────────

	public function process_redirects(): void {
		global $wpdb;

		// Only on 404s or for defined redirects
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? wp_parse_url( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH )
			: '';

		if ( empty( $request_uri ) ) return;

		// 404 logging (always)
		if ( is_404() && get_option( 'dfseo_track_404', '1' ) === '1' ) {
			$this->log_404( $request_uri );
		}

		if ( get_option( 'dfseo_redirect_enable', '1' ) !== '1' ) return;

		// Look up redirect
		$table = DFSEO_DB::table( 'redirects' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$redirect = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE source_url = %s AND enabled = 1 LIMIT 1",
			$request_uri
		) );

		if ( ! $redirect && ! dfseo_is_premium() ) return;

		if ( ! $redirect && dfseo_is_premium() ) {
			// Try regex matches (premium)
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$redirects = $wpdb->get_results( "SELECT * FROM `{$table}` WHERE source_url LIKE '^%' AND enabled = 1", ARRAY_A );
			foreach ( (array) $redirects as $r ) {
				if ( @preg_match( '#' . $r['source_url'] . '#', $request_uri ) ) {
					$redirect = (object) $r;
					break;
				}
			}
		}

		if ( ! $redirect ) return;

		// Hit count
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET hit_count = hit_count + 1 WHERE id = %d", $redirect->id ) );

		$type = in_array( (int) $redirect->redirect_type, [ 301, 302, 307, 308, 410 ], true ) ? (int) $redirect->redirect_type : 301;

		if ( $type === 410 ) {
			status_header( 410 );
			exit;
		}

		wp_redirect( $redirect->target_url, $type );
		exit;
	}

	// ─── 404 logging ────────────────────────────────────────────────────────

	private function log_404( string $url ): void {
		global $wpdb;
		$table    = DFSEO_DB::table( '404_log' );
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		$ua       = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$ip_hash  = hash( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE url = %s LIMIT 1", $url ) );

		if ( $existing ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET hit_count = hit_count + 1, last_seen = NOW() WHERE id = %d", $existing ) );
		} else {
			$wpdb->insert( $table, [
				'url'        => substr( $url, 0, 2048 ),
				'referrer'   => substr( $referrer, 0, 2048 ),
				'user_agent' => substr( $ua, 0, 512 ),
				'ip_hash'    => $ip_hash,
				'hit_count'  => 1,
				'created_at' => current_time( 'mysql' ),
				'last_seen'  => current_time( 'mysql' ),
			] );
		}
	}

	// ─── REST endpoints ──────────────────────────────────────────────────────

	public function register_rest(): void {
		$cap = 'manage_options';

		register_rest_route( 'dfseo/v1', '/redirects', [
			[ 'methods' => 'GET',  'callback' => [ $this, 'rest_get_redirects' ],    'permission_callback' => fn() => current_user_can( $cap ) ],
			[ 'methods' => 'POST', 'callback' => [ $this, 'rest_create_redirect' ],  'permission_callback' => fn() => current_user_can( $cap ) ],
		] );
		register_rest_route( 'dfseo/v1', '/redirects/(?P<id>\d+)', [
			[ 'methods' => 'PUT',    'callback' => [ $this, 'rest_update_redirect' ], 'permission_callback' => fn() => current_user_can( $cap ) ],
			[ 'methods' => 'DELETE', 'callback' => [ $this, 'rest_delete_redirect' ], 'permission_callback' => fn() => current_user_can( $cap ) ],
		] );
		register_rest_route( 'dfseo/v1', '/404-log', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_get_404_log' ],
			'permission_callback' => fn() => current_user_can( $cap ),
		] );
	}

	public function rest_get_redirects( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		$data = DFSEO_DB::paginate( DFSEO_DB::table( 'redirects' ), [], (int)( $r['per_page'] ?? 20 ), (int)( $r['page'] ?? 1 ), 'id', 'DESC' );
		return new WP_REST_Response( $data, 200 );
	}

	public function rest_create_redirect( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		global $wpdb;
		$table = DFSEO_DB::table( 'redirects' );
		$source = sanitize_text_field( (string)( $r['source_url'] ?? '' ) );
		$target = sanitize_text_field( (string)( $r['target_url'] ?? '' ) );
		$type   = (int)( $r['redirect_type'] ?? 301 );
		if ( ! $source || ! $target ) return new WP_REST_Response( [ 'error' => 'source_url and target_url required' ], 422 );
		$wpdb->insert( $table, [
			'source_url'    => $source,
			'target_url'    => $target,
			'redirect_type' => $type,
			'enabled'       => 1,
			'note'          => sanitize_text_field( (string)( $r['note'] ?? '' ) ),
			'created_at'    => current_time( 'mysql' ),
			'updated_at'    => current_time( 'mysql' ),
		] );
		return new WP_REST_Response( [ 'id' => $wpdb->insert_id ], 201 );
	}

	public function rest_update_redirect( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		global $wpdb;
		$table = DFSEO_DB::table( 'redirects' );
		$id    = (int) $r['id'];
		$data  = array_filter( [
			'source_url'    => isset( $r['source_url'] )    ? sanitize_text_field( (string)$r['source_url'] )    : null,
			'target_url'    => isset( $r['target_url'] )    ? sanitize_text_field( (string)$r['target_url'] )    : null,
			'redirect_type' => isset( $r['redirect_type'] ) ? (int)$r['redirect_type']                           : null,
			'enabled'       => isset( $r['enabled'] )       ? (int)(bool)$r['enabled']                           : null,
			'note'          => isset( $r['note'] )          ? sanitize_text_field( (string)$r['note'] )          : null,
			'updated_at'    => current_time( 'mysql' ),
		], fn($v) => $v !== null );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update( $table, $data, [ 'id' => $id ] );
		return new WP_REST_Response( [ 'updated' => true ], 200 );
	}

	public function rest_delete_redirect( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( DFSEO_DB::table( 'redirects' ), [ 'id' => (int)$r['id'] ] );
		return new WP_REST_Response( [ 'deleted' => true ], 200 );
	}

	public function rest_get_404_log( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		$data = DFSEO_DB::paginate( DFSEO_DB::table( '404_log' ), [], 50, (int)( $r['page'] ?? 1 ), 'hit_count', 'DESC' );
		return new WP_REST_Response( $data, 200 );
	}

	// ─── Cleanup old 404 log entries (runs daily) ───────────────────────────

	public static function cleanup_404_log(): void {
		global $wpdb;
		$table = DFSEO_DB::table( '404_log' );
		$days  = (int) get_option( 'dfseo_404_log_retention_days', 90 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE last_seen < DATE_SUB(NOW(), INTERVAL %d DAY) AND hit_count = 1", $days ) );
	}
}
