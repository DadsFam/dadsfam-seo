<?php
/**
 * Import SEO data from Yoast SEO, Rank Math, All in One SEO (premium).
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DFSEO_Import {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	public function register_rest(): void {
		register_rest_route( 'dfseo/v1', '/import/(?P<source>[a-z_]+)', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'rest_import' ],
			'permission_callback' => fn() => current_user_can( 'manage_options' ),
		] );
	}

	public function rest_import( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		$source = sanitize_key( $r['source'] );
		$offset = (int)( $r['offset'] ?? 0 );
		$limit  = 50;
		switch ( $source ) {
			case 'yoast':           return $this->import_yoast( $offset, $limit );
			case 'rankmath':        return $this->import_rankmath( $offset, $limit );
			case 'aioseo':          return $this->import_aioseo( $offset, $limit );
			case 'seopress':        return $this->import_seopress( $offset, $limit );
			case 'theseoframework': return $this->import_theseoframework( $offset, $limit );
			case 'slimsseo':        return $this->import_slimsseo( $offset, $limit );
			case 'siteseo':         return $this->import_siteseo( $offset, $limit );
			default:                return new WP_REST_Response( [ 'error' => 'Unknown source' ], 400 );
		}
	}

	private function import_yoast( int $offset, int $limit ): WP_REST_Response {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_yoast_wpseo_focuskw'
			 WHERE p.post_status = 'publish' LIMIT %d OFFSET %d",
			$limit, $offset
		), ARRAY_A );
		$imported = 0;
		foreach ( $posts as $row ) {
			$id = (int)$row['ID'];
			$kw    = get_post_meta( $id, '_yoast_wpseo_focuskw',                      true );
			$title = get_post_meta( $id, '_yoast_wpseo_title',                        true );
			$desc  = get_post_meta( $id, '_yoast_wpseo_metadesc',                     true );
			$noind = get_post_meta( $id, '_yoast_wpseo_meta-robots-noindex',          true );
			if ( $kw )    update_post_meta( $id, '_dfseo_focus_keyword', sanitize_text_field( $kw ) );
			if ( $title ) update_post_meta( $id, '_dfseo_title',         sanitize_text_field( $title ) );
			if ( $desc )  update_post_meta( $id, '_dfseo_meta_desc',     sanitize_textarea_field( $desc ) );
			if ( $noind ) update_post_meta( $id, '_dfseo_noindex',       '1' );
			$imported++;
		}
		return new WP_REST_Response( [ 'imported' => $imported, 'has_more' => count($posts) === $limit ], 200 );
	}

	private function import_rankmath( int $offset, int $limit ): WP_REST_Response {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_focus_keyword'
			 WHERE p.post_status = 'publish' LIMIT %d OFFSET %d",
			$limit, $offset
		), ARRAY_A );
		$imported = 0;
		foreach ( $posts as $row ) {
			$id = (int)$row['ID'];
			$kw     = get_post_meta( $id, 'rank_math_focus_keyword', true );
			$title  = get_post_meta( $id, 'rank_math_title',         true );
			$desc   = get_post_meta( $id, 'rank_math_description',   true );
			$robots = get_post_meta( $id, 'rank_math_robots',        true );
			if ( $kw )    update_post_meta( $id, '_dfseo_focus_keyword', sanitize_text_field( $kw ) );
			if ( $title ) update_post_meta( $id, '_dfseo_title',         sanitize_text_field( $title ) );
			if ( $desc )  update_post_meta( $id, '_dfseo_meta_desc',     sanitize_textarea_field( $desc ) );
			if ( is_array( $robots ) && in_array( 'noindex', $robots ) ) update_post_meta( $id, '_dfseo_noindex', '1' );
			$imported++;
		}
		return new WP_REST_Response( [ 'imported' => $imported, 'has_more' => count($posts) === $limit ], 200 );
	}

	private function import_aioseo( int $offset, int $limit ): WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'aioseo_posts';
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) {
			return new WP_REST_Response( [ 'error' => 'AIOSEO table not found' ], 404 );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id, title, description, keywords FROM `{$table}` WHERE post_id > 0 LIMIT %d OFFSET %d",
			$limit, $offset
		), ARRAY_A );
		$imported = 0;
		foreach ( $posts as $row ) {
			$id = (int)$row['post_id'];
			if ( $row['title'] )       update_post_meta( $id, '_dfseo_title',         sanitize_text_field( $row['title'] ) );
			if ( $row['description'] ) update_post_meta( $id, '_dfseo_meta_desc',     sanitize_textarea_field( $row['description'] ) );
			if ( $row['keywords'] ) {
				$kws = json_decode( $row['keywords'], true );
				$kw  = is_array( $kws ) ? sanitize_text_field( $kws[0] ?? '' ) : sanitize_text_field( $row['keywords'] );
				if ( $kw ) update_post_meta( $id, '_dfseo_focus_keyword', $kw );
			}
			$imported++;
		}
		return new WP_REST_Response( [ 'imported' => $imported, 'has_more' => count($posts) === $limit ], 200 );
	}
}
