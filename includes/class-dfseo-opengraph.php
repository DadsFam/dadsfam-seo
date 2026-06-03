<?php
/**
 * Open Graph & Twitter Card meta tags.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Opengraph {

	public function __construct() {
		add_action( 'wp_head', [ $this, 'output_og_tags' ], 3 );
	}

	public function output_og_tags(): void {
		$tags = array_merge(
			$this->get_og_tags(),
			$this->get_twitter_tags()
		);

		foreach ( $tags as $name => $content ) {
			if ( empty( $content ) ) continue;
			$attr = strpos( $name, 'twitter:' ) === 0 ? 'name' : 'property';
			printf(
				'<meta %s="%s" content="%s" />' . "\n",
				esc_attr( $attr ),
				esc_attr( $name ),
				esc_attr( $content )
			);
		}
	}

	// ─── OG ─────────────────────────────────────────────────────────────────

	private function get_og_tags(): array {
		$meta  = dfseo()->meta;
		$title = $meta->get_page_title();
		$desc  = $meta->get_meta_description();
		$url   = $meta->get_canonical_url() ?: ( is_singular() ? get_permalink() : home_url( '/' ) );
		$image = $this->get_og_image();
		$type  = $this->get_og_type();
		$site  = get_bloginfo( 'name' );
		$locale = str_replace( '-', '_', get_locale() );

		$tags = [
			'og:type'        => $type,
			'og:title'       => $title,
			'og:description' => $desc,
			'og:url'         => $url,
			'og:site_name'   => $site,
			'og:locale'      => $locale,
		];

		if ( $image ) {
			$tags['og:image']            = $image['url'];
			if ( $image['width'] )  $tags['og:image:width']  = (string) $image['width'];
			if ( $image['height'] ) $tags['og:image:height'] = (string) $image['height'];
			if ( $image['alt'] )    $tags['og:image:alt']    = $image['alt'];
			$tags['og:image:type'] = $image['mime'] ?: 'image/jpeg';
		}

		// Article-specific
		if ( $type === 'article' && is_singular( 'post' ) ) {
			global $post;
			$tags['article:published_time'] = mysql2date( 'c', $post->post_date_gmt, false );
			$tags['article:modified_time']  = mysql2date( 'c', $post->post_modified_gmt, false );
			$tags['article:author']         = get_author_posts_url( $post->post_author );
			$cats = wp_get_post_categories( $post->ID, [ 'fields' => 'names' ] );
			if ( $cats ) $tags['article:section'] = $cats[0];
			$tags_list = wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] );
			foreach ( $tags_list as $tag ) {
				$tags["article:tag:{$tag}"] = $tag; // rendered in output loop
			}
			// Article tags need special handling — output inline
			foreach ( wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] ) as $tag_name ) {
				echo '<meta property="article:tag" content="' . esc_attr( $tag_name ) . '" />' . "\n";
			}
		}

		// Facebook App ID
		$fb_app = (string) get_option( 'dfseo_social_fb_app_id', '' );
		if ( $fb_app ) $tags['fb:app_id'] = $fb_app;

		return $tags;
	}

	private function get_og_type(): string {
		if ( is_singular( 'post' ) ) return 'article';
		if ( is_author() )          return 'profile';
		return 'website';
	}

	private function get_og_image(): ?array {
		global $post;
		// 1. Custom OG image from meta box
		if ( is_singular() ) {
			$custom_id = (int) get_post_meta( $post->ID, '_dfseo_og_image_id', true );
			if ( $custom_id ) {
				return $this->image_data( $custom_id );
			}
			// 2. Featured image
			$thumb_id = (int) get_post_thumbnail_id( $post->ID );
			if ( $thumb_id ) {
				return $this->image_data( $thumb_id );
			}
			// 3. First content image
			preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $post->post_content, $m );
			if ( ! empty( $m[1] ) ) {
				return [ 'url' => $m[1], 'width' => null, 'height' => null, 'alt' => '', 'mime' => '' ];
			}
		}
		// 4. Default OG image
		$default_url = (string) get_option( 'dfseo_og_default_image', '' );
		if ( $default_url ) {
			return [ 'url' => $default_url, 'width' => null, 'height' => null, 'alt' => '', 'mime' => '' ];
		}
		return null;
	}

	private function image_data( int $id ): array {
		$meta = wp_get_attachment_metadata( $id );
		$url  = wp_get_attachment_image_url( $id, 'large' );
		$alt  = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		$mime = get_post_mime_type( $id ) ?: 'image/jpeg';
		return [
			'url'    => $url ?: '',
			'width'  => $meta['width']  ?? null,
			'height' => $meta['height'] ?? null,
			'alt'    => $alt,
			'mime'   => $mime,
		];
	}

	// ─── Twitter ──────────────────────────────────────────────────────────

	private function get_twitter_tags(): array {
		$card     = (string) get_option( 'dfseo_twitter_card_type', 'summary_large_image' );
		$site     = (string) get_option( 'dfseo_twitter_site', '' );
		$title    = dfseo()->meta->get_page_title();
		$desc     = dfseo()->meta->get_meta_description();
		$image    = $this->get_og_image();

		$tags = [
			'twitter:card'        => $card,
			'twitter:title'       => $title,
			'twitter:description' => $desc,
		];
		if ( $site )         $tags['twitter:site']  = '@' . ltrim( $site, '@' );
		if ( $image )        $tags['twitter:image'] = $image['url'];
		if ( $image && $image['alt'] ) $tags['twitter:image:alt'] = $image['alt'];

		// Creator
		if ( is_singular( 'post' ) ) {
			global $post;
			$creator = (string) get_the_author_meta( 'twitter', $post->post_author );
			if ( $creator ) $tags['twitter:creator'] = '@' . ltrim( $creator, '@' );
		}

		return $tags;
	}
}
