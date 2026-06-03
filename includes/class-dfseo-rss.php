<?php
/**
 * RSS Feed SEO — enhances WordPress RSS feeds for better search engine treatment.
 *
 * Features:
 * - Adds featured image to the beginning of each feed item
 * - Appends canonical source attribution to prevent content scraping
 * - Adds <atom:link> self-referencing to feed headers
 * - Ensures feeds don't get indexed standalone (X-Robots header)
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_RSS {

	public function __construct() {
		// Featured image before content in feed
		add_filter( 'the_excerpt_rss', [ $this, 'prepend_featured_image' ] );
		add_filter( 'the_content_feed', [ $this, 'prepend_featured_image' ] );

		// Source attribution appended after content — deters scrapers
		add_filter( 'the_content_feed', [ $this, 'append_attribution' ] );
		add_filter( 'the_excerpt_rss',  [ $this, 'append_attribution' ] );

		// Add atom:link self-reference and correct namespace to RSS head
		add_action( 'rss2_head', [ $this, 'add_atom_self_link' ] );

		// Noindex RSS feeds from being indexed as separate URLs
		add_action( 'template_redirect', [ $this, 'noindex_feeds' ] );
	}

	// ─── Featured image ──────────────────────────────────────────────────────

	public function prepend_featured_image( string $content ): string {
		if ( ! is_feed() ) return $content;
		if ( ! has_post_thumbnail() ) return $content;

		$img = get_the_post_thumbnail(
			null,
			'large',
			[ 'style' => 'display:block;max-width:100%;height:auto;margin-bottom:16px' ]
		);

		return $img . $content;
	}

	// ─── Attribution / anti-scraping ─────────────────────────────────────────

	public function append_attribution( string $content ): string {
		if ( ! is_feed() ) return $content;

		$post_url  = esc_url( get_permalink() );
		$site_name = esc_html( get_bloginfo( 'name' ) );
		$site_url  = esc_url( home_url() );
		$author    = esc_html( get_the_author() );

		$attribution = sprintf(
			'<p style="border-top:1px solid #eee;margin-top:20px;padding-top:12px;font-size:0.85em;color:#666">'
			. __( 'The post %1$s appeared first on %2$s.', 'dadsfam-seo' )
			. '</p>',
			'<a href="' . $post_url . '">' . esc_html( get_the_title() ) . '</a>',
			'<a href="' . $site_url . '">' . $site_name . '</a>'
		);

		return $content . $attribution;
	}

	// ─── Atom self-link ──────────────────────────────────────────────────────

	public function add_atom_self_link(): void {
		$feed_url = esc_url( get_feed_link() );
		echo '<atom:link href="' . $feed_url . '" rel="self" type="application/rss+xml" />' . "\n";
	}

	// ─── Noindex feeds ───────────────────────────────────────────────────────

	public function noindex_feeds(): void {
		if ( ! is_feed() ) return;
		// Send X-Robots-Tag so Googlebot won't index the feed URL itself
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex', true );
		}
	}
}
