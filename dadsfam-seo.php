<?php
/**
 * Plugin Name:       DadsFam SEO
 * Plugin URI:        https://www.dadsfam.co.za/plugins/dadsfsam-seo
 * Description:       Enterprise-grade SEO plugin for WordPress. On-page analysis, XML sitemaps, structured data, OpenGraph, AI-powered optimization, redirect manager, local SEO, analytics and more. Freemium with premium AI features.
 * Version:           1.4.6
 * Author:            DadsFam
 * Author URI:        https://www.dadsfam.co.za
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dadsfam-seo
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Tested up to:      6.7
 * Network:           false
 *
 * @package DadsFam_SEO
 * @copyright Copyright (c) 2024 DadsFam. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Constants ──────────────────────────────────────────────────────────────
define( 'DFSEO_VERSION',      '1.4.6' );
define( 'DFSEO_FILE',         __FILE__ );
define( 'DFSEO_PATH',         plugin_dir_path( __FILE__ ) );
define( 'DFSEO_URL',          plugin_dir_url( __FILE__ ) );
define( 'DFSEO_BASENAME',     plugin_basename( __FILE__ ) );
define( 'DFSEO_SLUG',         'dadsfam-seo' );
define( 'DFSEO_PREFIX',       'dfseo_' );
define( 'DFSEO_LICENSE_URL',  'https://www.dadsfam.co.za/wp-json/dfem-licenses/v1/verify' );
define( 'DFSEO_PRODUCT_CODE', 'dfseo' );
define( 'DFSEO_STORE_URL',    'https://www.dadsfam.co.za/plugins/dadsfam-seo' );
define( 'DFSEO_MIN_PHP',      '7.4' );
define( 'DFSEO_MIN_WP',       '5.8' );

// ─── PHP/WP Version Gate ────────────────────────────────────────────────────
if ( version_compare( PHP_VERSION, DFSEO_MIN_PHP, '<' ) ) {
	add_action( 'admin_notices', static function() {
		echo '<div class="notice notice-error"><p>' .
			sprintf(
				/* translators: 1: required PHP version 2: current PHP version */
				esc_html__( 'DadsFam SEO requires PHP %1$s or higher. Your server is running PHP %2$s.', 'dadsfam-seo' ),
				esc_html( DFSEO_MIN_PHP ),
				esc_html( PHP_VERSION )
			) .
		'</p></div>';
	} );
	return;
}

// ─── Autoloader ─────────────────────────────────────────────────────────────
spl_autoload_register( static function( string $class ): void {
	$prefix = 'DFSEO_';
	if ( strpos( $class, $prefix ) !== 0 ) {
		return;
	}
	$suffix    = substr( $class, strlen( $prefix ) );
	$filename  = 'class-dfseo-' . strtolower( str_replace( '_', '-', $suffix ) ) . '.php';
	$locations = [
		DFSEO_PATH . 'includes/' . $filename,
		DFSEO_PATH . 'admin/'    . $filename,
	];
	foreach ( $locations as $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
			return;
		}
	}
} );

// ─── Lifecycle Hooks ────────────────────────────────────────────────────────
register_activation_hook(   DFSEO_FILE, [ 'DFSEO_Core', 'activate' ] );
register_deactivation_hook( DFSEO_FILE, [ 'DFSEO_Core', 'deactivate' ] );

// ─── Boot ───────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', static function(): void {
	load_plugin_textdomain( 'dadsfam-seo', false, dirname( DFSEO_BASENAME ) . '/languages' );
	DFSEO_Core::instance();
}, 8 );
