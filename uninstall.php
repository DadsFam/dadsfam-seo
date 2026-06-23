<?php
/**
 * Uninstall DadsFam SEO.
 * Runs when the plugin is deleted from wp-admin/plugins.php.
 * Drops all plugin database tables and removes all plugin options.
 *
 * @package DadsFam_SEO
 */

// Only run via WP uninstall mechanism
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// ─── Drop custom tables ─────────────────────────────────────────────────────
$tables = [
	$wpdb->prefix . 'dfseo_redirects',
	$wpdb->prefix . 'dfseo_404_log',
	$wpdb->prefix . 'dfseo_keyword_rankings',
	$wpdb->prefix . 'dfseo_analytics',
];
foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
}

// ─── Delete all plugin options ───────────────────────────────────────────────
$options = [
	// Core
	'dfseo_version',
	'dfseo_license_key',
	'dfseo_license_status',
	'dfseo_license_status_data',
	'dfseo_license_lock_token',
	'dfseo_license_last_checked',
	'dfseo_license_data',
	// General
	'dfseo_separator',
	'dfseo_title_template',
	'dfseo_home_title',
	'dfseo_home_description',
	'dfseo_og_default_image',
	'dfseo_google_verify',
	'dfseo_bing_verify',
	'dfseo_yandex_verify',
	'dfseo_pinterest_verify',
	'dfseo_norton_verify',
	// Social
	'dfseo_social_facebook',
	'dfseo_social_twitter',
	'dfseo_social_instagram',
	'dfseo_social_linkedin',
	'dfseo_social_youtube',
	'dfseo_social_fb_app_id',
	'dfseo_twitter_site',
	'dfseo_twitter_card_type',
	// Sitemap
	'dfseo_sitemap_post_types',
	'dfseo_sitemap_taxonomies',
	'dfseo_sitemap_exclude_ids',
	'dfseo_ping_engines',
	// Schema
	'dfseo_schema_org_type',
	'dfseo_schema_org_name',
	'dfseo_schema_org_logo',
	'dfseo_schema_search_box',
	// Advanced
	'dfseo_noindex_archives',
	'dfseo_noindex_author',
	'dfseo_noindex_tags',
	'dfseo_noindex_paged',
	'dfseo_breadcrumbs_enable',
	'dfseo_breadcrumbs_separator',
	'dfseo_breadcrumbs_home_label',
	'dfseo_robots_txt_custom',
	'dfseo_track_404',
	'dfseo_redirect_enable',
	// AI
	'dfseo_ai_api_key',
	'dfseo_ai_model',
	'dfseo_track_analytics',
	'dfseo_analytics_retention_days',
	// Local SEO
	'dfseo_local_schema_type',
	'dfseo_local_name',
	'dfseo_local_address',
	'dfseo_local_city',
	'dfseo_local_state',
	'dfseo_local_zip',
	'dfseo_local_country',
	'dfseo_local_phone',
	'dfseo_local_email',
	'dfseo_local_lat',
	'dfseo_local_lng',
	// Crons
	'dfseo_sitemap_last_ping',
	// Sitemap cache + auto-redirect + GSC
	'dfseo_sitemap_cache_v',
	'dfseo_auto_redirect_slug',
	'dfseo_google_indexing_key',
	// GEO
	'dfseo_geo_llms_enable',
	'dfseo_geo_llms_summary',
	'dfseo_geo_llms_post_count',
	'dfseo_geo_ai_control',
	'dfseo_geo_ai_mode',
	'dfseo_geo_ai_blocked',
	'dfseo_geo_speakable',
	'dfseo_welcome_dismissed',
	'dfseo_settings_saved_once',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// ─── Delete transients ────────────────────────────────────────────────────────
delete_transient( 'dfseo_license_status' );
delete_transient( 'dfseo_llms_txt_cache' );

// Sweep cached sitemap files + GSC caches (transient names are versioned/dynamic)
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dfseo_sm_%'
	 OR option_name LIKE '_transient_timeout_dfseo_sm_%'
	 OR option_name LIKE '_transient_dfseo_gsc_%'
	 OR option_name LIKE '_transient_timeout_dfseo_gsc_%'"
);

// ─── Clean up post meta (optional — only if you want a full wipe) ────────────
// Uncomment the block below to also delete all SEO meta from posts on uninstall.
// WARNING: This cannot be undone.
/*
$meta_keys = [
	'_dfseo_focus_keyword', '_dfseo_title', '_dfseo_meta_desc', '_dfseo_canonical',
	'_dfseo_noindex', '_dfseo_nofollow', '_dfseo_noarchive', '_dfseo_nosnippet',
	'_dfseo_og_image_id', '_dfseo_schema_type', '_dfseo_faq_items',
	'_dfseo_score', '_dfseo_word_count',
];
foreach ( $meta_keys as $key ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => $key ] );
}
*/

// ─── Remove scheduled events ─────────────────────────────────────────────────
foreach ( [ 'dfseo_license_cron', 'dfseo_daily_cron', 'dfseo_cleanup_404_log', 'dfseo_analytics_prune' ] as $event ) {
	$timestamp = wp_next_scheduled( $event );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, $event );
	}
}

// ─── Clean up per-user meta ───────────────────────────────────────────────────
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('dfseo_admin_theme','dfseo_dismissed_welcome')" );
