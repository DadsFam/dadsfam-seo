<?php
/**
 * Local SEO — stores business information used in LocalBusiness schema (premium).
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DFSEO_Local_Seo {
	public function __construct() {
		// Local SEO settings are registered via DFSEO_Settings.
		// This class exists as a namespace for potential future hooks.
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Bulk SEO Editor — edit title/description/keyword for multiple posts (premium).
 * @package DadsFam_SEO
 */
class DFSEO_Bulk_Edit {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	public function register_rest(): void {
		register_rest_route( 'dfseo/v1', '/bulk-posts', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_get_posts' ],
			'permission_callback' => fn() => current_user_can( 'edit_posts' ),
		] );
		register_rest_route( 'dfseo/v1', '/bulk-update', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'rest_bulk_update' ],
			'permission_callback' => fn() => current_user_can( 'edit_posts' ),
		] );
	}

	public function rest_get_posts( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		$post_type = sanitize_text_field( (string)( $r['post_type'] ?? 'post' ) );
		$page      = max( 1, (int)( $r['page'] ?? 1 ) );
		$search    = sanitize_text_field( (string)( $r['search'] ?? '' ) );
		$filter    = sanitize_text_field( (string)( $r['filter'] ?? '' ) ); // 'no_kw' | 'no_meta' | 'poor_score'

		$args = [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 25,
			'paged'          => $page,
			's'              => $search,
		];

		if ( $filter === 'no_kw' ) {
			$args['meta_query'] = [ [ 'key' => '_dfseo_focus_keyword', 'compare' => 'NOT EXISTS' ] ];
		} elseif ( $filter === 'no_meta' ) {
			$args['meta_query'] = [ [ 'key' => '_dfseo_meta_desc', 'compare' => 'NOT EXISTS' ] ];
		} elseif ( $filter === 'poor_score' ) {
			$args['meta_query'] = [ [ 'key' => '_dfseo_score', 'value' => 50, 'compare' => '<', 'type' => 'NUMERIC' ] ];
			$args['orderby']    = 'meta_value_num';
			$args['meta_key']   = '_dfseo_score';
			$args['order']      = 'ASC';
		}

		$query = new WP_Query( $args );
		$posts = [];
		foreach ( $query->posts as $post ) {
			$posts[] = [
				'id'            => $post->ID,
				'title'         => $post->post_title,
				'url'           => get_permalink( $post->ID ),
				'focus_keyword' => get_post_meta( $post->ID, '_dfseo_focus_keyword', true ),
				'seo_title'     => get_post_meta( $post->ID, '_dfseo_title', true ),
				'meta_desc'     => get_post_meta( $post->ID, '_dfseo_meta_desc', true ),
				'seo_score'     => (int) get_post_meta( $post->ID, '_dfseo_score', true ),
				'word_count'    => (int) get_post_meta( $post->ID, '_dfseo_word_count', true ),
			];
		}
		return new WP_REST_Response( [
			'posts' => $posts,
			'total' => (int) $query->found_posts,
			'pages' => (int) $query->max_num_pages,
		], 200 );
	}

	public function rest_bulk_update( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		$updates = (array)( $r['updates'] ?? [] );
		$updated = 0;
		foreach ( $updates as $upd ) {
			$id = (int)( $upd['id'] ?? 0 );
			if ( ! $id || ! current_user_can( 'edit_post', $id ) ) continue;
			if ( isset( $upd['focus_keyword'] ) ) update_post_meta( $id, '_dfseo_focus_keyword', sanitize_text_field( $upd['focus_keyword'] ) );
			if ( isset( $upd['seo_title'] ) )     update_post_meta( $id, '_dfseo_title',         sanitize_text_field( $upd['seo_title'] ) );
			if ( isset( $upd['meta_desc'] ) )      update_post_meta( $id, '_dfseo_meta_desc',     sanitize_textarea_field( $upd['meta_desc'] ) );
			$updated++;
		}
		// Re-run analysis on updated posts
		foreach ( $updates as $upd ) {
			$id = (int)( $upd['id'] ?? 0 );
			if ( $id ) dfseo()->analysis->update_post_score( $id );
		}
		return new WP_REST_Response( [ 'updated' => $updated ], 200 );
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Import from Yoast SEO, Rank Math, All in One SEO (premium).
 * @package DadsFam_SEO
 */
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
		register_rest_route( 'dfseo/v1', '/import/status', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_status' ],
			'permission_callback' => fn() => current_user_can( 'manage_options' ),
		] );
	}

	public function rest_status( WP_REST_Request $r ): WP_REST_Response {
		return new WP_REST_Response( [
			'yoast'   => $this->plugin_active( 'wordpress-seo/wp-seo.php' ),
			'rankmath'=> $this->plugin_active( 'seo-by-rank-math/rank-math.php' ),
			'aioseo'  => $this->plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ),
		], 200 );
	}

	public function rest_import( WP_REST_Request $r ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		$source  = sanitize_key( $r['source'] );
		$offset  = (int)( $r['offset'] ?? 0 );
		$limit   = 50;
		switch ( $source ) {
			case 'yoast':    return $this->import_yoast( $offset, $limit );
			case 'rankmath': return $this->import_rankmath( $offset, $limit );
			case 'aioseo':   return $this->import_aioseo( $offset, $limit );
			default:         return new WP_REST_Response( [ 'error' => 'Unknown source' ], 400 );
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
			$kw    = get_post_meta( $id, '_yoast_wpseo_focuskw',     true );
			$title = get_post_meta( $id, '_yoast_wpseo_title',       true );
			$desc  = get_post_meta( $id, '_yoast_wpseo_metadesc',    true );
			$noindex = get_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', true );
			if ( $kw )      update_post_meta( $id, '_dfseo_focus_keyword', sanitize_text_field( $kw ) );
			if ( $title )   update_post_meta( $id, '_dfseo_title',         sanitize_text_field( $title ) );
			if ( $desc )    update_post_meta( $id, '_dfseo_meta_desc',     sanitize_textarea_field( $desc ) );
			if ( $noindex ) update_post_meta( $id, '_dfseo_noindex',       '1' );
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
			$kw    = get_post_meta( $id, 'rank_math_focus_keyword', true );
			$title = get_post_meta( $id, 'rank_math_title',         true );
			$desc  = get_post_meta( $id, 'rank_math_description',   true );
			$robots = get_post_meta( $id, 'rank_math_robots',       true );
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
		if ( ! $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) ) {
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

	private function plugin_active( string $plugin ): bool {
		return in_array( $plugin, (array) get_option( 'active_plugins', [] ), true );
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * WooCommerce integration — product SEO, category SEO.
 * @package DadsFam_SEO
 */
class DFSEO_Woocommerce {
	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) return;
		// Remove Woo's own OG tags to avoid duplication
		add_action( 'init', static function() {
			remove_action( 'wp_head', [ wc()->structured_data ?? null, 'output_structured_data' ], 10 );
		}, 20 );
		// WC product pages get schema from DFSEO_Schema
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * AJAX handler — nonce-verified, maps to REST or quick actions.
 * @package DadsFam_SEO
 */
class DFSEO_Ajax {
	public function __construct() {
		add_action( 'wp_ajax_dfseo_save_meta',         [ $this, 'save_meta' ] );
		add_action( 'wp_ajax_dfseo_get_post_data',     [ $this, 'get_post_data' ] );
		add_action( 'wp_ajax_dfseo_dismiss_notice',    [ $this, 'dismiss_notice' ] );
		add_action( 'wp_ajax_dfseo_run_analysis',      [ $this, 'run_analysis' ] );
	}

	public function save_meta(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( 'Permission denied' );

		$fields = [
			'_dfseo_focus_keyword' => [ 'sanitize_text_field', '_dfseo_focus_keyword' ],
			'_dfseo_title'         => [ 'sanitize_text_field', '_dfseo_title' ],
			'_dfseo_meta_desc'     => [ 'sanitize_textarea_field', '_dfseo_meta_desc' ],
			'_dfseo_canonical'     => [ 'sanitize_url', '_dfseo_canonical' ],
			'_dfseo_noindex'       => [ null, '_dfseo_noindex' ],
			'_dfseo_nofollow'      => [ null, '_dfseo_nofollow' ],
			'_dfseo_noarchive'     => [ null, '_dfseo_noarchive' ],
			'_dfseo_og_image_id'   => [ 'intval', '_dfseo_og_image_id' ],
		];

		foreach ( $fields as $key => $def ) {
			[ $sanitizer, $meta_key ] = $def;
			if ( ! isset( $_POST[ $key ] ) ) continue;
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw   = wp_unslash( $_POST[ $key ] );
			$value = $sanitizer ? call_user_func( $sanitizer, $raw ) : ( $raw === '1' ? '1' : '0' );
			update_post_meta( $post_id, $meta_key, $value );
		}

		// Premium: schema fields
		if ( dfseo_is_premium() && isset( $_POST['_dfseo_faq_items'] ) ) {
			$faqs = json_decode( wp_unslash( $_POST['_dfseo_faq_items'] ), true );
			if ( is_array( $faqs ) ) {
				$clean = array_map( static fn($f) => [
					'q' => sanitize_text_field( $f['q'] ?? '' ),
					'a' => wp_kses_post( $f['a'] ?? '' ),
				], $faqs );
				update_post_meta( $post_id, '_dfseo_faq_items', $clean );
			}
		}

		// Re-run analysis
		dfseo()->analysis->update_post_score( $post_id );

		wp_send_json_success( [ 'score' => (int) get_post_meta( $post_id, '_dfseo_score', true ) ] );
	}

	public function get_post_data(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error();
		wp_send_json_success( [
			'focus_keyword' => get_post_meta( $post_id, '_dfseo_focus_keyword', true ),
			'title'         => get_post_meta( $post_id, '_dfseo_title', true ),
			'meta_desc'     => get_post_meta( $post_id, '_dfseo_meta_desc', true ),
			'seo_score'     => (int) get_post_meta( $post_id, '_dfseo_score', true ),
		] );
	}

	public function dismiss_notice(): void {
		check_ajax_referer( 'dfseo_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
		$notice = sanitize_key( $_POST['notice'] ?? '' );
		update_user_meta( get_current_user_id(), "dfseo_dismissed_{$notice}", 1 );
		wp_send_json_success();
	}

	public function run_analysis(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		$post_id = (int)( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error();
		$result = dfseo()->analysis->analyse_post( $post_id, [
			'focus_keyword' => sanitize_text_field( wp_unslash( $_POST['focus_keyword'] ?? '' ) ),
			'title'         => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
			'meta_desc'     => sanitize_textarea_field( wp_unslash( $_POST['meta_desc'] ?? '' ) ),
			'content'       => wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) ),
		] );
		wp_send_json_success( $result );
	}
}
