<?php
// License page
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );

$key        = DFSEO_License::get_key();
$status_raw = DFSEO_License::get_status_data();
$is_active  = DFSEO_License::is_active();
$check_info = DFSEO_License::get_check_info();
$status_str = $is_active ? __( 'Active ✓', 'dadsfam-seo' ) : __( 'Inactive', 'dadsfam-seo' );
?>
<?php $dfseo_page = 'license'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">

	<div class="dfseo-box dfseo-box-license">
		<div class="dfseo-license-status <?php echo $is_active ? 'active' : 'inactive'; ?>">
			<div class="dfseo-license-icon"><?php echo $is_active ? '⭐' : '🔒'; ?></div>
			<div>
				<strong><?php echo esc_html( $is_active ? __( 'Premium Active', 'dadsfam-seo' ) : __( 'Free Version', 'dadsfam-seo' ) ); ?></strong>
				<span class="dfseo-muted"><?php echo esc_html( $status_str ); ?></span>
			</div>
		</div>

		<?php if ( $is_active ) : ?>
			<div class="dfseo-license-info">
				<p><?php printf( esc_html__( 'Site: %s', 'dadsfam-seo' ), '<strong>' . esc_html( home_url() ) . '</strong>' ); ?></p>
				<?php
				$expires = $status_raw['expires'] ?? '';
				if ( $expires && $expires !== 'never' ) : ?>
					<p><?php printf( esc_html__( 'Expires: %s', 'dadsfam-seo' ), '<strong>' . esc_html( $expires ) . '</strong>' ); ?></p>
				<?php elseif ( $expires === 'never' ) : ?>
					<p><?php esc_html_e( 'Licence: Lifetime / Never expires', 'dadsfam-seo' ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $status_raw['product'] ) ) : ?>
					<p><?php printf( esc_html__( 'Product: %s', 'dadsfam-seo' ), '<code>' . esc_html( $status_raw['product'] ) . '</code>' ); ?></p>
				<?php endif; ?>
				<p><?php printf( esc_html__( 'Key: %s', 'dadsfam-seo' ), '<code>' . esc_html( DFSEO_License::masked_key() ) . '</code>' ); ?></p>
			</div>

			<div class="dfseo-license-check-info">
				<div class="dfseo-check-row">
					<span class="dfseo-check-icon">🕐</span>
					<div>
						<strong><?php esc_html_e( 'Last verified', 'dadsfam-seo' ); ?></strong>
						<span><?php echo esc_html( $check_info['last_date'] ); ?></span>
						<span class="dfseo-check-human">(<?php echo esc_html( $check_info['last_human'] ); ?>)</span>
					</div>
				</div>
				<div class="dfseo-check-row">
					<span class="dfseo-check-icon"><?php echo $check_info['cron_ok'] ? '⏰' : '⚠️'; ?></span>
					<div>
						<strong><?php esc_html_e( 'Next check', 'dadsfam-seo' ); ?></strong>
						<span><?php echo esc_html( $check_info['next_date'] ); ?></span>
						<span class="dfseo-check-human">(<?php echo esc_html( $check_info['next_human'] ); ?>)</span>
					</div>
				</div>
				<p class="dfseo-hint" style="margin:8px 0 0">
					🛡️ <?php esc_html_e( 'Your licence is automatically re-verified every hour. If our server or your site is ever unreachable, your existing active status is kept — premium will never switch off because of a temporary connection problem.', 'dadsfam-seo' ); ?>
				</p>
			</div>

			<div class="dfseo-license-actions">
				<button type="button" id="dfseo-recheck-btn" class="dfseo-btn dfseo-btn-secondary">
					<span class="dfseo-btn-text">🔄 <?php esc_html_e( 'Re-check Now', 'dadsfam-seo' ); ?></span>
					<span class="dfseo-btn-loading" style="display:none"><span class="dfseo-spinner"></span> <?php esc_html_e( 'Checking…', 'dadsfam-seo' ); ?></span>
				</button>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
					<?php wp_nonce_field( 'dfseo_license_action' ); ?>
					<input type="hidden" name="action" value="dfseo_deactivate_license">
					<button type="submit" class="dfseo-btn dfseo-btn-ghost"><?php esc_html_e( 'Deactivate Licence', 'dadsfam-seo' ); ?></button>
				</form>
				<span id="dfseo-recheck-result" style="margin-left:10px;font-size:13px;display:none"></span>
			</div>

		<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'dfseo_license_action' ); ?>
				<input type="hidden" name="action" value="dfseo_activate_license">
				<div class="dfseo-field-group">
					<label class="dfseo-label" for="dfseo_license_key"><?php esc_html_e( 'Enter your licence key', 'dadsfam-seo' ); ?></label>
					<div style="display:flex;gap:8px">
						<input type="text" name="dfseo_license_key" id="dfseo_license_key" value="<?php echo esc_attr( $key ); ?>" class="dfseo-input" placeholder="DFSEO-XXXX-XXXX-XXXX-XXXX" style="flex:1">
						<button type="submit" class="dfseo-btn dfseo-btn-primary"><?php esc_html_e( 'Activate', 'dadsfam-seo' ); ?></button>
					</div>
					<p class="dfseo-hint" style="margin-top:8px">💡 <?php printf( esc_html__( 'You received your licence key by email after purchase. Lost it? Email %s and we\'ll resend it.', 'dadsfam-seo' ), '<a href="mailto:support@dadsfam.co.za">support@dadsfam.co.za</a>' ); ?></p>
				</div>
			</form>

			<div class="dfseo-upsell-box">
				<h3><?php esc_html_e( '✨ Unlock Premium Features', 'dadsfam-seo' ); ?></h3>
				<ul>
					<li>🤖 <?php esc_html_e( 'AI-powered meta title & description generation (Claude AI)', 'dadsfam-seo' ); ?></li>
					<li>🔍 <?php esc_html_e( 'AI keyword research & content optimisation suggestions', 'dadsfam-seo' ); ?></li>
					<li>🔀 <?php esc_html_e( 'Advanced redirect manager (301, 302, 307, 410, regex)', 'dadsfam-seo' ); ?></li>
					<li>📊 <?php esc_html_e( 'Analytics dashboard — track organic traffic per post', 'dadsfam-seo' ); ?></li>
					<li>✏️ <?php esc_html_e( 'Bulk SEO editor — update hundreds of posts at once', 'dadsfam-seo' ); ?></li>
					<li>📍 <?php esc_html_e( 'Local SEO — LocalBusiness schema with map & hours', 'dadsfam-seo' ); ?></li>
					<li>❓ <?php esc_html_e( 'FAQ, HowTo, Video, Product, Event schema rich results', 'dadsfam-seo' ); ?></li>
					<li>📰 <?php esc_html_e( 'Google News sitemap', 'dadsfam-seo' ); ?></li>
					<li>📦 <?php esc_html_e( 'Import from Yoast, Rank Math, AIOSEO, SEOPress & more', 'dadsfam-seo' ); ?></li>
					<li>🛒 <?php esc_html_e( 'WooCommerce Product schema', 'dadsfam-seo' ); ?></li>
				</ul>
				<a href="<?php echo esc_url( DFSEO_STORE_URL ); ?>" class="dfseo-btn dfseo-btn-primary dfseo-btn-lg" target="_blank">
					<?php esc_html_e( 'Get DadsFam SEO Premium →', 'dadsfam-seo' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

	<!-- Support card — available to everyone, free and premium -->
	<div class="dfseo-box dfseo-support-card">
		<div class="dfseo-support-icon">💬</div>
		<div class="dfseo-support-body">
			<strong><?php esc_html_e( 'Need a hand? We\'re here to help.', 'dadsfam-seo' ); ?></strong>
			<p>
				<?php if ( $is_active ) : ?>
					<?php esc_html_e( 'As a Premium customer your emails get priority support. Reach us any time:', 'dadsfam-seo' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Free or Premium, we\'re happy to help you get set up. Email us:', 'dadsfam-seo' ); ?>
				<?php endif; ?>
				<a href="mailto:support@dadsfam.co.za"><strong>support@dadsfam.co.za</strong></a>
			</p>
		</div>
		<?php if ( $is_active ) : ?>
			<span class="dfseo-support-badge">⭐ <?php esc_html_e( 'Priority', 'dadsfam-seo' ); ?></span>
		<?php endif; ?>
	</div>
</div>

<script>
(function($){
	$('#dfseo-recheck-btn').on('click', function(){
		var $btn = $(this), $res = $('#dfseo-recheck-result');
		$btn.find('.dfseo-btn-text').hide();
		$btn.find('.dfseo-btn-loading').show();
		$btn.prop('disabled', true);
		$res.hide();
		$.post(dfseoAdmin.ajaxUrl, { action:'dfseo_recheck_license', nonce:dfseoAdmin.nonce }, function(r){
			$btn.find('.dfseo-btn-text').show();
			$btn.find('.dfseo-btn-loading').hide();
			$btn.prop('disabled', false);
			var ok = r.success;
			var net = r.data && r.data.network;
			var color = ok ? '#16a34a' : (net ? '#d97706' : '#dc2626');
			var icon  = ok ? '✅' : (net ? '🛡️' : '⚠️');
			$res.html('<span style="color:'+color+'">'+icon+' '+(r.data.message||'')+'</span>').show();
			if (ok) setTimeout(function(){ location.reload(); }, 1200);
		}).fail(function(){
			$btn.find('.dfseo-btn-text').show();
			$btn.find('.dfseo-btn-loading').hide();
			$btn.prop('disabled', false);
			$res.html('<span style="color:#dc2626">⚠️ Request failed. Please try again.</span>').show();
		});
	});
})(jQuery);
</script>
