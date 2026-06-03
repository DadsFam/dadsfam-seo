<?php
/**
 * DadsFam SEO — First-Run Setup Wizard
 *
 * Fires once after activation. Guides the user through 5 quick steps:
 * 1. Welcome
 * 2. Your Site (org name, type, homepage description)
 * 3. Social Profiles (the big three)
 * 4. Search Engines (Google Search Console verification)
 * 5. All done — next steps
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Setup {

	const OPTION_DONE    = 'dfseo_setup_wizard_done';
	const OPTION_PENDING = 'dfseo_setup_wizard_pending';

	public function __construct() {
		add_action( 'admin_menu',       [ $this, 'register_page' ] );
		add_action( 'admin_init',       [ $this, 'maybe_redirect' ] );
		add_action( 'wp_ajax_dfseo_wizard_save', [ $this, 'ajax_save_step' ] );
		add_action( 'wp_ajax_dfseo_wizard_skip', [ $this, 'ajax_skip' ] );
	}

	// ── Register hidden wizard page ──────────────────────────────────────────

	public function register_page(): void {
		add_submenu_page(
			'',                                 // hidden — no parent
			__( 'Setup Wizard', 'dadsfam-seo' ),
			__( 'Setup Wizard', 'dadsfam-seo' ),
			'manage_options',
			'dfseo-setup-wizard',
			[ $this, 'render' ]
		);
	}

	// ── Redirect to wizard on first activation ────────────────────────────────

	public static function trigger(): void {
		update_option( self::OPTION_PENDING, '1' );
	}

	public function maybe_redirect(): void {
		if ( ! get_option( self::OPTION_PENDING ) ) return;
		if ( get_option( self::OPTION_DONE ) )      return;
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( wp_doing_ajax() || wp_doing_cron() )   return;

		$current_page = $_GET['page'] ?? '';
		if ( $current_page === 'dfseo-setup-wizard' ) return;

		delete_option( self::OPTION_PENDING );
		wp_safe_redirect( admin_url( 'admin.php?page=dfseo-setup-wizard' ) );
		exit;
	}

	// ── AJAX: save a wizard step ─────────────────────────────────────────────

	public function ajax_save_step(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$step = sanitize_key( $_POST['step'] ?? '' );
		$data = (array)( $_POST['data'] ?? [] );

		switch ( $step ) {
			case 'site':
				if ( ! empty( $data['org_name'] ) )      update_option( 'dfseo_schema_org_name',   sanitize_text_field( $data['org_name'] ) );
				if ( ! empty( $data['org_type'] ) )      update_option( 'dfseo_schema_org_type',   sanitize_text_field( $data['org_type'] ) );
				if ( ! empty( $data['home_title'] ) )    update_option( 'dfseo_home_title',         sanitize_text_field( $data['home_title'] ) );
				if ( ! empty( $data['home_desc'] ) )     update_option( 'dfseo_home_description',   sanitize_textarea_field( $data['home_desc'] ) );
				break;

			case 'social':
				$map = [
					'facebook'  => 'dfseo_social_facebook',
					'twitter'   => 'dfseo_social_twitter',
					'instagram' => 'dfseo_social_instagram',
					'linkedin'  => 'dfseo_social_linkedin',
					'youtube'   => 'dfseo_social_youtube',
					'tiktok'    => 'dfseo_social_tiktok',
				];
				foreach ( $map as $field => $option ) {
					if ( ! empty( $data[ $field ] ) ) {
						update_option( $option, sanitize_url( $data[ $field ] ) );
					}
				}
				break;

			case 'search':
				if ( ! empty( $data['google_verify'] ) ) update_option( 'dfseo_google_verify', sanitize_text_field( $data['google_verify'] ) );
				if ( ! empty( $data['bing_verify'] ) )   update_option( 'dfseo_bing_verify',   sanitize_text_field( $data['bing_verify'] ) );
				break;
		}

		wp_send_json_success();
	}

	public function ajax_skip(): void {
		check_ajax_referer( 'dfseo_meta_box', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
		update_option( self::OPTION_DONE, '1' );
		wp_send_json_success( [ 'redirect' => admin_url( 'admin.php?page=dfseo-dashboard' ) ] );
	}

	public static function complete(): void {
		update_option( self::OPTION_DONE, '1' );
	}

	public static function is_done(): bool {
		return (bool) get_option( self::OPTION_DONE );
	}

	// ── Render ────────────────────────────────────────────────────────────────

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
		require DFSEO_PATH . 'admin/views/setup-wizard.php';
	}
}
