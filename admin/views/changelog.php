<?php
/**
 * Admin view: Changelog
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
?>
<?php $dfseo_page = 'changelog'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">

	<div class="dfseo-cl-hero">
		<div class="dfseo-cl-hero-left">
			<div class="dfseo-cl-hero-icon">📋</div>
			<div>
				<h2><?php esc_html_e( "What's New in DadsFam SEO", 'dadsfam-seo' ); ?></h2>
				<p><?php esc_html_e( 'Every improvement, fix, and new feature — newest first.', 'dadsfam-seo' ); ?></p>
			</div>
		</div>
		<div class="dfseo-cl-hero-right">
			<span class="dfseo-cl-hero-version">v<?php echo esc_html( DFSEO_VERSION ); ?></span>
			<span class="dfseo-cl-hero-label"><?php esc_html_e( 'Currently installed', 'dadsfam-seo' ); ?></span>
		</div>
	</div>

	<div class="dfseo-box">
		<div class="dfseo-changelog-body">
			<?php include DFSEO_PATH . 'admin/views/changelog-content.php'; ?>
		</div>
	</div>

	<div class="dfseo-box dfseo-support-card">
		<div class="dfseo-support-icon">💬</div>
		<div class="dfseo-support-body">
			<strong><?php esc_html_e( 'Spotted a bug or have an idea?', 'dadsfam-seo' ); ?></strong>
			<p><?php printf( esc_html__( 'We read every message — free and Premium users alike. Email %s', 'dadsfam-seo' ), '<a href="mailto:support@dadsfam.co.za"><strong>support@dadsfam.co.za</strong></a>' ); ?></p>
		</div>
	</div>
</div>

<style>
.dfseo-version-badge {
	background: var(--df-blue-light);
	color: var(--df-blue);
	padding: 5px 14px;
	border-radius: 99px;
	font-size: 13px;
	font-weight: 700;
	border: 1px solid var(--df-blue);
}
.dfseo-changelog-body {
	padding: 24px;
	font-size: 14px;
	line-height: 1.7;
}
.dfseo-changelog-body h3 {
	font-size: 16px;
	font-weight: 700;
	margin: 32px 0 10px;
	padding-bottom: 8px;
	border-bottom: 2px solid var(--df-amber);
	color: var(--df-gray-900);
}
.dfseo-changelog-body h3:first-child { margin-top: 0; }
.dfseo-changelog-body ul {
	list-style: none;
	padding: 0;
	margin: 0 0 8px;
}
.dfseo-changelog-body li {
	padding: 5px 0 5px 4px;
	border-bottom: 1px solid var(--df-gray-100);
	font-size: 13px;
}
.dfseo-changelog-body li:last-child { border-bottom: none; }
</style>
