<?php
/**
 * Settings — registers all plugin options via WordPress Settings API.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Settings {

	const GROUPS = [
		'dfseo_general'  => [ 'dfseo_separator', 'dfseo_title_template', 'dfseo_home_title', 'dfseo_home_description', 'dfseo_og_default_image', 'dfseo_google_verify', 'dfseo_bing_verify', 'dfseo_yandex_verify', 'dfseo_pinterest_verify', 'dfseo_norton_verify' ],
		'dfseo_social'   => [ 'dfseo_social_facebook', 'dfseo_social_twitter', 'dfseo_social_instagram', 'dfseo_social_linkedin', 'dfseo_social_youtube', 'dfseo_social_tiktok', 'dfseo_social_pinterest', 'dfseo_social_threads', 'dfseo_social_whatsapp', 'dfseo_social_mastodon', 'dfseo_social_github', 'dfseo_social_telegram', 'dfseo_social_custom', 'dfseo_social_fb_app_id', 'dfseo_twitter_site', 'dfseo_twitter_card_type' ],
		'dfseo_sitemap'  => [ 'dfseo_sitemap_post_types', 'dfseo_sitemap_taxonomies', 'dfseo_sitemap_exclude_ids', 'dfseo_ping_engines' ],
		'dfseo_schema'   => [ 'dfseo_schema_org_type', 'dfseo_schema_org_name', 'dfseo_schema_org_logo', 'dfseo_schema_search_box' ],
		'dfseo_advanced' => [ 'dfseo_noindex_archives', 'dfseo_noindex_author', 'dfseo_noindex_tags', 'dfseo_noindex_paged', 'dfseo_breadcrumbs_enable', 'dfseo_breadcrumbs_separator', 'dfseo_breadcrumbs_home_label', 'dfseo_robots_txt_custom', 'dfseo_track_404', 'dfseo_redirect_enable', 'dfseo_auto_redirect_slug' ],
		'dfseo_geo'      => [ 'dfseo_geo_llms_enable', 'dfseo_geo_llms_summary', 'dfseo_geo_llms_post_count', 'dfseo_geo_ai_control', 'dfseo_geo_ai_mode', 'dfseo_geo_ai_blocked', 'dfseo_geo_speakable' ],
		'dfseo_ai'       => [ 'dfseo_ai_api_key', 'dfseo_ai_model', 'dfseo_track_analytics', 'dfseo_analytics_retention_days' ],
		'dfseo_indexing' => [ 'dfseo_indexnow_key', 'dfseo_google_indexing_key', 'dfseo_google_indexing_json', 'dfseo_indexing_auto_submit' ],
		'dfseo_local'    => [ 'dfseo_local_schema_type', 'dfseo_local_name', 'dfseo_local_address', 'dfseo_local_city', 'dfseo_local_state', 'dfseo_local_zip', 'dfseo_local_country', 'dfseo_local_phone', 'dfseo_local_email', 'dfseo_local_lat', 'dfseo_local_lng' ],
	];

	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_settings(): void {
		foreach ( self::GROUPS as $group => $options ) {
			foreach ( $options as $option ) {
				register_setting(
					$group,
					$option,
					[ 'sanitize_callback' => [ $this, 'sanitize_option' ] ]
				);
			}
		}
	}

	public function sanitize_option( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}
		return sanitize_textarea_field( (string) $value );
	}

	/**
	 * Save settings from a POST.
	 * Handles the custom settings form (not the Settings API form).
	 */
	public static function handle_save( string $group ): bool {
		// Derive the tab name (strip leading 'dfseo_' if present) so the nonce key
		// matches what the settings form generates from $tab (e.g. 'sitemap', not 'dfseo_sitemap').
		$tab = preg_replace( '/^dfseo_/', '', $group );
		if ( ! isset( $_POST["dfseo_settings_nonce_{$tab}"] ) ) return false;
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST["dfseo_settings_nonce_{$tab}"] ) ), "dfseo_save_{$tab}" ) ) return false;
		if ( ! current_user_can( 'manage_options' ) ) return false;

		// Checkbox options — browsers omit unchecked boxes from POST. For these we
		// must save '0' rather than deleting, otherwise a toggle that defaults to
		// '1' (on) can never be switched off (delete → default '1' → still on).
		$checkbox_keys = [
			'dfseo_noindex_archives', 'dfseo_noindex_author', 'dfseo_noindex_tags',
			'dfseo_noindex_paged', 'dfseo_breadcrumbs_enable', 'dfseo_track_404',
			'dfseo_redirect_enable', 'dfseo_auto_redirect_slug', 'dfseo_track_analytics',
			'dfseo_indexing_auto_submit', 'dfseo_sitemap_news', 'dfseo_og_enable',
			'dfseo_twitter_enable', 'dfseo_schema_enable',
			'dfseo_geo_llms_enable', 'dfseo_geo_ai_control', 'dfseo_geo_speakable',
		];

		$options = self::GROUPS[ $group ] ?? [];
		foreach ( $options as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				if ( in_array( $key, $checkbox_keys, true ) ) {
					update_option( $key, '0' );   // explicit "off"
				} else {
					delete_option( $key );          // array / text field cleared
				}
				continue;
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw = wp_unslash( $_POST[ $key ] );
			if ( is_array( $raw ) ) {
				$val = array_map( 'sanitize_text_field', $raw );
			} elseif ( in_array( $key, [ 'dfseo_robots_txt_custom', 'dfseo_geo_llms_summary' ], true ) ) {
				$val = sanitize_textarea_field( $raw );
			} elseif ( in_array( $key, [ 'dfseo_og_default_image', 'dfseo_schema_org_logo' ], true ) ) {
				$val = sanitize_url( $raw );
			} elseif ( $key === 'dfseo_ai_api_key' ) {
				// Never overwrite a saved key with an empty submission —
				// browsers sometimes don't submit pre-filled password fields.
				if ( empty( $raw ) ) continue;
				$val = sanitize_text_field( $raw );
			} else {
				$val = sanitize_text_field( $raw );
			}
			update_option( $key, $val );
		}
		// Mark that the user has configured the plugin at least once — this
		// permanently suppresses the "Welcome / configure your settings" notice.
		update_option( 'dfseo_settings_saved_once', 1, false );
		return true;
	}
}
