<?php
// Premium overlay partial
if ( ! defined( 'ABSPATH' ) ) exit;
$feature = $feature ?? 'this feature';
?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">
	<div class="dfseo-premium-overlay">
		<div class="dfseo-premium-overlay-inner">
			<div class="dfseo-premium-icon">🔒</div>
			<h2><?php printf( esc_html__( '%s — Premium Feature', 'dadsfam-seo' ), esc_html( $feature ) ); ?></h2>
			<p><?php esc_html_e( 'Upgrade to DadsFam SEO Premium to unlock this feature and more.', 'dadsfam-seo' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>" class="dfseo-btn dfseo-btn-primary dfseo-btn-lg">
				⭐ <?php esc_html_e( 'Unlock Premium', 'dadsfam-seo' ); ?>
			</a>
			<a href="<?php echo esc_url( DFSEO_STORE_URL ); ?>" class="dfseo-btn dfseo-btn-ghost" target="_blank">
				<?php esc_html_e( 'Learn More →', 'dadsfam-seo' ); ?>
			</a>
		</div>
	</div>
</div>
