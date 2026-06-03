<?php
/**
 * Setup Wizard view — 5 steps, fully self-contained, no WP admin chrome needed.
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die();

$org_name  = get_option( 'dfseo_schema_org_name',  get_bloginfo( 'name' ) );
$org_type  = get_option( 'dfseo_schema_org_type',  'Organization' );
$home_desc = get_option( 'dfseo_home_description', get_bloginfo( 'description' ) );
$sitemap   = home_url( '/sitemap.xml' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'DadsFam SEO — Setup Wizard', 'dadsfam-seo' ); ?></title>
<?php wp_head(); ?>
<style>
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; padding: 0; background: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1a2332; }
#wpadminbar { display: none !important; }

/* ── Layout ── */
.wz-body   { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 40px 20px 60px; }
.wz-card   { background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(26,79,160,.12); width: 100%; max-width: 640px; overflow: hidden; }

/* ── Header ── */
.wz-header { background: linear-gradient(135deg, #1a4fa0, #123a78); padding: 28px 36px 22px; }
.wz-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.wz-logo   { display: flex; align-items: center; gap: 10px; }
.wz-logo-icon { font-size: 28px; }
.wz-logo-text { font-size: 18px; font-weight: 800; color: #fff; }
.wz-skip   { font-size: 12px; color: rgba(255,255,255,.6); text-decoration: none; cursor: pointer; background: none; border: none; padding: 0; }
.wz-skip:hover { color: #fff; }

/* ── Progress ── */
.wz-progress      { display: flex; gap: 6px; }
.wz-progress-step { height: 4px; flex: 1; border-radius: 2px; background: rgba(255,255,255,.25); transition: background .3s; }
.wz-progress-step.done   { background: #f59e0b; }
.wz-progress-step.active { background: #fff; }

/* ── Body ── */
.wz-body-inner { padding: 36px; }
.wz-step       { display: none; }
.wz-step.active { display: block; }

.wz-step-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #6b7a8d; margin-bottom: 6px; }
.wz-step-title { font-size: 22px; font-weight: 800; color: #1a2332; margin: 0 0 6px; }
.wz-step-desc  { font-size: 14px; color: #6b7a8d; line-height: 1.6; margin: 0 0 28px; }

/* ── Fields ── */
.wz-field      { margin-bottom: 20px; }
.wz-label      { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; }
.wz-sublabel   { font-size: 12px; color: #9ca3af; font-weight: 400; margin-left: 4px; }
.wz-input,
.wz-textarea,
.wz-select     { display: block; width: 100%; padding: 10px 14px; border: 1.5px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; background: #fff; transition: border-color .15s, box-shadow .15s; }
.wz-input:focus, .wz-textarea:focus, .wz-select:focus { border-color: #1a4fa0; box-shadow: 0 0 0 3px rgba(26,79,160,.12); outline: none; }
.wz-textarea   { resize: vertical; min-height: 80px; }
.wz-hint       { font-size: 12px; color: #9ca3af; margin-top: 5px; line-height: 1.5; }
.wz-optional   { color: #9ca3af; font-weight: 400; font-size: 11px; margin-left: 4px; }

/* Social grid */
.wz-social-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 500px) { .wz-social-grid { grid-template-columns: 1fr; } }

/* ── Tip box ── */
.wz-tip  { display: flex; gap: 12px; background: #eff6ff; border-left: 4px solid #1a4fa0; border-radius: 0 8px 8px 0; padding: 14px 16px; margin: 20px 0; font-size: 13px; line-height: 1.6; color: #1a2332; }
.wz-tip-icon { font-size: 20px; flex-shrink: 0; line-height: 1.4; }

/* ── Checklist (done screen) ── */
.wz-checklist { list-style: none; padding: 0; margin: 0 0 24px; }
.wz-checklist li { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
.wz-checklist li:last-child { border-bottom: none; }
.wz-check-icon { font-size: 16px; }

/* ── Footer buttons ── */
.wz-footer     { display: flex; align-items: center; justify-content: space-between; padding: 20px 36px 28px; border-top: 1px solid #f3f4f6; gap: 12px; }
.wz-btn        { display: inline-flex; align-items: center; gap: 6px; padding: 11px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; border: 2px solid transparent; transition: all .15s; text-decoration: none; }
.wz-btn-primary{ background: #1a4fa0; color: #fff !important; }
.wz-btn-primary:hover { background: #123a78; }
.wz-btn-secondary { background: #f59e0b; color: #fff !important; }
.wz-btn-secondary:hover { background: #d97706; }
.wz-btn-ghost  { background: transparent; color: #6b7a8d !important; border-color: #d1d5db; }
.wz-btn-ghost:hover { border-color: #9ca3af; color: #374151 !important; }
.wz-btn-lg     { padding: 13px 32px; font-size: 16px; }
.wz-saving     { opacity: .7; pointer-events: none; }

/* ── Step counter ── */
.wz-step-count { font-size: 12px; color: #9ca3af; }

/* ── Welcome icons ── */
.wz-welcome-features { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 24px 0; }
.wz-feature-card { background: #f9fafb; border-radius: 8px; padding: 14px; display: flex; align-items: flex-start; gap: 10px; }
.wz-feature-icon { font-size: 22px; flex-shrink: 0; }
.wz-feature-text strong { display: block; font-size: 13px; font-weight: 700; color: #1a2332; }
.wz-feature-text span   { font-size: 12px; color: #6b7a8d; line-height: 1.4; }
@media (max-width: 500px) { .wz-welcome-features { grid-template-columns: 1fr; } }
</style>
</head>
<body class="wp-admin">
<div class="wz-body">
<div class="wz-card">

	<!-- Header -->
	<div class="wz-header">
		<div class="wz-header-top">
			<div class="wz-logo">
				<span class="wz-logo-icon">🏆</span>
				<span class="wz-logo-text">DadsFam SEO</span>
			</div>
			<button class="wz-skip" id="wz-skip"><?php esc_html_e( 'Skip setup →', 'dadsfam-seo' ); ?></button>
		</div>
		<div class="wz-progress" id="wz-progress">
			<div class="wz-progress-step active" data-step="1"></div>
			<div class="wz-progress-step" data-step="2"></div>
			<div class="wz-progress-step" data-step="3"></div>
			<div class="wz-progress-step" data-step="4"></div>
			<div class="wz-progress-step" data-step="5"></div>
		</div>
	</div>

	<!-- Steps -->
	<div class="wz-body-inner">

		<!-- Step 1: Welcome -->
		<div class="wz-step active" id="wz-step-1">
			<div class="wz-step-label"><?php esc_html_e( 'Step 1 of 5', 'dadsfam-seo' ); ?></div>
			<h2 class="wz-step-title">👋 <?php esc_html_e( 'Welcome to DadsFam SEO!', 'dadsfam-seo' ); ?></h2>
			<p class="wz-step-desc"><?php esc_html_e( "Let's get your site ranking in 5 quick steps. This only takes about 3 minutes and you can change everything later.", 'dadsfam-seo' ); ?></p>

			<div class="wz-welcome-features">
				<div class="wz-feature-card">
					<span class="wz-feature-icon">📊</span>
					<div class="wz-feature-text"><strong><?php esc_html_e( 'SEO Analysis', 'dadsfam-seo' ); ?></strong><span><?php esc_html_e( '23-point score on every post', 'dadsfam-seo' ); ?></span></div>
				</div>
				<div class="wz-feature-card">
					<span class="wz-feature-icon">🗺️</span>
					<div class="wz-feature-text"><strong><?php esc_html_e( 'XML Sitemap', 'dadsfam-seo' ); ?></strong><span><?php esc_html_e( 'Auto-submitted to Google & Bing', 'dadsfam-seo' ); ?></span></div>
				</div>
				<div class="wz-feature-card">
					<span class="wz-feature-icon">📋</span>
					<div class="wz-feature-text"><strong><?php esc_html_e( 'Schema Markup', 'dadsfam-seo' ); ?></strong><span><?php esc_html_e( 'Rich results in Google search', 'dadsfam-seo' ); ?></span></div>
				</div>
				<div class="wz-feature-card">
					<span class="wz-feature-icon">📣</span>
					<div class="wz-feature-text"><strong><?php esc_html_e( 'Social Sharing', 'dadsfam-seo' ); ?></strong><span><?php esc_html_e( 'OpenGraph & Twitter Cards', 'dadsfam-seo' ); ?></span></div>
				</div>
			</div>

			<div class="wz-tip">
				<span class="wz-tip-icon">💡</span>
				<span><?php esc_html_e( 'Your sitemap is already live at ', 'dadsfam-seo' ); ?><a href="<?php echo esc_url( $sitemap ); ?>" target="_blank"><strong><?php echo esc_html( $sitemap ); ?></strong></a><?php esc_html_e( ' — Google can already find your content!', 'dadsfam-seo' ); ?></span>
			</div>
		</div>

		<!-- Step 2: Your Site -->
		<div class="wz-step" id="wz-step-2">
			<div class="wz-step-label"><?php esc_html_e( 'Step 2 of 5', 'dadsfam-seo' ); ?></div>
			<h2 class="wz-step-title">🏢 <?php esc_html_e( 'Tell Google about your site', 'dadsfam-seo' ); ?></h2>
			<p class="wz-step-desc"><?php esc_html_e( "This goes into your site's structured data (JSON-LD), which helps Google understand who you are and what you do.", 'dadsfam-seo' ); ?></p>

			<div class="wz-field">
				<label class="wz-label" for="wz-org-name"><?php esc_html_e( 'Your Business / Site Name', 'dadsfam-seo' ); ?></label>
				<input type="text" id="wz-org-name" class="wz-input" value="<?php echo esc_attr( $org_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. DadsFam', 'dadsfam-seo' ); ?>">
				<p class="wz-hint"><?php esc_html_e( 'This is the name Google will show for your business in search results and the Knowledge Panel.', 'dadsfam-seo' ); ?></p>
			</div>

			<div class="wz-field">
				<label class="wz-label" for="wz-org-type"><?php esc_html_e( 'What best describes your site?', 'dadsfam-seo' ); ?></label>
				<select id="wz-org-type" class="wz-select">
					<?php foreach ( [
						'Organization'   => '🏢 ' . __( 'Organisation / Business', 'dadsfam-seo' ),
						'Person'         => '👤 ' . __( 'Personal Blog or Portfolio', 'dadsfam-seo' ),
						'LocalBusiness'  => '📍 ' . __( 'Local Business (shop, restaurant, etc.)', 'dadsfam-seo' ),
						'Corporation'    => '🏗️ ' . __( 'Corporation / Company', 'dadsfam-seo' ),
						'NewsMediaOrganization' => '📰 ' . __( 'News / Media', 'dadsfam-seo' ),
					] as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $org_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="wz-hint"><?php esc_html_e( 'Choosing the right type helps Google categorise your site correctly in search results.', 'dadsfam-seo' ); ?></p>
			</div>

			<div class="wz-field">
				<label class="wz-label" for="wz-home-desc"><?php esc_html_e( 'Homepage Meta Description', 'dadsfam-seo' ); ?> <span class="wz-optional"><?php esc_html_e( '(shown under your site name in Google)', 'dadsfam-seo' ); ?></span></label>
				<textarea id="wz-home-desc" class="wz-textarea" placeholder="<?php esc_attr_e( 'A short description of what your site is about — this appears in Google search results…', 'dadsfam-seo' ); ?>"><?php echo esc_textarea( $home_desc ); ?></textarea>
				<p class="wz-hint"><?php esc_html_e( 'Aim for 120–160 characters. Write for humans, not robots — make it compelling enough that people want to click.', 'dadsfam-seo' ); ?></p>
			</div>

			<div class="wz-tip">
				<span class="wz-tip-icon">💡</span>
				<span><?php esc_html_e( "A good meta description is like a mini advert for your site. It doesn't directly affect rankings but a great one gets more clicks.", 'dadsfam-seo' ); ?></span>
			</div>
		</div>

		<!-- Step 3: Social Profiles -->
		<div class="wz-step" id="wz-step-3">
			<div class="wz-step-label"><?php esc_html_e( 'Step 3 of 5', 'dadsfam-seo' ); ?></div>
			<h2 class="wz-step-title">📣 <?php esc_html_e( 'Your social profiles', 'dadsfam-seo' ); ?></h2>
			<p class="wz-step-desc"><?php esc_html_e( "Adding your social profiles links your website to your social presence in Google's eyes — this strengthens your brand's authority online.", 'dadsfam-seo' ); ?></p>

			<div class="wz-tip">
				<span class="wz-tip-icon">💡</span>
				<span><?php esc_html_e( "These go into your structured data as sameAs links. Google uses them to confirm your brand identity across the web. You can skip any you don't use.", 'dadsfam-seo' ); ?></span>
			</div>

			<div class="wz-social-grid">
				<?php $socials = [
					'wz-facebook'  => [ '📘 Facebook',  get_option('dfseo_social_facebook',''),  'https://facebook.com/yourpage' ],
					'wz-twitter'   => [ '𝕏 X/Twitter',  get_option('dfseo_social_twitter',''),   'https://twitter.com/yourhandle' ],
					'wz-instagram' => [ '📸 Instagram',  get_option('dfseo_social_instagram',''), 'https://instagram.com/yourpage' ],
					'wz-linkedin'  => [ '💼 LinkedIn',   get_option('dfseo_social_linkedin',''),  'https://linkedin.com/company/...' ],
					'wz-youtube'   => [ '▶️ YouTube',    get_option('dfseo_social_youtube',''),   'https://youtube.com/c/...' ],
					'wz-tiktok'    => [ '🎵 TikTok',     get_option('dfseo_social_tiktok',''),    'https://tiktok.com/@yourhandle' ],
				];
				foreach ( $socials as $id => [$label, $val, $ph] ) : ?>
				<div class="wz-field" style="margin-bottom:0">
					<label class="wz-label" for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
					<input type="url" id="<?php echo esc_attr($id); ?>" class="wz-input" value="<?php echo esc_attr($val); ?>" placeholder="<?php echo esc_attr($ph); ?>">
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Step 4: Search Engines -->
		<div class="wz-step" id="wz-step-4">
			<div class="wz-step-label"><?php esc_html_e( 'Step 4 of 5', 'dadsfam-seo' ); ?></div>
			<h2 class="wz-step-title">🔍 <?php esc_html_e( 'Connect to search engines', 'dadsfam-seo' ); ?></h2>
			<p class="wz-step-desc"><?php esc_html_e( "Verifying your site with Google and Bing gives you access to their free tools — see which queries bring visitors, fix crawl errors, and submit your sitemap directly.", 'dadsfam-seo' ); ?></p>

			<div class="wz-field">
				<label class="wz-label" for="wz-google-verify">
					🟢 <?php esc_html_e( 'Google Search Console', 'dadsfam-seo' ); ?>
					<span class="wz-optional"><?php esc_html_e( '(recommended)', 'dadsfam-seo' ); ?></span>
				</label>
				<input type="text" id="wz-google-verify" class="wz-input" value="<?php echo esc_attr( get_option('dfseo_google_verify','') ); ?>" placeholder="<?php esc_attr_e( 'Paste your google-site-verification= meta content value here', 'dadsfam-seo' ); ?>">
				<p class="wz-hint">
					<?php esc_html_e( 'In Google Search Console → Settings → Ownership verification → HTML tag, copy just the content="..." value (not the full tag).', 'dadsfam-seo' ); ?>
					<a href="https://search.google.com/search-console" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open Google Search Console →', 'dadsfam-seo' ); ?></a>
				</p>
			</div>

			<div class="wz-field">
				<label class="wz-label" for="wz-bing-verify">
					🔵 <?php esc_html_e( 'Bing Webmaster Tools', 'dadsfam-seo' ); ?>
					<span class="wz-optional"><?php esc_html_e( '(optional)', 'dadsfam-seo' ); ?></span>
				</label>
				<input type="text" id="wz-bing-verify" class="wz-input" value="<?php echo esc_attr( get_option('dfseo_bing_verify','') ); ?>" placeholder="<?php esc_attr_e( 'Paste your msvalidate.01 content value here', 'dadsfam-seo' ); ?>">
				<p class="wz-hint"><?php esc_html_e( "Bing covers about 30% of desktop searches globally, and Microsoft's Copilot uses Bing's index. Worth connecting.", 'dadsfam-seo' ); ?></p>
			</div>

			<div class="wz-tip">
				<span class="wz-tip-icon">💡</span>
				<span><?php esc_html_e( "You can skip this for now and add verification codes later under Settings → General → Site Verification. Your sitemap works even without verification.", 'dadsfam-seo' ); ?></span>
			</div>
		</div>

		<!-- Step 5: All Done -->
		<div class="wz-step" id="wz-step-5">
			<div class="wz-step-label"><?php esc_html_e( '🎉 All done!', 'dadsfam-seo' ); ?></div>
			<h2 class="wz-step-title"><?php esc_html_e( "You're set up and ready to rank!", 'dadsfam-seo' ); ?></h2>
			<p class="wz-step-desc"><?php esc_html_e( "Here's what to do next to get the most out of DadsFam SEO:", 'dadsfam-seo' ); ?></p>

			<ul class="wz-checklist">
				<li><span class="wz-check-icon">✅</span><span><?php esc_html_e( 'Plugin configured — sitemap, schema, and OpenGraph are active', 'dadsfam-seo' ); ?></span></li>
				<li><span class="wz-check-icon">📝</span><span><?php printf( esc_html__( 'Edit your first post and add a %sFocus Keyword%s to get your first SEO score', 'dadsfam-seo' ), '<strong>', '</strong>' ); ?></span></li>
				<li><span class="wz-check-icon">🗺️</span>
					<span><?php printf( esc_html__( 'Submit your sitemap to %sGoogle Search Console%s: %s', 'dadsfam-seo' ), '<a href="https://search.google.com/search-console" target="_blank">', '</a>', '<strong>' . esc_html( $sitemap ) . '</strong>' ); ?></span>
				</li>
				<li><span class="wz-check-icon">⚙️</span><span><?php printf( esc_html__( 'Fine-tune everything in %sSettings%s — social profiles, schema, redirects and more', 'dadsfam-seo' ), '<a href="' . esc_url( admin_url('admin.php?page=dfseo-settings') ) . '">', '</a>' ); ?></span></li>
				<li><span class="wz-check-icon">⭐</span><span><?php printf( esc_html__( 'Want AI-powered meta generation, redirect manager and analytics? %sUnlock Premium%s', 'dadsfam-seo' ), '<a href="' . esc_url( admin_url('admin.php?page=dfseo-license') ) . '">', '</a>' ); ?></span></li>
			</ul>
		</div>

	</div><!-- .wz-body-inner -->

	<!-- Footer -->
	<div class="wz-footer">
		<span class="wz-step-count" id="wz-step-count">1 / 5</span>
		<div style="display:flex;gap:10px">
			<button class="wz-btn wz-btn-ghost" id="wz-back" style="display:none">← <?php esc_html_e( 'Back', 'dadsfam-seo' ); ?></button>
			<button class="wz-btn wz-btn-primary" id="wz-next"><?php esc_html_e( 'Get started', 'dadsfam-seo' ); ?> →</button>
			<a href="<?php echo esc_url( admin_url('admin.php?page=dfseo-dashboard') ); ?>" class="wz-btn wz-btn-secondary wz-btn-lg" id="wz-finish" style="display:none">
				🚀 <?php esc_html_e( 'Go to Dashboard', 'dadsfam-seo' ); ?>
			</a>
		</div>
	</div>

</div><!-- .wz-card -->
</div><!-- .wz-body -->

<script>
(function() {
	var current = 1;
	var total = 5;
	var nonce = '<?php echo esc_js( wp_create_nonce( 'dfseo_meta_box' ) ); ?>';
	var ajaxUrl = '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>';

	var labels = ['Get started', 'Save & Continue →', 'Save & Continue →', 'Save & Continue →', 'Save & Continue →'];

	function goTo(step) {
		// Hide all steps
		document.querySelectorAll('.wz-step').forEach(function(el) { el.classList.remove('active'); });
		document.getElementById('wz-step-' + step).classList.add('active');

		// Progress bar
		document.querySelectorAll('.wz-progress-step').forEach(function(el) {
			var s = parseInt(el.dataset.step);
			el.classList.remove('done','active');
			if (s < step) el.classList.add('done');
			if (s === step) el.classList.add('active');
		});

		// Buttons
		document.getElementById('wz-back').style.display = step > 1 ? '' : 'none';
		document.getElementById('wz-next').style.display = step < total ? '' : 'none';
		document.getElementById('wz-finish').style.display = step === total ? '' : 'none';
		document.getElementById('wz-next').textContent = labels[step - 1] || 'Continue →';
		document.getElementById('wz-step-count').textContent = step + ' / ' + total;

		current = step;
	}

	function saveStep(step, callback) {
		var data = { action: 'dfseo_wizard_save', nonce: nonce, step: '' };

		if (step === 2) {
			data.step = 'site';
			data['data[org_name]']   = document.getElementById('wz-org-name').value;
			data['data[org_type]']   = document.getElementById('wz-org-type').value;
			data['data[home_desc]']  = document.getElementById('wz-home-desc').value;
		} else if (step === 3) {
			data.step = 'social';
			data['data[facebook]']  = document.getElementById('wz-facebook').value;
			data['data[twitter]']   = document.getElementById('wz-twitter').value;
			data['data[instagram]'] = document.getElementById('wz-instagram').value;
			data['data[linkedin]']  = document.getElementById('wz-linkedin').value;
			data['data[youtube]']   = document.getElementById('wz-youtube').value;
			data['data[tiktok]']    = document.getElementById('wz-tiktok').value;
		} else if (step === 4) {
			data.step = 'search';
			data['data[google_verify]'] = document.getElementById('wz-google-verify').value;
			data['data[bing_verify]']   = document.getElementById('wz-bing-verify').value;
		} else {
			if (callback) callback();
			return;
		}

		var btn = document.getElementById('wz-next');
		btn.classList.add('wz-saving');
		btn.textContent = 'Saving…';

		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		var body = Object.keys(data).map(function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]); }).join('&');
		xhr.onload = function() {
			btn.classList.remove('wz-saving');
			if (callback) callback();
		};
		xhr.onerror = function() { btn.classList.remove('wz-saving'); if (callback) callback(); };
		xhr.send(body);
	}

	document.getElementById('wz-next').addEventListener('click', function() {
		saveStep(current, function() { if (current < total) goTo(current + 1); });
	});

	document.getElementById('wz-back').addEventListener('click', function() {
		if (current > 1) goTo(current - 1);
	});

	document.getElementById('wz-skip').addEventListener('click', function() {
		if (!confirm('<?php echo esc_js( __( 'Skip setup? You can always run it again from the Dashboard.', 'dadsfam-seo' ) ); ?>')) return;
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onload = function() {
			var res = JSON.parse(xhr.responseText);
			if (res.success) window.location.href = res.data.redirect;
		};
		xhr.send('action=dfseo_wizard_skip&nonce=' + encodeURIComponent(nonce));
	});

	document.getElementById('wz-finish').addEventListener('click', function() {
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send('action=dfseo_wizard_skip&nonce=' + encodeURIComponent(nonce));
	});
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
