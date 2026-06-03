<?php
/**
 * Bulk SEO Editor — REST endpoints for editing multiple posts at once (premium).
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

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
		$filter    = sanitize_text_field( (string)( $r['filter'] ?? '' ) );

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
		foreach ( $updates as $upd ) {
			$id = (int)( $upd['id'] ?? 0 );
			if ( $id ) dfseo()->analysis->update_post_score( $id );
		}
		return new WP_REST_Response( [ 'updated' => $updated ], 200 );
	}
}
