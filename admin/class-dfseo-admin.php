<?php
/**
 * Admin — registers menus, enqueues assets, shows notices.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Admin {

	public function __construct() {
		add_action( 'admin_menu',    [ $this, 'register_menus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_filter( 'plugin_action_links_' . DFSEO_BASENAME, [ $this, 'plugin_action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'admin_body_class', [ $this, 'add_theme_body_class' ] );
		add_action( 'wp_ajax_dfseo_set_theme', [ $this, 'ajax_set_theme' ] );
	}

	/** The admin colour theme for the current user: auto | light | dark. */
	public static function current_theme(): string {
		$t = get_user_meta( get_current_user_id(), 'dfseo_admin_theme', true );
		return in_array( $t, [ 'auto', 'light', 'dark' ], true ) ? $t : 'auto';
	}

	/** Add theme classes to the admin body on plugin pages. */
	public function add_theme_body_class( string $classes ): string {
		$mode = self::current_theme();          // auto | light | dark
		$classes .= ' dfseo-mode-' . $mode;     // drives the toggle icon
		// For explicit choices we can also set the effective theme server-side
		// (avoids a flash); 'auto' is resolved by JS from the OS preference.
		if ( $mode === 'dark' )  $classes .= ' dfseo-theme-dark';
		if ( $mode === 'light' ) $classes .= ' dfseo-theme-light';
		return $classes;
	}

	/** Persist the user's theme choice. */
	public function ajax_set_theme(): void {
		if ( ! check_ajax_referer( 'dfseo_meta_box', 'nonce', false ) ) wp_send_json_error();
		$theme = sanitize_key( $_POST['theme'] ?? 'auto' );
		if ( ! in_array( $theme, [ 'auto', 'light', 'dark' ], true ) ) $theme = 'auto';
		update_user_meta( get_current_user_id(), 'dfseo_admin_theme', $theme );
		wp_send_json_success( [ 'theme' => $theme ] );
	}

	// ─── Menus ──────────────────────────────────────────────────────────────

	public function register_menus(): void {
		$premium = dfseo_is_premium();
		$icon    = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#a0a5aa" d="M10 2a8 8 0 100 16A8 8 0 0010 2zm0 14.5A6.5 6.5 0 1110 3.5a6.5 6.5 0 010 13zm.75-9.25a.75.75 0 01-.75.75H8.5a.5.5 0 000 1h1.5a1.75 1.75 0 010 3.5H9v.75a.75.75 0 01-1.5 0V12.5a.75.75 0 01.75-.75H10a.25.25 0 000-.5H8.5A1.75 1.75 0 118.5 8h1.25a.75.75 0 01.75.75 .75.75 0 01-.75.75H8.5a.25.25 0 000 .5H10a1.75 1.75 0 011.75 1.75v.25h.25a.75.75 0 010 1.5h-.25v.75a.75.75 0 01-1.5 0"/></svg>' );

		add_menu_page(
			__( 'DadsFam SEO', 'dadsfam-seo' ),
			__( 'DadsFam SEO', 'dadsfam-seo' ),
			'manage_options',
			'dfseo-dashboard',
			[ $this, 'page_dashboard' ],
			$icon,
			90
		);

		add_submenu_page( 'dfseo-dashboard', __( 'Dashboard', 'dadsfam-seo' ), __( 'Dashboard', 'dadsfam-seo' ), 'manage_options', 'dfseo-dashboard', [ $this, 'page_dashboard' ] );
		add_submenu_page( 'dfseo-dashboard', __( 'Settings', 'dadsfam-seo' ),  __( 'Settings', 'dadsfam-seo' ),  'manage_options', 'dfseo-settings',  [ $this, 'page_settings' ] );

		// Premium only in menu (still renders with overlay if not premium)
		add_submenu_page( 'dfseo-dashboard', __( 'Redirects', 'dadsfam-seo' ), $this->maybe_lock( __( 'Redirects', 'dadsfam-seo' ), $premium ), 'manage_options', 'dfseo-redirects',  [ $this, 'page_redirects' ] );
		add_submenu_page( 'dfseo-dashboard', __( 'Analytics', 'dadsfam-seo' ), $this->maybe_lock( __( 'Analytics', 'dadsfam-seo' ), $premium ), 'manage_options', 'dfseo-analytics',  [ $this, 'page_analytics' ] );
		add_submenu_page( 'dfseo-dashboard', __( 'Bulk Editor', 'dadsfam-seo' ), $this->maybe_lock( __( 'Bulk Editor', 'dadsfam-seo' ), $premium ), 'manage_options', 'dfseo-bulk-edit', [ $this, 'page_bulk_edit' ] );
		add_submenu_page( 'dfseo-dashboard', __( 'License', 'dadsfam-seo' ),   __( 'License', 'dadsfam-seo' ),  'manage_options', 'dfseo-license',   [ $this, 'page_license' ] );
		add_submenu_page( 'dfseo-dashboard', __( 'Changelog', 'dadsfam-seo' ), __( 'Changelog', 'dadsfam-seo' ), 'manage_options', 'dfseo-changelog', [ $this, 'page_changelog' ] );
	}

	private function maybe_lock( string $label, bool $active ): string {
		return $active ? $label : $label . ' 🔒';
	}

	// ─── Page renderers ─────────────────────────────────────────────────────

	public function page_dashboard(): void {
		require DFSEO_PATH . 'admin/views/dashboard.php';
	}
	public function page_settings(): void {
		require DFSEO_PATH . 'admin/views/settings.php';
	}
	public function page_redirects(): void {
		if ( ! dfseo_is_premium() ) { DFSEO_Core::premium_overlay( 'Redirect Manager' ); return; }
		require DFSEO_PATH . 'admin/views/redirects.php';
	}
	public function page_analytics(): void {
		if ( ! dfseo_is_premium() ) {
			$dfseo_page = 'analytics'; include DFSEO_PATH . 'admin/views/partials/header.php';
			DFSEO_Core::premium_overlay( 'Analytics Dashboard' ); return;
		}
		require DFSEO_PATH . 'admin/views/analytics.php';
	}
	public function page_bulk_edit(): void {
		if ( ! dfseo_is_premium() ) {
			$dfseo_page = 'bulk-edit'; include DFSEO_PATH . 'admin/views/partials/header.php';
			DFSEO_Core::premium_overlay( 'Bulk SEO Editor' ); return;
		}
		require DFSEO_PATH . 'admin/views/bulk-edit.php';
	}
	public function page_license(): void {
		require DFSEO_PATH . 'admin/views/license.php';
	}
	public function page_changelog(): void {
		require DFSEO_PATH . 'admin/views/changelog.php';
	}

	// ─── Assets ─────────────────────────────────────────────────────────────

	public function enqueue_assets( string $hook ): void {
		$dfseo_pages = [
			'toplevel_page_dfseo-dashboard',
			'dadsfsam-seo_page_dfseo-settings',
			'dadsfsam-seo_page_dfseo-redirects',
			'dadsfsam-seo_page_dfseo-analytics',
			'dadsfsam-seo_page_dfseo-bulk-edit',
			'dadsfsam-seo_page_dfseo-license',
		];
		$is_dfseo_page = in_array( $hook, $dfseo_pages, true )
			|| strpos( $hook, 'dfseo' ) !== false;
		$is_post_edit  = in_array( $hook, [ 'post.php', 'post-new.php' ], true );

		if ( ! $is_dfseo_page && ! $is_post_edit ) return;

		wp_enqueue_style(
			'dfseo-admin',
			DFSEO_URL . 'assets/css/dfseo-admin.css',
			[],
			DFSEO_VERSION
		);

		// Chart.js — required for the analytics dashboard chart
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js',
			[],
			'4.4.3',
			true
		);

		wp_enqueue_script(
			'dfseo-admin',
			DFSEO_URL . 'assets/js/dfseo-admin.js',
			[ 'jquery', 'wp-util', 'chartjs' ],
			DFSEO_VERSION,
			true
		);

		if ( $is_post_edit ) {
			wp_enqueue_script(
				'dfseo-analysis',
				DFSEO_URL . 'assets/js/dfseo-analysis.js',
				[ 'jquery', 'dfseo-admin', 'wp-util' ],
				DFSEO_VERSION,
				true
			);
			wp_enqueue_script(
				'dfseo-serp-preview',
				DFSEO_URL . 'assets/js/dfseo-serp-preview.js',
				[ 'jquery', 'dfseo-admin' ],
				DFSEO_VERSION,
				true
			);
		}

		$post_id = (int) ( $_GET['post'] ?? 0 );

		wp_localize_script( 'dfseo-admin', 'dfseoAdmin', [
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'restUrl'     => rest_url( 'dfseo/v1/' ),
			'restNonce'   => wp_create_nonce( 'wp_rest' ),
			'nonce'       => wp_create_nonce( 'dfseo_meta_box' ),
			'postId'      => $post_id,
			'theme'       => self::current_theme(),
			'isPremium'   => dfseo_is_premium(),
			'siteName'    => get_bloginfo( 'name' ),
			'separator'   => get_option( 'dfseo_separator', '–' ),
			'i18n'        => [
				'loading'           => __( 'Analysing…', 'dadsfam-seo' ),
				'premiumRequired'   => __( 'Upgrade to Premium for AI Tools', 'dadsfam-seo' ),
				'generating'        => __( 'Generating with AI…', 'dadsfam-seo' ),
				'saved'             => __( 'Saved!', 'dadsfam-seo' ),
				'errorSaving'       => __( 'Error saving. Please try again.', 'dadsfam-seo' ),
				'confirmDelete'     => __( 'Are you sure you want to delete this?', 'dadsfam-seo' ),
				'noKeyword'         => __( 'Enter a focus keyword to run analysis.', 'dadsfam-seo' ),
				'good'              => __( 'Great', 'dadsfam-seo' ),
				'ok'                => __( 'Needs Work', 'dadsfam-seo' ),
				'poor'              => __( 'Poor', 'dadsfam-seo' ),
			],
		] );
	}

	// ─── Notices ─────────────────────────────────────────────────────────────

	public function admin_notices(): void {
		// Suppress on our own pages
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'dfseo' ) !== false ) return;

		$user_id = get_current_user_id();
		$notice  = sanitize_key( $_GET['dfseo_notice'] ?? '' );

		// URL-based notice (after license action)
		if ( $notice === 'license_activated' ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '🎉 DadsFam SEO Premium activated! All premium features are now unlocked.', 'dadsfam-seo' ) . '</p></div>';
		} elseif ( $notice === 'license_deactivated' ) {
			echo '<div class="notice notice-info"><p>' . esc_html__( 'DadsFam SEO Premium licence deactivated.', 'dadsfam-seo' ) . '</p></div>';
		} elseif ( $notice === 'license_error' ) {
			$msg = isset( $_GET['dfseo_msg'] ) ? rawurldecode( sanitize_text_field( wp_unslash( $_GET['dfseo_msg'] ) ) ) : '';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'DadsFam SEO: Licence error — ', 'dadsfam-seo' ) . esc_html( $msg ) . '</p></div>';
		}

		// Welcome notice — only until the plugin is configured or dismissed.
		// Suppressed automatically once the setup wizard is done, settings have
		// been saved, or the user dismisses it. The dismiss is handled by an
		// inline script so it persists on ANY admin page (this notice is global,
		// but the plugin's main JS only loads on its own screens).
		$welcome_done = get_user_meta( $user_id, 'dfseo_dismissed_welcome', true )
			|| get_option( 'dfseo_welcome_dismissed' )
			|| get_option( 'dfseo_setup_wizard_done' )
			|| get_option( 'dfseo_settings_saved_once' );

		if ( ! $welcome_done && (string) get_option( 'dfseo_version', '' ) === DFSEO_VERSION ) {
			$dfseo_dismiss_nonce = wp_create_nonce( 'dfseo_meta_box' );
			$dfseo_ajax          = admin_url( 'admin-ajax.php' );
			echo '<div class="notice notice-info is-dismissible dfseo-notice" data-notice="welcome" id="dfseo-welcome-notice">';
			echo '<p>' . sprintf(
				/* translators: %s: link to settings */
				esc_html__( '👋 Welcome to DadsFam SEO! Start by %sconfiguring your settings%s.', 'dadsfam-seo' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=dfseo-settings' ) ) . '">',
				'</a>'
			) . '</p>';
			echo '</div>';
			// Inline, self-contained dismiss — no dependency on the plugin JS bundle.
			echo "<script>(function(){var n=document.getElementById('dfseo-welcome-notice');if(!n)return;n.addEventListener('click',function(e){if(!e.target||!e.target.classList.contains('notice-dismiss'))return;var x=new XMLHttpRequest();x.open('POST'," . wp_json_encode( $dfseo_ajax ) . ");x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');x.send('action=dfseo_dismiss_notice&notice=welcome&nonce=" . esc_js( $dfseo_dismiss_nonce ) . "');});})();</script>";
		}
	}

	// ─── Plugin links ───────────────────────────────────────────────────────

	public function plugin_action_links( array $links ): array {
		$custom = [
			'<a href="' . esc_url( admin_url( 'admin.php?page=dfseo-settings' ) ) . '">' . esc_html__( 'Settings', 'dadsfam-seo' ) . '</a>',
		];
		if ( ! dfseo_is_premium() ) {
			$custom[] = '<a href="' . esc_url( DFSEO_STORE_URL ) . '" target="_blank" style="color:#d97706;font-weight:700">' . esc_html__( '⭐ Go Premium', 'dadsfam-seo' ) . '</a>';
		}
		return array_merge( $custom, $links );
	}

	public function plugin_row_meta( array $links, string $file ): array {
		if ( $file !== DFSEO_BASENAME ) return $links;
		$links[] = '<a href="https://www.dadsfam.co.za/docs/dadsfam-seo" target="_blank">' . esc_html__( 'Documentation', 'dadsfam-seo' ) . '</a>';
		$links[] = '<a href="https://www.dadsfam.co.za/support" target="_blank">' . esc_html__( 'Support', 'dadsfam-seo' ) . '</a>';
		return $links;
	}
}
