<?php
/**
 * Image SEO — auto alt-text injection, title optimisation (premium).
 *
 * Free:    Injects focus keyword into missing alt attributes on the frontend.
 * Premium: Auto alt-text for media library images, bulk image audit.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Image_Seo {

	public function __construct() {
		// Frontend: inject alt attribute if missing and there's a focus keyword
		add_filter( 'the_content', [ $this, 'inject_alt_attributes' ], 20 );

		// On media upload: auto-set alt from filename if empty
		add_action( 'add_attachment', [ $this, 'auto_set_attachment_alt' ] );

		// REST endpoint for bulk image audit (premium)
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	/**
	 * Inject alt attributes into content images that are missing them.
	 * Uses the post's focus keyword as fallback alt text.
	 */
	public function inject_alt_attributes( string $content ): string {
		if ( ! is_singular() || empty( $content ) ) return $content;
		global $post;
		$kw = (string) get_post_meta( $post->ID, '_dfseo_focus_keyword', true );
		if ( ! $kw ) return $content;

		return preg_replace_callback(
			'/<img([^>]*)>/i',
			static function( array $matches ) use ( $kw, $post ): string {
				$attrs = $matches[1];
				// Already has a non-empty alt
				if ( preg_match( '/alt=["\'][^"\']+["\']/', $attrs ) ) {
					return $matches[0];
				}
				// Replace empty alt or inject
				if ( preg_match( '/alt=["\']["\']/', $attrs ) ) {
					$attrs = preg_replace( '/alt=["\']["\']/', 'alt="' . esc_attr( $kw ) . '"', $attrs );
				} else {
					$attrs .= ' alt="' . esc_attr( $kw ) . '"';
				}
				return '<img' . $attrs . '>';
			},
			$content
		);
	}

	/**
	 * When a new image is uploaded, auto-populate alt text from filename.
	 */
	public function auto_set_attachment_alt( int $attachment_id ): void {
		if ( ! wp_attachment_is_image( $attachment_id ) ) return;
		$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $alt ) return; // Already has alt
		$file    = get_attached_file( $attachment_id );
		$name    = pathinfo( $file, PATHINFO_FILENAME );
		$cleaned = ucwords( str_replace( [ '-', '_', '.' ], ' ', $name ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $cleaned ) );
	}

	// ─── Bulk audit (premium) ───────────────────────────────────────────────

	public function register_rest(): void {
		register_rest_route( 'dfseo/v1', '/image-audit', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_image_audit' ],
			'permission_callback' => static fn() => current_user_can( 'upload_files' ),
		] );
	}

	public function rest_image_audit( WP_REST_Request $request ): WP_REST_Response {
		if ( ! dfseo_is_premium() ) {
			return new WP_REST_Response( [ 'error' => 'premium_required' ], 403 );
		}
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$images = $wpdb->get_results( "
			SELECT p.ID, p.post_title, p.guid
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
			WHERE p.post_type = 'attachment'
			  AND p.post_mime_type LIKE 'image/%'
			  AND (pm.meta_value IS NULL OR pm.meta_value = '')
			LIMIT 200
		", ARRAY_A );

		return new WP_REST_Response( [
			'missing_alt' => $images ?: [],
			'count'       => count( $images ?: [] ),
		], 200 );
	}
}
