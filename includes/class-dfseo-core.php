<?php
/**
 * Core bootstrap class for DadsFam SEO.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DFSEO_Core
 *
 * Single entry point for the plugin.
 * Instantiates every subsystem and coordinates hooks.
 */
final class DFSEO_Core {

	// ─── Singleton ──────────────────────────────────────────────────────────

	/** @var DFSEO_Core|null */
	private static ?DFSEO_Core $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init_hooks();
		add_action( 'admin_init', [ $this, 'maybe_upgrade' ] );
	}

	/**
	 * Runs on admin_init. When the stored version differs from the running
	 * version (i.e. just after a plugin update), flush rewrite rules once so
	 * new endpoints like /llms.txt start working without the user having to
	 * manually re-save permalinks.
	 */
	public function maybe_upgrade(): void {
		$stored = get_option( 'dfseo_version', '' );
		if ( $stored === DFSEO_VERSION ) return;

		flush_rewrite_rules();
		DFSEO_DB::create_tables();          // ensure any new tables/columns exist
		update_option( 'dfseo_version', DFSEO_VERSION );
	}

	private function __clone() {}

	// ─── Subsystem Instances ────────────────────────────────────────────────

	public DFSEO_Meta     $meta;
	public DFSEO_Sitemap  $sitemap;
	public DFSEO_Schema   $schema;
	public DFSEO_Opengraph $opengraph;
	public DFSEO_Robots   $robots;
	public DFSEO_GEO      $geo;
	public DFSEO_Breadcrumbs $breadcrumbs;
	public DFSEO_Analysis $analysis;
	public DFSEO_License  $license;
	public DFSEO_Ajax     $ajax;
	public DFSEO_Image_Seo $image_seo;

	// ─── Includes ───────────────────────────────────────────────────────────

	private function includes(): void {
		// Core utilities
		require_once DFSEO_PATH . 'includes/class-dfseo-db.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-license.php';

		// SEO engines
		require_once DFSEO_PATH . 'includes/class-dfseo-meta.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-analysis.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-sitemap.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-schema.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-opengraph.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-robots.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-geo.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-breadcrumbs.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-image-seo.php';

		// Premium subsystems
		require_once DFSEO_PATH . 'includes/class-dfseo-ai.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-redirects.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-analytics.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-gsc.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-local-seo.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-bulk-edit.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-import.php';
		require_once DFSEO_PATH . 'includes/class-dfseo-woocommerce.php';

		// Setup wizard
		require_once DFSEO_PATH . 'includes/class-dfseo-setup.php';

		// Instant Indexing
		require_once DFSEO_PATH . 'includes/class-dfseo-indexing.php';

		// Instant Indexing
		require_once DFSEO_PATH . 'includes/class-dfseo-indexing.php';

		// RSS Feed SEO
		require_once DFSEO_PATH . 'includes/class-dfseo-rss.php';

		// AJAX
		require_once DFSEO_PATH . 'includes/class-dfseo-ajax.php';

		// Admin
		if ( is_admin() ) {
			require_once DFSEO_PATH . 'admin/class-dfseo-admin.php';
			require_once DFSEO_PATH . 'admin/class-dfseo-post-meta-box.php';
			require_once DFSEO_PATH . 'admin/class-dfseo-settings.php';
			require_once DFSEO_PATH . 'admin/class-dfseo-dashboard-widget.php';
		}
	}

	private function init_hooks(): void {
		// Boot subsystems
		$this->license     = new DFSEO_License();
		$this->meta        = new DFSEO_Meta();
		$this->sitemap     = new DFSEO_Sitemap();
		$this->schema      = new DFSEO_Schema();
		$this->opengraph   = new DFSEO_Opengraph();
		$this->robots      = new DFSEO_Robots();
		$this->geo         = new DFSEO_GEO();
		$this->breadcrumbs = new DFSEO_Breadcrumbs();
		$this->analysis    = new DFSEO_Analysis();
		$this->image_seo   = new DFSEO_Image_Seo();
		$this->ajax        = new DFSEO_Ajax();
		new DFSEO_Indexing();
		new DFSEO_RSS();

		// Premium subsystems (always instantiated; features gated internally)
		new DFSEO_AI();
		new DFSEO_Redirects();
		new DFSEO_Analytics();
		new DFSEO_GSC();
		new DFSEO_Local_Seo();
		new DFSEO_Bulk_Edit();
		new DFSEO_Import();
		new DFSEO_Woocommerce();

		// Admin
		if ( is_admin() ) {
			new DFSEO_Setup();
			new DFSEO_Admin();
			new DFSEO_Post_Meta_Box();
			new DFSEO_Settings();
			new DFSEO_Dashboard_Widget();
		}

		// Cron
		// License recheck runs hourly so a force-lock suspension takes effect within 1 hour if the
		// force-lock ping couldn't reach this site at the moment the admin clicked the button.
		add_action( 'dfseo_license_cron', [ $this->license, 'verify_license_cron' ] );

		// Heavy maintenance tasks stay daily
		add_action( 'dfseo_daily_cron', [ 'DFSEO_Analytics', 'prune_old_data' ] );
		add_action( 'dfseo_daily_cron', [ 'DFSEO_Redirects', 'cleanup_404_log' ] );
	}

	// ─── Lifecycle ──────────────────────────────────────────────────────────

	public static function activate(): void {
		DFSEO_DB::create_tables();
		DFSEO_Sitemap::flush_rewrite_rules_on_activate();

		// Default options
		$defaults = [
			'dfseo_separator'              => '–',
			'dfseo_title_template'         => '%title% %sep% %sitename%',
			'dfseo_home_title'             => get_bloginfo( 'name' ),
			'dfseo_home_description'       => get_bloginfo( 'description' ),
			'dfseo_og_default_image'       => '',
			'dfseo_sitemap_post_types'     => [ 'post', 'page' ],
			'dfseo_sitemap_taxonomies'     => [ 'category', 'post_tag' ],
			'dfseo_sitemap_exclude_ids'    => [],
			'dfseo_noindex_archives'       => '0',
			'dfseo_noindex_author'         => '0',
			'dfseo_breadcrumbs_enable'     => '1',
			'dfseo_breadcrumbs_separator'  => '›',
			'dfseo_breadcrumbs_home_label' => __( 'Home', 'dadsfam-seo' ),
			'dfseo_schema_org_type'        => 'Organization',
			'dfseo_schema_org_name'        => get_bloginfo( 'name' ),
			'dfseo_schema_org_logo'        => '',
			'dfseo_twitter_site'           => '',
			'dfseo_twitter_card_type'      => 'summary_large_image',
			'dfseo_google_verify'          => '',
			'dfseo_bing_verify'            => '',
			'dfseo_yandex_verify'          => '',
			'dfseo_pinterest_verify'       => '',
			'dfseo_redirect_enable'        => '1',
			'dfseo_track_404'              => '1',
			'dfseo_ai_api_key'             => '',
			'dfseo_ai_model'               => 'claude-sonnet-4-6',
			'dfseo_installed_at'           => current_time( 'mysql' ),
			'dfseo_version'                => DFSEO_VERSION,
		];
		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}

		// Schedule cron
		// Hourly license recheck (catches force-lock suspensions within 1 hour)
		if ( ! wp_next_scheduled( 'dfseo_license_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'dfseo_license_cron' );
		}

		// Daily maintenance
		if ( ! wp_next_scheduled( 'dfseo_daily_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'dfseo_daily_cron' );
		}

		// Trigger the setup wizard on first activation
		DFSEO_Setup::trigger();

		// Flush
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'dfseo_license_cron' );
		wp_clear_scheduled_hook( 'dfseo_daily_cron' );
		flush_rewrite_rules();
	}

	// ─── Helpers ────────────────────────────────────────────────────────────

	/**
	 * Whether the current site has a valid premium licence.
	 */
	public static function is_premium(): bool {
		return DFSEO_License::is_active();
	}

	/**
	 * Render a premium-required overlay for gated admin sections.
	 *
	 * @param string $feature Short feature name for the CTA.
	 */
	public static function premium_overlay( string $feature = '' ): void {
		require DFSEO_PATH . 'admin/views/premium-overlay.php';
	}
}

// ─── Global helper ──────────────────────────────────────────────────────────

/**
 * True when a valid DadsFam SEO premium licence is active.
 */
function dfseo_is_premium(): bool {
	return DFSEO_Core::is_premium();
}

/**
 * Return the plugin core instance.
 */
function dfseo(): DFSEO_Core {
	return DFSEO_Core::instance();
}
