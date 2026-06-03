<?php
/**
 * WooCommerce integration — deduplicates OG/Schema with WooCommerce (premium).
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DFSEO_Woocommerce {
	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) return;
		add_action( 'init', static function() {
			if ( function_exists( 'wc' ) && isset( wc()->structured_data ) ) {
				remove_action( 'wp_head', [ wc()->structured_data, 'output_structured_data' ], 10 );
			}
		}, 20 );
	}
}
