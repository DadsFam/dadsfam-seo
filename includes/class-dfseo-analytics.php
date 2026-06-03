<?php
/**
 * DadsFam SEO — Analytics (premium)
 *
 * Tracks organic traffic from referrer headers, stores per-post per-day data,
 * provides trend comparison, engine breakdown, and content decay detection.
 *
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DFSEO_Analytics {

	const OPT_ENGINE_STATS = 'dfseo_engine_breakdown';

	// Search engine domain → display name map
	const ENGINES = [
		'google'     => 'Google',
		'bing'       => 'Bing',
		'yahoo'      => 'Yahoo',
		'duckduckgo' => 'DuckDuckGo',
		'yandex'     => 'Yandex',
		'baidu'      => 'Baidu',
		'ecosia'     => 'Ecosia',
		'brave'      => 'Brave',
		'qwant'      => 'Qwant',
		'startpage'  => 'Startpage',
	];

	public function __construct() {
		add_action( 'wp',           [ $this, 'track_visit' ] );
		add_action( 'rest_api_init',[ $this, 'register_rest' ] );
	}

	// ─── Source detection ────────────────────────────────────────────────────

	private function detect_engine( string $referrer ): string {
		foreach ( self::ENGINES as $domain => $label ) {
			if ( preg_match( '~^https?://(?:[a-z0-9-]+\.)*' . preg_quote( $domain, '~' ) . '\.[a-z]~i', $referrer ) ) {
				return $label;
			}
		}
		return 'Other';
	}

	// ─── Track visit ─────────────────────────────────────────────────────────

	public function track_visit(): void {
		if ( ! is_singular() || ! dfseo_is_premium() ) return;
		if ( get_option( 'dfseo_track_analytics', '1' ) !== '1' ) return;

		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		if ( ! $referrer ) return;

		$is_organic = (bool) preg_match(
			'~^https?://(?:[a-z0-9-]+\.)*(?:' . implode( '|', array_keys( self::ENGINES ) ) . '|swisscows|mojeek|naver|seznam|sogou|ask|aol)\.[a-z]~i',
			$referrer
		);
		if ( ! $is_organic ) return;

		global $post, $wpdb;
		$table  = DFSEO_DB::table( 'analytics' );
		$today  = gmdate( 'Y-m-d' );
		$engine = $this->detect_engine( $referrer );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO `{$table}` (post_id, organic_clicks, impressions, date_recorded, created_at)
			 VALUES (%d, 1, 1, %s, NOW())
			 ON DUPLICATE KEY UPDATE organic_clicks = organic_clicks + 1, impressions = impressions + 1",
			$post->ID, $today
		) );

		// Store running engine breakdown (no extra DB table needed)
		$stats = (array) get_option( self::OPT_ENGINE_STATS, [] );
		$stats[ $engine ] = ( $stats[ $engine ] ?? 0 ) + 1;
		update_option( self::OPT_ENGINE_STATS, $stats, false );
	}

	// ─── REST endpoint ───────────────────────────────────────────────────────

	public function register_rest(): void {
		register_rest_route( 'dfseo/v1', '/analytics', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_get_analytics' ],
			'permission_callback' => fn() => current_user_can( 'manage_options' ),
		] );
	}

	public function rest_get_analytics( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );

		global $wpdb;
		$table = DFSEO_DB::table( 'analytics' );
		$days  = (int)( $r['days'] ?? 30 );
		$days  = in_array( $days, [ 7, 14, 30, 60, 90 ], true ) ? $days : 30;

		// ── Current period timeline ──────────────────────────────────────────
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$timeline = $wpdb->get_results( $wpdb->prepare(
			"SELECT date_recorded, SUM(organic_clicks) AS clicks, SUM(impressions) AS impressions
			 FROM `{$table}`
			 WHERE date_recorded >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			 GROUP BY date_recorded ORDER BY date_recorded ASC",
			$days
		), ARRAY_A );

		// ── Previous period totals (for trend arrows) ────────────────────────
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$prev = $wpdb->get_row( $wpdb->prepare(
			"SELECT SUM(organic_clicks) AS clicks, SUM(impressions) AS impressions
			 FROM `{$table}`
			 WHERE date_recorded >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			   AND date_recorded <  DATE_SUB(CURDATE(), INTERVAL %d DAY)",
			$days * 2, $days
		), ARRAY_A );

		// ── Top posts ────────────────────────────────────────────────────────
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$top_posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, SUM(organic_clicks) AS clicks FROM `{$table}`
			 WHERE date_recorded >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			 GROUP BY post_id ORDER BY clicks DESC LIMIT 10",
			$days
		), ARRAY_A );
		foreach ( $top_posts as &$p ) {
			$p['title'] = get_the_title( (int) $p['post_id'] ) ?: __( '(Untitled)', 'dadsfam-seo' );
			$p['url']   = get_permalink( (int) $p['post_id'] );
		}

		// ── Content decay — posts with traffic drops >30% vs previous period ─
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$decay_current = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, SUM(organic_clicks) AS clicks FROM `{$table}`
			 WHERE date_recorded >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			 GROUP BY post_id HAVING clicks > 0",
			$days
		), ARRAY_A );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$decay_prev = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, SUM(organic_clicks) AS clicks FROM `{$table}`
			 WHERE date_recorded >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			   AND date_recorded <  DATE_SUB(CURDATE(), INTERVAL %d DAY)
			 GROUP BY post_id HAVING clicks > 0",
			$days * 2, $days
		), ARRAY_A );

		$prev_map = array_column( $decay_prev, 'clicks', 'post_id' );
		$decay    = [];
		foreach ( $decay_current as $row ) {
			$prev_clicks = (int)( $prev_map[ $row['post_id'] ] ?? 0 );
			if ( $prev_clicks > 0 ) {
				$change = ( ( (int)$row['clicks'] - $prev_clicks ) / $prev_clicks ) * 100;
				if ( $change < -30 ) {
					$decay[] = [
						'post_id' => $row['post_id'],
						'title'   => get_the_title( (int) $row['post_id'] ) ?: '(Untitled)',
						'url'     => get_permalink( (int) $row['post_id'] ),
						'current' => (int) $row['clicks'],
						'prev'    => $prev_clicks,
						'change'  => round( $change, 1 ),
					];
				}
			}
		}
		usort( $decay, fn( $a, $b ) => $a['change'] <=> $b['change'] );

		// ── Engine breakdown ─────────────────────────────────────────────────
		$engine_stats = (array) get_option( self::OPT_ENGINE_STATS, [] );
		arsort( $engine_stats );

		return new WP_REST_Response( [
			'timeline'       => $timeline ?: [],
			'top_posts'      => $top_posts ?: [],
			'decay'          => array_slice( $decay, 0, 5 ),
			'engine_stats'   => $engine_stats,
			'totals'         => [
				'clicks'      => array_sum( array_column( $timeline, 'clicks' ) ),
				'impressions' => array_sum( array_column( $timeline, 'impressions' ) ),
			],
			'prev_totals'    => [
				'clicks'      => (int)( $prev['clicks'] ?? 0 ),
				'impressions' => (int)( $prev['impressions'] ?? 0 ),
			],
			'days'           => $days,
		], 200 );
	}

	public static function prune_old_data(): void {
		global $wpdb;
		$table = DFSEO_DB::table( 'analytics' );
		$keep  = (int) get_option( 'dfseo_analytics_retention_days', 180 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM `{$table}` WHERE date_recorded < DATE_SUB(CURDATE(), INTERVAL %d DAY)",
			$keep
		) );
	}
}
