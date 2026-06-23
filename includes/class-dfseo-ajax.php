<?php
/**
 * AJAX handlers — nonce-verified admin-ajax endpoints.
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DFSEO_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_dfseo_save_meta',      [ $this, 'save_meta' ] );
		add_action( 'wp_ajax_dfseo_get_post_data',  [ $this, 'get_post_data' ] );
		add_action( 'wp_ajax_dfseo_dismiss_notice', [ $this, 'dismiss_notice' ] );
		add_action( 'wp_ajax_dfseo_run_analysis',   [ $this, 'run_analysis' ] );
	}

	public function save_meta(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		$post_id = (int)( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( 'Permission denied' );

		$string_fields = [
			'_dfseo_focus_keyword' => 'sanitize_text_field',
			'_dfseo_title'         => 'sanitize_text_field',
			'_dfseo_meta_desc'     => 'sanitize_textarea_field',
			'_dfseo_canonical'     => 'sanitize_url',
		];
		foreach ( $string_fields as $key => $fn ) {
			if ( isset( $_POST[ $key ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				update_post_meta( $post_id, $key, call_user_func( $fn, wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		foreach ( [ '_dfseo_noindex', '_dfseo_nofollow', '_dfseo_noarchive', '_dfseo_nosnippet' ] as $key ) {
			$val = ! empty( $_POST[ $key ] ) && $_POST[ $key ] === '1' ? '1' : '';
			if ( $val ) update_post_meta( $post_id, $key, '1' );
			else        delete_post_meta( $post_id, $key );
		}
		if ( isset( $_POST['_dfseo_og_image_id'] ) ) {
			$img_id = (int) $_POST['_dfseo_og_image_id'];
			if ( $img_id ) update_post_meta( $post_id, '_dfseo_og_image_id', $img_id );
			else           delete_post_meta( $post_id, '_dfseo_og_image_id' );
		}
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

		dfseo()->analysis->update_post_score( $post_id );
		wp_send_json_success( [ 'score' => (int) get_post_meta( $post_id, '_dfseo_score', true ) ] );
	}

	public function get_post_data(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		$post_id = (int)( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error();
		wp_send_json_success( [
			'focus_keyword' => get_post_meta( $post_id, '_dfseo_focus_keyword', true ),
			'title'         => get_post_meta( $post_id, '_dfseo_title',         true ),
			'meta_desc'     => get_post_meta( $post_id, '_dfseo_meta_desc',     true ),
			'seo_score'     => (int) get_post_meta( $post_id, '_dfseo_score',   true ),
		] );
	}

	public function dismiss_notice(): void {
		if ( ! check_ajax_referer( 'dfseo_meta_box', 'nonce', false ) ) wp_send_json_error();
		$notice = sanitize_key( $_POST['notice'] ?? '' );
		update_user_meta( get_current_user_id(), "dfseo_dismissed_{$notice}", 1 );
		// The welcome notice is global (shows on every admin page), so also store
		// a site-wide flag — this guarantees it never reappears for anyone.
		if ( $notice === 'welcome' ) {
			update_option( 'dfseo_welcome_dismissed', 1, false );
		}
		wp_send_json_success();
	}

	public function run_analysis(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		$post_id = (int)( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error();
		$overrides = [
			'focus_keyword' => sanitize_text_field( wp_unslash( $_POST['focus_keyword'] ?? '' ) ),
			'title'         => sanitize_text_field( wp_unslash( $_POST['title']         ?? '' ) ),
			'meta_desc'     => sanitize_textarea_field( wp_unslash( $_POST['meta_desc'] ?? '' ) ),
			'content'       => wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) ),
		];
		$result = dfseo()->analysis->analyse_post( $post_id, $overrides );
		wp_send_json_success( $result );
	}
}
