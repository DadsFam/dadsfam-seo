<?php
/**
 * Admin view: Dashboard
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );

// Gather stats
$args = [
	'post_type'      => [ 'post', 'page' ],
	'post_status'    => 'publish',
	'posts_per_page' => 500,
	'fields'         => 'ids',
	'no_found_rows'  => true,
];
$all_ids = ( new WP_Query( $args ) )->posts;

$great = $ok = $poor = $no_kw = $total = 0;
$needs_attention = [];

foreach ( $all_ids as $id ) {
	$total++;
	$score = (int) get_post_meta( $id, '_dfseo_score', true );
	$kw    = get_post_meta( $id, '_dfseo_focus_keyword', true );
	if ( ! $kw ) { $no_kw++; continue; }
	if ( $score >= 80 )     $great++;
	elseif ( $score >= 50 ) $ok++;
	else {
		$poor++;
		$needs_attention[] = [ 'id' => $id, 'score' => $score, 'title' => get_the_title( $id ) ];
	}
}

usort( $needs_attention, fn( $a, $b ) => $a['score'] <=> $b['score'] );
$needs_attention = array_slice( $needs_attention, 0, 10 );

$sitemap_url = home_url( '/sitemap.xml' );
$premium     = dfseo_is_premium();
?>
<?php $dfseo_page = 'dashboard'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">


	<!-- Health Overview -->
	<div class="dfseo-cards">
		<div class="dfseo-card dfseo-card-score great">
			<div class="dfseo-card-num"><?php echo esc_html( $great ); ?></div>
			<div class="dfseo-card-label"><?php esc_html_e( 'Great (80–100)', 'dadsfam-seo' ); ?></div>
			<div class="dfseo-card-bar"><div class="dfseo-card-bar-fill" style="width:<?php echo $total ? esc_attr( round( $great / $total * 100 ) ) : 0; ?>%"></div></div>
		</div>
		<div class="dfseo-card dfseo-card-score ok">
			<div class="dfseo-card-num"><?php echo esc_html( $ok ); ?></div>
			<div class="dfseo-card-label"><?php esc_html_e( 'OK (50–79)', 'dadsfam-seo' ); ?></div>
			<div class="dfseo-card-bar"><div class="dfseo-card-bar-fill" style="width:<?php echo $total ? esc_attr( round( $ok / $total * 100 ) ) : 0; ?>%"></div></div>
		</div>
		<div class="dfseo-card dfseo-card-score poor">
			<div class="dfseo-card-num"><?php echo esc_html( $poor ); ?></div>
			<div class="dfseo-card-label"><?php esc_html_e( 'Poor (0–49)', 'dadsfam-seo' ); ?></div>
			<div class="dfseo-card-bar"><div class="dfseo-card-bar-fill" style="width:<?php echo $total ? esc_attr( round( $poor / $total * 100 ) ) : 0; ?>%"></div></div>
		</div>
		<div class="dfseo-card dfseo-card-score na">
			<div class="dfseo-card-num"><?php echo esc_html( $no_kw ); ?></div>
			<div class="dfseo-card-label"><?php esc_html_e( 'No Focus Keyword', 'dadsfam-seo' ); ?></div>
			<div class="dfseo-card-bar"><div class="dfseo-card-bar-fill" style="width:<?php echo $total ? esc_attr( round( $no_kw / $total * 100 ) ) : 0; ?>%"></div></div>
		</div>
	</div>

	<div class="dfseo-row">
		<!-- Left column: Needs Attention + No Keywords + Opportunities + Upgrade Banner -->
		<div class="dfseo-col-2">

			<?php if ( ! $premium ) : ?>
			<!-- ── Upgrade banner (free users only) ───────────────────────── -->
			<div class="dfseo-upgrade-banner">
				<div class="dfseo-upgrade-banner-icon">⭐</div>
				<div class="dfseo-upgrade-banner-body">
					<strong><?php esc_html_e( 'Unlock DadsFam SEO Premium', 'dadsfam-seo' ); ?></strong>
					<p><?php esc_html_e( 'AI-powered meta generation, advanced redirects, analytics, bulk editor, local SEO, WooCommerce SEO, Google News sitemap and more.', 'dadsfam-seo' ); ?></p>
					<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>" class="dfseo-upgrade-btn">
							🔑 <?php esc_html_e( 'Activate Licence', 'dadsfam-seo' ); ?>
						</a>
						<a href="https://www.dadsfam.co.za/product/dadsfam-pro-license-keys/" target="_blank" rel="noopener noreferrer" class="dfseo-upgrade-btn-outline">
							🛒 <?php esc_html_e( 'Get Premium →', 'dadsfam-seo' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<!-- ── Needs Attention ────────────────────────────────────────── -->
			<div class="dfseo-box">
				<div class="dfseo-box-header">
					<h2><?php esc_html_e( '⚠️ Needs Attention', 'dadsfam-seo' ); ?></h2>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-bulk-edit&filter=poor_score' ) ); ?>" class="dfseo-btn dfseo-btn-sm dfseo-btn-ghost"><?php esc_html_e( 'View all →', 'dadsfam-seo' ); ?></a>
				</div>
				<?php if ( empty( $needs_attention ) ) : ?>
					<p style="padding:12px 16px;color:var(--df-green);font-weight:600">🎉 <?php esc_html_e( 'All scored posts look good! Great work.', 'dadsfam-seo' ); ?></p>
				<?php else : ?>
					<table class="dfseo-table">
						<thead><tr><th><?php esc_html_e( 'Post', 'dadsfam-seo' ); ?></th><th><?php esc_html_e( 'Score', 'dadsfam-seo' ); ?></th><th></th></tr></thead>
						<tbody>
						<?php foreach ( $needs_attention as $p ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_post_link( $p['id'] ) ); ?>"><?php echo esc_html( $p['title'] ); ?></a></td>
								<td><span class="dfseo-score-badge poor"><?php echo esc_html( $p['score'] ); ?></span></td>
								<td><a href="<?php echo esc_url( get_permalink( $p['id'] ) ); ?>" target="_blank" class="dfseo-muted">↗</a></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<!-- ── Posts with no focus keyword ───────────────────────────── -->
			<?php
			$no_kw_posts = ( new WP_Query([
				'post_type'      => ['post','page'],
				'post_status'    => 'publish',
				'posts_per_page' => 8,
				'meta_query'     => [[ 'key' => '_dfseo_focus_keyword', 'compare' => 'NOT EXISTS' ]],
				'fields'         => 'ids',
				'no_found_rows'  => false,
			]) );
			$no_kw_total = $no_kw_posts->found_posts;
			if ( $no_kw_total > 0 ) : ?>
			<div class="dfseo-box" style="margin-top:20px">
				<div class="dfseo-box-header">
					<h2>🎯 <?php esc_html_e( 'Missing Focus Keywords', 'dadsfam-seo' ); ?></h2>
					<span class="dfseo-badge-count"><?php echo esc_html( $no_kw_total ); ?> <?php esc_html_e( 'posts', 'dadsfam-seo' ); ?></span>
				</div>
				<p class="dfseo-hint" style="padding:0 16px 10px"><?php esc_html_e( 'These published posts have no focus keyword — they\'re not being tracked or scored. Adding one takes 30 seconds and immediately improves your SEO visibility.', 'dadsfam-seo' ); ?></p>
				<table class="dfseo-table">
					<thead><tr><th><?php esc_html_e( 'Post', 'dadsfam-seo' ); ?></th><th><?php esc_html_e( 'Type', 'dadsfam-seo' ); ?></th><th><?php esc_html_e( 'Action', 'dadsfam-seo' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $no_kw_posts->posts as $pid ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ?: '(Untitled)' ); ?></a></td>
							<td><span class="dfseo-muted" style="font-size:11px;text-transform:uppercase"><?php echo esc_html( get_post_type( $pid ) ); ?></span></td>
							<td><a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>#dfseo-meta-box" class="dfseo-btn dfseo-btn-sm dfseo-btn-primary" style="font-size:11px;padding:3px 10px">+ <?php esc_html_e( 'Add keyword', 'dadsfam-seo' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ( $no_kw_total > 8 ) : ?>
					<p class="dfseo-hint" style="padding:8px 16px">
						<?php printf( esc_html__( 'Showing 8 of %d. Use the %sBulk Editor%s to add keywords to many posts at once.', 'dadsfam-seo' ), esc_html( $no_kw_total ), '<a href="' . esc_url( admin_url( 'admin.php?page=dfseo-bulk-edit' ) ) . '">', '</a>' ); ?>
					</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<!-- ── Quick wins (OK scores that could be Great) ────────────── -->
			<?php
			$quick_wins = [];
			foreach ( $all_ids as $id ) {
				$score = (int) get_post_meta( $id, '_dfseo_score', true );
				$kw    = get_post_meta( $id, '_dfseo_focus_keyword', true );
				if ( $kw && $score >= 50 && $score < 80 ) {
					$quick_wins[] = [ 'id' => $id, 'score' => $score, 'title' => get_the_title( $id ) ];
				}
			}
			usort( $quick_wins, fn($a,$b) => $b['score'] <=> $a['score'] );
			$quick_wins = array_slice( $quick_wins, 0, 5 );
			if ( ! empty( $quick_wins ) ) : ?>
			<div class="dfseo-box" style="margin-top:20px">
				<div class="dfseo-box-header">
					<h2>🚀 <?php esc_html_e( 'Quick Wins', 'dadsfam-seo' ); ?></h2>
					<span class="dfseo-hint" style="font-size:12px"><?php esc_html_e( 'OK scores close to Great', 'dadsfam-seo' ); ?></span>
				</div>
				<p class="dfseo-hint" style="padding:0 16px 10px"><?php esc_html_e( 'These posts score OK (50–79) and are closest to reaching Great (80+). A small update — better content, image alt text, or internal links — could push them over the line.', 'dadsfam-seo' ); ?></p>
				<table class="dfseo-table">
					<thead><tr><th><?php esc_html_e( 'Post', 'dadsfam-seo' ); ?></th><th><?php esc_html_e( 'Score', 'dadsfam-seo' ); ?></th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $quick_wins as $p ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_post_link( $p['id'] ) ); ?>"><?php echo esc_html( $p['title'] ); ?></a></td>
							<td><span class="dfseo-score-badge ok"><?php echo esc_html( $p['score'] ); ?></span></td>
							<td><a href="<?php echo esc_url( get_edit_post_link( $p['id'] ) ); ?>" class="dfseo-btn dfseo-btn-sm dfseo-btn-ghost" style="font-size:11px">✏️ <?php esc_html_e( 'Improve', 'dadsfam-seo' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>

			<!-- ── DadsFam branded banner (always visible) ───────────────── -->
			<div style="margin-top:20px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:var(--df-radius);padding:20px 22px;color:#fff;position:relative;overflow:hidden">
				<div style="position:absolute;right:-10px;top:-10px;font-size:80px;opacity:.1;line-height:1">🏆</div>
				<div style="display:flex;align-items:flex-start;gap:14px">
					<div style="font-size:28px;flex-shrink:0">🏆</div>
					<div style="flex:1">
						<?php if ( $premium ) : ?>
							<strong style="font-size:15px;display:block;margin-bottom:4px"><?php esc_html_e( 'You\'re running DadsFam SEO Premium', 'dadsfam-seo' ); ?> ⭐</strong>
							<p style="font-size:13px;margin:0 0 12px;opacity:.92"><?php esc_html_e( 'All features unlocked. Need help, found a bug, or have a feature request? We respond fast — usually same day.', 'dadsfam-seo' ); ?></p>
							<div style="display:flex;gap:8px;flex-wrap:wrap">
								<a href="mailto:support@dadsfam.co.za" style="background:#fff;color:#d97706;font-weight:700;font-size:13px;padding:7px 16px;border-radius:8px;text-decoration:none">✉️ <?php esc_html_e( 'Contact Support', 'dadsfam-seo' ); ?></a>
								<a href="https://dadsfam.co.za" target="_blank" rel="noopener noreferrer" style="background:rgba(255,255,255,.2);color:#fff;font-weight:600;font-size:13px;padding:7px 16px;border-radius:8px;text-decoration:none">🌐 DadsFam.co.za</a>
							</div>
						<?php else : ?>
							<strong style="font-size:15px;display:block;margin-bottom:4px"><?php esc_html_e( 'Unlock DadsFam SEO Premium', 'dadsfam-seo' ); ?> ⭐</strong>
							<p style="font-size:13px;margin:0 0 12px;opacity:.92"><?php esc_html_e( 'AI meta generation, advanced redirects, full analytics, bulk editor, Local SEO, WooCommerce SEO, Google News sitemap and more — all built by the DadsFam team in South Africa.', 'dadsfam-seo' ); ?></p>
							<div style="display:flex;gap:8px;flex-wrap:wrap">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>" style="background:#fff;color:#d97706;font-weight:700;font-size:13px;padding:7px 16px;border-radius:8px;text-decoration:none">🔑 <?php esc_html_e( 'Activate Licence', 'dadsfam-seo' ); ?></a>
								<a href="https://dadsfam.co.za/product/dadsfam-pro-license-keys/" target="_blank" rel="noopener noreferrer" style="background:rgba(255,255,255,.2);color:#fff;font-weight:600;font-size:13px;padding:7px 16px;border-radius:8px;text-decoration:none">🛒 <?php esc_html_e( 'Get Premium →', 'dadsfam-seo' ); ?></a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- ── SEO Tip of the Day ──────────────────────────────────── -->
			<?php
			$__tips = [
				[ "Focus keyword first", "Your focus keyword should appear in the first 100 words, title, H2 headings, and meta description. This is the core signal Google uses for topic relevance." ],
				[ "Meta descriptions drive clicks", "Google does not use meta descriptions for ranking but humans do. A compelling 150-160 character description is your ad copy in search results." ],
				[ "Internal links spread authority", "Whenever you publish a new post, link to it from 2-3 existing related posts. This spreads link equity and helps Google discover content faster." ],
				[ "Image alt text matters", "Every image should have descriptive alt text including your focus keyword naturally. It helps with image search and accessibility." ],
				[ "Page speed is a ranking factor", "Google confirmed Core Web Vitals affect rankings. Compress images, use caching, and minimise plugins to keep your site fast." ],
				[ "Mobile-first indexing", "Google indexes your mobile version first. Check your site on a phone regularly and fix anything hard to read or tap." ],
				[ "Update old posts", "Refreshing a post with new information and re-publishing tells Google the content is current, often leading to quick ranking improvements." ],
				[ "Length vs quality", "Longer posts often rank better but only if the extra length adds value. Aim for comprehensive coverage, not word padding." ],
			];
			$__tip = $__tips[ (int) gmdate('N') % count($__tips) ];
			?>
			<div class="dfseo-box" style="margin-top:20px">
				<div class="dfseo-box-header">
					<h2>💡 <?php esc_html_e( 'SEO Tip', 'dadsfam-seo' ); ?></h2>
					<span class="dfseo-muted" style="font-size:11px"><?php esc_html_e( 'changes daily', 'dadsfam-seo' ); ?></span>
				</div>
				<div style="padding:14px 18px;display:flex;gap:12px;align-items:flex-start">
					<span style="font-size:24px;flex-shrink:0">💡</span>
					<div>
						<strong style="display:block;margin-bottom:4px;color:var(--df-gray-900)"><?php echo esc_html( $__tip[0] ); ?></strong>
						<p style="margin:0;font-size:13px;color:var(--df-gray-600);line-height:1.6"><?php echo esc_html( $__tip[1] ); ?></p>
					</div>
				</div>
			</div>

		</div><!-- .dfseo-col-2 -->

		<!-- Quick Links -->
		<div class="dfseo-col-1">
			<div class="dfseo-box">
				<div class="dfseo-box-header"><h2><?php esc_html_e( '⚡ Quick Actions', 'dadsfam-seo' ); ?></h2></div>
				<ul class="dfseo-quick-links">
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-settings' ) ); ?>">⚙️ <?php esc_html_e( 'Configure Settings', 'dadsfam-seo' ); ?></a></li>
					<li><a href="<?php echo esc_url( $sitemap_url ); ?>" target="_blank">🗺️ <?php esc_html_e( 'View Sitemap', 'dadsfam-seo' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">📝 <?php esc_html_e( 'Edit Posts', 'dadsfam-seo' ); ?></a></li>
					<?php if ( $premium ) : ?>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-bulk-edit' ) ); ?>">✏️ <?php esc_html_e( 'Bulk SEO Editor', 'dadsfam-seo' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-analytics' ) ); ?>">📈 <?php esc_html_e( 'Analytics', 'dadsfam-seo' ); ?></a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-redirects' ) ); ?>">🔀 <?php esc_html_e( 'Redirects', 'dadsfam-seo' ); ?></a></li>
					<?php else : ?>
					<li class="dfseo-locked"><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>">🔒 <?php esc_html_e( 'Bulk Editor (Premium)', 'dadsfam-seo' ); ?></a></li>
					<li class="dfseo-locked"><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>">🔒 <?php esc_html_e( 'Analytics (Premium)', 'dadsfam-seo' ); ?></a></li>
					<li class="dfseo-locked"><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>">🔒 <?php esc_html_e( 'Redirects (Premium)', 'dadsfam-seo' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-license' ) ); ?>">🔑 <?php esc_html_e( 'License', 'dadsfam-seo' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-setup-wizard' ) ); ?>">🧙 <?php esc_html_e( 'Run Setup Wizard', 'dadsfam-seo' ); ?></a></li>
				</ul>
			</div>

			<div class="dfseo-box dfseo-box-info" style="margin-top:20px">
				<div class="dfseo-box-header"><h2><?php esc_html_e( '🌐 SEO Health', 'dadsfam-seo' ); ?></h2></div>
				<ul class="dfseo-health-list">
					<?php
                    // WP serves virtual robots.txt by default — green unless physical file explicitly blocks crawlers
                    $__rb_file = ABSPATH . 'robots.txt';
                    $__rb_ok   = ! file_exists( $__rb_file ) || ( stripos( (string) @file_get_contents( $__rb_file ), 'disallow: /' ) === false );
                    ?>
                <li class="<?php echo $__rb_ok ? 'ok' : 'warn'; ?>">
						<?php esc_html_e( 'Robots.txt', 'dadsfam-seo' ); ?>
					</li>
					<li class="ok">
						<a href="<?php echo esc_url( $sitemap_url ); ?>" target="_blank"><?php esc_html_e( 'XML Sitemap active', 'dadsfam-seo' ); ?></a>
					</li>
					<li class="<?php echo is_ssl() ? 'ok' : 'warn'; ?>">
						<?php esc_html_e( 'HTTPS', 'dadsfam-seo' ); ?> <?php echo is_ssl() ? '✓' : '⚠ Not secure'; ?>
					</li>
					<li class="<?php echo get_option( 'blog_public' ) ? 'ok' : 'warn'; ?>">
						<?php echo get_option( 'blog_public' ) ? esc_html__( 'Visible to search engines ✓', 'dadsfam-seo' ) : '<strong>' . esc_html__( '⚠ Blocked from search engines!', 'dadsfam-seo' ) . '</strong>'; ?>
					</li>
				</ul>
			</div>
		</div>
		<!-- Getting Started Checklist -->
		<?php
		$_c_desc   = strlen( trim( (string) get_option( 'dfseo_home_description', '' ) ) ) > 10;
		$_c_social = (bool) get_option( 'dfseo_social_facebook' ) || (bool) get_option( 'dfseo_social_twitter' ) || (bool) get_option( 'dfseo_social_instagram' );
		$_c_gsc    = (bool) get_option( 'dfseo_google_verify' );
		global $wpdb;
		$_c_kw     = (bool) $wpdb->get_var( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key='_dfseo_focus_keyword' LIMIT 1" );
		?>
		<div class="dfseo-box" style="margin-top:20px">
			<div class="dfseo-box-header"><h2>🚀 <?php esc_html_e( 'Getting Started', 'dadsfam-seo' ); ?></h2>
			<a href="<?php echo esc_url( admin_url('admin.php?page=dfseo-setup-wizard') ); ?>" class="dfseo-btn dfseo-btn-sm dfseo-btn-ghost"><?php esc_html_e( '🧙 Run Wizard', 'dadsfam-seo' ); ?></a></div>
			<ul class="dfseo-gs-list">
				<?php
				$_gs_items = [
					[ true,     __( 'Plugin activated', 'dadsfam-seo' ),                   __( 'Sitemap & schema are live.', 'dadsfam-seo' ),                                                                 '' ],
					[ $_c_desc,  __( 'Homepage description', 'dadsfam-seo' ),               $_c_desc  ? __( 'Set ✓', 'dadsfam-seo' ) : __( 'Helps Google understand your site', 'dadsfam-seo' ),           admin_url('admin.php?page=dfseo-settings&tab=general') ],
					[ $_c_social,__( 'Social profiles added', 'dadsfam-seo' ),              $_c_social? __( 'Connected ✓', 'dadsfam-seo' ) : __( 'Strengthens brand authority in Google', 'dadsfam-seo' ),  admin_url('admin.php?page=dfseo-settings&tab=social') ],
					[ $_c_gsc,  __( 'Google Search Console', 'dadsfam-seo' ),               $_c_gsc   ? __( 'Verified ✓', 'dadsfam-seo' ) : __( 'See which queries bring you visitors', 'dadsfam-seo' ),    admin_url('admin.php?page=dfseo-settings&tab=general') ],
					[ $_c_kw,   __( 'Focus keyword on a post', 'dadsfam-seo' ),             $_c_kw    ? __( 'Done ✓', 'dadsfam-seo' ) : __( 'Edit any post and add a focus keyword', 'dadsfam-seo' ),       admin_url('edit.php') ],
					[ $premium, __( 'Premium licence', 'dadsfam-seo' ),                     $premium  ? __( 'Active ✓ All features unlocked!', 'dadsfam-seo' ) : __( 'Unlock AI tools, redirects & analytics', 'dadsfam-seo' ), admin_url('admin.php?page=dfseo-license') ],
				];
				foreach ( $_gs_items as [ $_done, $_title, $_detail, $_link ] ) :
				?>
				<li class="dfseo-gs-item <?php echo $_done ? 'done' : 'todo'; ?>">
					<span class="dfseo-gs-icon"><?php echo $_done ? '✅' : '⬜'; ?></span>
					<div class="dfseo-gs-text">
						<strong><?php echo esc_html( $_title ); ?></strong>
						<span><?php echo esc_html( $_detail );
							if ( ! $_done && $_link ) echo ' <a href="' . esc_url( $_link ) . '">→</a>';
						?></span>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>

	</div>
	</div>

</div>
