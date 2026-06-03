<?php
/**
 * Robots.txt manager.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Robots {

	public function __construct() {
		add_filter( 'robots_txt', [ $this, 'filter_robots_txt' ], 20, 2 );
	}

	public function filter_robots_txt( string $output, bool $public ): string {
		if ( ! $public ) {
			return "User-agent: *\nDisallow: /\n";
		}
		$custom = (string) get_option( 'dfseo_robots_txt_custom', '' );
		if ( $custom ) {
			return $custom;
		}
		// Enhanced default
		$default  = "User-agent: *\n";
		$default .= "Disallow: /wp-admin/\n";
		$default .= "Disallow: /wp-includes/\n";
		$default .= "Disallow: /wp-content/plugins/\n";
		$default .= "Disallow: /wp-content/cache/\n";
		$default .= "Disallow: /?s=\n";
		$default .= "Disallow: /search/\n";
		$default .= "Allow: /wp-admin/admin-ajax.php\n\n";
		$default .= "Sitemap: " . home_url( '/sitemap.xml' ) . "\n";
		return $default . "\n" . $output;
	}
}
