<?php
/**
 * Admin view: Settings (tabbed)
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );

$tab     = sanitize_key( $_GET['tab'] ?? 'general' );
$tabs    = [
	'general'  => __( 'General', 'dadsfam-seo' ),
	'social'   => __( 'Social', 'dadsfam-seo' ),
	'sitemap'  => __( 'Sitemap', 'dadsfam-seo' ),
	'schema'   => __( 'Schema', 'dadsfam-seo' ),
	'advanced' => __( 'Advanced', 'dadsfam-seo' ),
	'indexing' => '📡 ' . __( 'Instant Indexing', 'dadsfam-seo' ),
	'ai'       => __( 'AI Tools', 'dadsfam-seo' ),
	'local'    => __( 'Local SEO 🔒', 'dadsfam-seo' ),
	'import'   => __( 'Import 🔒', 'dadsfam-seo' ),
];
$premium = dfseo_is_premium();
$saved   = false;

// Handle save
if ( isset( $_POST['dfseo_save'] ) && isset( $_POST["dfseo_settings_nonce_{$tab}"] ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST["dfseo_settings_nonce_{$tab}"] ) ), "dfseo_save_{$tab}" ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			DFSEO_Settings::handle_save( "dfseo_{$tab}" );
			$saved = true;
		}
	}
}
?>
<?php $dfseo_page = 'settings'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">

	<?php if ( $saved ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'dadsfam-seo' ); ?></p></div>
	<?php endif; ?>

	<nav class="dfseo-settings-tabs">
		<?php foreach ( $tabs as $t => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'dfseo-settings', 'tab' => $t ], admin_url( 'admin.php' ) ) ); ?>"
			   class="dfseo-settings-tab <?php echo $t === $tab ? 'active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<form method="post" class="dfseo-settings-form" action="">
		<?php wp_nonce_field( "dfseo_save_{$tab}", "dfseo_settings_nonce_{$tab}" ); ?>

		<?php
		$view_file = DFSEO_PATH . "admin/views/settings-{$tab}.php";
		if ( file_exists( $view_file ) ) {
			include $view_file;
		}
		?>

		<div class="dfseo-settings-footer">
			<button type="submit" name="dfseo_save" value="1" class="dfseo-btn dfseo-btn-primary dfseo-btn-lg">
				<?php esc_html_e( 'Save Settings', 'dadsfam-seo' ); ?>
			</button>
		</div>
	</form>
</div>
