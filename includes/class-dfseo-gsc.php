<?php
/**
 * DadsFam SEO — Google Search Console integration (premium)
 *
 * Pulls REAL keyword data (queries, clicks, impressions, CTR, position)
 * from the Search Console API using the same service account JSON the
 * Instant Indexing feature stores. One credential, two features.
 *
 * Setup (same service account as Google Indexing):
 *   1. Google Cloud Console → enable "Google Search Console API"
 *   2. Search Console → Settings → Users → add service account email as Owner
 *   3. Paste the JSON in Settings → Instant Indexing → Google API
 *
 * Results cached for 6 hours per range to stay well within API quotas.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_GSC {

	const SCOPE       = 'https://www.googleapis.com/auth/webmasters.readonly';
	const CACHE_GROUP = 'dfseo_gsc_';
	const CACHE_TTL   = 6 * HOUR_IN_SECONDS;

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	public function register_rest(): void {
		register_rest_route( 'dfseo/v1', '/gsc/keywords', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_keywords' ],
			'permission_callback' => fn() => current_user_can( 'manage_options' ),
		] );
	}

	/**
	 * Is the service account configured?
	 */
	public function is_configured(): bool {
		$json = get_option( DFSEO_Indexing::OPT_GOOGLE_JSON, '' );
		if ( empty( $json ) ) return false;
		$key = json_decode( $json, true );
		return is_array( $key ) && ! empty( $key['private_key'] ) && ! empty( $key['client_email'] );
	}

	/**
	 * REST: GET /dfseo/v1/gsc/keywords?days=30
	 * Returns top queries with clicks/impressions/ctr/position.
	 */
	public function rest_keywords( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) {
			return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		}
		if ( ! $this->is_configured() ) {
			return new WP_REST_Response( [ 'error' => 'not_configured' ], 200 );
		}

		$days = (int) ( $r['days'] ?? 30 );
		$days = in_array( $days, [ 7, 14, 30, 60, 90 ], true ) ? $days : 30;

		// Serve from cache when fresh
		$cache_key = self::CACHE_GROUP . 'kw_' . $days;
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			$cached['cached'] = true;
			return new WP_REST_Response( $cached, 200 );
		}

		$token = $this->access_token();
		if ( ! $token ) {
			return new WP_REST_Response( [ 'error' => 'auth_failed' ], 200 );
		}

		$result = $this->query_search_analytics( $token, $days );
		if ( isset( $result['error'] ) ) {
			return new WP_REST_Response( $result, 200 );
		}

		set_transient( $cache_key, $result, self::CACHE_TTL );
		$result['cached'] = false;
		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Query the Search Analytics API for top queries + top pages.
	 */
	private function query_search_analytics( string $token, int $days ): array {
		$site_url = $this->site_url_candidates();
		$start    = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
		$end      = gmdate( 'Y-m-d' );

		foreach ( $site_url as $candidate ) {
			$endpoint = 'https://searchconsole.googleapis.com/webmasters/v3/sites/'
				. rawurlencode( $candidate ) . '/searchAnalytics/query';

			// ── Top queries ─────────────────────────────────────────────
			$queries = $this->api_post( $endpoint, $token, [
				'startDate'  => $start,
				'endDate'    => $end,
				'dimensions' => [ 'query' ],
				'rowLimit'   => 25,
			] );

			if ( isset( $queries['error_code'] ) && in_array( $queries['error_code'], [ 403, 404 ], true ) ) {
				continue; // try next site URL format
			}

			// ── Top pages ───────────────────────────────────────────────
			$pages = $this->api_post( $endpoint, $token, [
				'startDate'  => $start,
				'endDate'    => $end,
				'dimensions' => [ 'page' ],
				'rowLimit'   => 10,
			] );

			$fmt = function ( array $rows, string $key_name ): array {
				$out = [];
				foreach ( $rows as $row ) {
					$out[] = [
						$key_name     => $row['keys'][0] ?? '',
						'clicks'      => (int) ( $row['clicks'] ?? 0 ),
						'impressions' => (int) ( $row['impressions'] ?? 0 ),
						'ctr'         => round( (float) ( $row['ctr'] ?? 0 ) * 100, 1 ),
						'position'    => round( (float) ( $row['position'] ?? 0 ), 1 ),
					];
				}
				return $out;
			};

			return [
				'site'     => $candidate,
				'days'     => $days,
				'keywords' => $fmt( $queries['rows'] ?? [], 'query' ),
				'pages'    => $fmt( $pages['rows'] ?? [], 'page' ),
			];
		}

		return [ 'error' => 'site_not_found', 'tried' => $site_url ];
	}

	/**
	 * GSC properties can be url-prefix (https://www.example.com/) or
	 * domain properties (sc-domain:example.com) — try both.
	 */
	private function site_url_candidates(): array {
		$home   = trailingslashit( home_url() );
		$host   = wp_parse_url( $home, PHP_URL_HOST );
		$domain = preg_replace( '/^www\./', '', (string) $host );
		return [
			'sc-domain:' . $domain,
			$home,
			str_replace( '://www.', '://', $home ),
		];
	}

	private function api_post( string $url, string $token, array $body ): array {
		$response = wp_remote_post( $url, [
			'timeout' => 15,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $token,
			],
			'body'    => wp_json_encode( $body ),
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'error_code' => 0, 'rows' => [] ];
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			return [ 'error_code' => $code, 'rows' => [] ];
		}
		return is_array( $data ) ? $data : [ 'rows' => [] ];
	}

	/**
	 * OAuth2 access token via service-account JWT (webmasters.readonly scope).
	 */
	private function access_token(): ?string {
		// Token cached ~50 min (expires at 60)
		$cached = get_transient( self::CACHE_GROUP . 'token' );
		if ( is_string( $cached ) && $cached !== '' ) return $cached;

		$key = json_decode( (string) get_option( DFSEO_Indexing::OPT_GOOGLE_JSON, '' ), true );
		if ( ! is_array( $key ) || empty( $key['private_key'] ) || empty( $key['client_email'] ) ) return null;
		if ( ! function_exists( 'openssl_sign' ) ) return null;

		$now    = time();
		$header = $this->base64url( (string) wp_json_encode( [ 'alg' => 'RS256', 'typ' => 'JWT' ] ) );
		$claims = $this->base64url( (string) wp_json_encode( [
			'iss'   => $key['client_email'],
			'scope' => self::SCOPE,
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
		$body  = json_decode( wp_remote_retrieve_body( $response ), true );
		$token = $body['access_token'] ?? null;

		if ( $token ) set_transient( self::CACHE_GROUP . 'token', $token, 50 * MINUTE_IN_SECONDS );
		return $token;
	}

	private function base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}
}
