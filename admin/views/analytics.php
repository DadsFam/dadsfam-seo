<?php
/**
 * Analytics dashboard view
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
?>
<?php $dfseo_page = 'analytics'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">

	<!-- Date range selector -->
	<div class="dfseo-analytics-toolbar">
		<div class="dfseo-analytics-range">
			<?php foreach ( [ 7 => '7 days', 14 => '14 days', 30 => '30 days', 60 => '60 days', 90 => '90 days' ] as $d => $label ) : ?>
				<button type="button" class="dfseo-range-btn<?php echo $d === 30 ? ' active' : ''; ?>" data-days="<?php echo esc_attr( $d ); ?>">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<span class="dfseo-analytics-live" id="dfseo-analytics-live">
			<span class="dfseo-live-dot"></span>
			<span class="dfseo-live-text"><?php esc_html_e( 'LIVE', 'dadsfam-seo' ); ?></span>
			<span class="dfseo-analytics-last-updated dfseo-muted"></span>
		</span>
	</div>

	<!-- Summary cards -->
	<div class="dfseo-analytics-cards">
		<div class="dfseo-analytics-card">
			<div class="dfseo-analytics-card-icon">🖱️</div>
			<div class="dfseo-analytics-card-body">
				<div class="dfseo-analytics-num" id="dfseo-total-clicks">—</div>
				<div class="dfseo-analytics-label"><?php esc_html_e( 'Organic Clicks', 'dadsfam-seo' ); ?></div>
				<div class="dfseo-analytics-trend" id="dfseo-clicks-trend"></div>
			</div>
		</div>
		<div class="dfseo-analytics-card">
			<div class="dfseo-analytics-card-icon">👁️</div>
			<div class="dfseo-analytics-card-body">
				<div class="dfseo-analytics-num" id="dfseo-total-impressions">—</div>
				<div class="dfseo-analytics-label"><?php esc_html_e( 'Impressions', 'dadsfam-seo' ); ?></div>
				<div class="dfseo-analytics-trend" id="dfseo-impressions-trend"></div>
			</div>
		</div>
		<div class="dfseo-analytics-card">
			<div class="dfseo-analytics-card-icon">🔢</div>
			<div class="dfseo-analytics-card-body">
				<div class="dfseo-analytics-num" id="dfseo-top-engine">—</div>
				<div class="dfseo-analytics-label"><?php esc_html_e( 'Top Search Engine', 'dadsfam-seo' ); ?></div>
				<div class="dfseo-analytics-trend" id="dfseo-engine-sub"></div>
			</div>
		</div>
		<div class="dfseo-analytics-card">
			<div class="dfseo-analytics-card-icon">📉</div>
			<div class="dfseo-analytics-card-body">
				<div class="dfseo-analytics-num" id="dfseo-decay-count">—</div>
				<div class="dfseo-analytics-label"><?php esc_html_e( 'Declining Posts', 'dadsfam-seo' ); ?></div>
				<div class="dfseo-analytics-trend" id="dfseo-decay-sub" class="dfseo-muted"><?php esc_html_e( 'Traffic dropped >30%', 'dadsfam-seo' ); ?></div>
			</div>
		</div>
	</div>

	<!-- Main row: chart + top posts -->
	<div class="dfseo-row" style="margin-top:20px">
		<div class="dfseo-col-2">
			<div class="dfseo-box">
				<div class="dfseo-box-header">
					<h2>📈 <?php esc_html_e( 'Organic Traffic Timeline', 'dadsfam-seo' ); ?></h2>
				</div>
				<div id="dfseo-chart-empty" class="dfseo-analytics-empty" style="display:none">
					<div class="dfseo-analytics-empty-icon">📊</div>
					<strong><?php esc_html_e( 'No organic traffic recorded yet', 'dadsfam-seo' ); ?></strong>
					<p><?php esc_html_e( 'DadsFam SEO tracks visitors that arrive from Google, Bing, DuckDuckGo and other search engines. Data appears here as organic traffic comes in.', 'dadsfam-seo' ); ?></p>
					<p class="dfseo-muted"><?php esc_html_e( 'This is normal for new sites. Make sure your sitemap is submitted to Google Search Console and that Auto-Submit is on in Instant Indexing settings.', 'dadsfam-seo' ); ?></p>
				</div>
				<div class="dfseo-chart-wrap" style="position:relative;width:100%;max-height:360px;overflow:hidden;padding:8px 4px 0;">
					<canvas id="dfseo-analytics-chart"></canvas>
				</div>
			</div>
		</div>
		<div class="dfseo-col-1">
			<div class="dfseo-box">
				<div class="dfseo-box-header"><h2>🏆 <?php esc_html_e( 'Top Posts by Clicks', 'dadsfam-seo' ); ?></h2></div>
				<table class="dfseo-table" id="dfseo-top-posts-table">
					<thead><tr>
						<th><?php esc_html_e( 'Post', 'dadsfam-seo' ); ?></th>
						<th style="text-align:right"><?php esc_html_e( 'Clicks', 'dadsfam-seo' ); ?></th>
					</tr></thead>
					<tbody id="dfseo-top-posts-body">
						<tr><td colspan="2" class="dfseo-muted" style="text-align:center;padding:20px"><?php esc_html_e( 'Loading…', 'dadsfam-seo' ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Google Search Console — real keyword data -->
	<div class="dfseo-box" style="margin-top:20px">
		<div class="dfseo-box-header">
			<h2>🔑 <?php esc_html_e( 'Top Keywords — Google Search Console', 'dadsfam-seo' ); ?></h2>
			<span class="dfseo-muted" style="font-size:12px" id="dfseo-gsc-meta"></span>
		</div>
		<div id="dfseo-gsc-keywords">
			<p class="dfseo-muted" style="text-align:center;padding:20px"><?php esc_html_e( 'Loading…', 'dadsfam-seo' ); ?></p>
		</div>
	</div>

	<!-- Engine breakdown + Content decay -->
	<div class="dfseo-row" style="margin-top:20px">
		<div class="dfseo-col-1">
			<div class="dfseo-box">
				<div class="dfseo-box-header"><h2>🔍 <?php esc_html_e( 'Traffic by Search Engine', 'dadsfam-seo' ); ?></h2></div>
				<div id="dfseo-engine-breakdown">
					<p class="dfseo-muted" style="text-align:center;padding:20px"><?php esc_html_e( 'Loading…', 'dadsfam-seo' ); ?></p>
				</div>
			</div>
		</div>
		<div class="dfseo-col-2">
			<div class="dfseo-box">
				<div class="dfseo-box-header">
					<h2>📉 <?php esc_html_e( 'Content Decay', 'dadsfam-seo' ); ?></h2>
					<span class="dfseo-muted" style="font-size:12px"><?php esc_html_e( 'Posts with traffic dropped >30% vs previous period', 'dadsfam-seo' ); ?></span>
				</div>
				<div id="dfseo-decay-section">
					<p class="dfseo-muted" style="text-align:center;padding:20px"><?php esc_html_e( 'Loading…', 'dadsfam-seo' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<!-- How it works -->
	<div class="dfseo-box" style="margin-top:20px">
		<p class="dfseo-hint" style="margin:0">
			💡 <strong><?php esc_html_e( 'How this works:', 'dadsfam-seo' ); ?></strong>
			<?php esc_html_e( 'DadsFam SEO detects visits arriving from Google, Bing, Yahoo, DuckDuckGo, Yandex, Ecosia and 10+ other search engines using the HTTP referrer header. Trend arrows compare the selected period to the same-length period before it. Content Decay flags posts that received traffic before but have dropped significantly — worth updating those posts.', 'dadsfam-seo' ); ?>
		</p>
	</div>

</div><!-- .wrap -->

<style>
.dfseo-analytics-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
.dfseo-analytics-range   { display:flex; gap:6px; flex-wrap:wrap; }
.dfseo-range-btn { background:#fff; border:1px solid var(--df-gray-300); border-radius:6px; padding:6px 14px; font-size:13px; font-weight:600; color:var(--df-gray-600); cursor:pointer; transition:.15s; }
.dfseo-range-btn:hover  { border-color:var(--df-blue); color:var(--df-blue); }
.dfseo-range-btn.active { background:var(--df-blue); border-color:var(--df-blue); color:#fff; }

.dfseo-analytics-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .dfseo-analytics-cards { grid-template-columns:repeat(2,1fr); } }
@media(max-width:500px){ .dfseo-analytics-cards { grid-template-columns:1fr; } }

.dfseo-analytics-card { background:#fff; border:1px solid var(--df-gray-200); border-radius:var(--df-radius); padding:18px 20px; display:flex; align-items:center; gap:14px; }
.dfseo-analytics-card-icon { font-size:28px; flex-shrink:0; }
.dfseo-analytics-num   { font-size:26px; font-weight:800; color:var(--df-gray-900); line-height:1; }
.dfseo-analytics-label { font-size:12px; color:var(--df-gray-500); margin-top:4px; }
.dfseo-analytics-trend { font-size:12px; margin-top:4px; font-weight:600; }
.trend-up   { color:var(--df-green); }
.trend-down { color:var(--df-red); }
.trend-flat { color:var(--df-gray-500); }

.dfseo-analytics-empty { text-align:center; padding:32px 20px; }
.dfseo-analytics-empty-icon { font-size:40px; margin-bottom:10px; }
.dfseo-analytics-empty strong { display:block; font-size:15px; margin-bottom:8px; }
.dfseo-analytics-empty p { font-size:13px; color:var(--df-gray-600); max-width:420px; margin:0 auto 6px; }

.dfseo-engine-bar { display:flex; align-items:center; gap:10px; padding:8px 16px; border-bottom:1px solid var(--df-gray-100); }
.dfseo-engine-bar:last-child { border-bottom:none; }
.dfseo-engine-name { width:100px; font-size:13px; font-weight:600; }
.dfseo-engine-track { flex:1; background:var(--df-gray-100); border-radius:99px; height:8px; }
.dfseo-engine-fill  { height:8px; border-radius:99px; transition:width 1.1s cubic-bezier(.16,1,.3,1);
	background:linear-gradient(90deg,#1a4fa0,#3b82f6,#60a5fa); background-size:200% 100%; animation:dfx-bar-sheen 3s linear infinite; }
@keyframes dfx-bar-sheen { to { background-position:-200% 0; } }

/* ── LIVE indicator ──────────────────────────────────────────────────────── */
.dfseo-analytics-live { display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:700; color:#16a34a; letter-spacing:.4px; }
.dfseo-live-dot { width:9px; height:9px; border-radius:50%; background:#16a34a; position:relative; box-shadow:0 0 0 0 rgba(22,163,74,.5); animation:dfx-live-pulse 2s ease-in-out infinite; }
.dfseo-live-dot.dfx-ping { animation:dfx-live-ping .7s ease-out; }
@keyframes dfx-live-pulse { 0%,100% { box-shadow:0 0 0 0 rgba(22,163,74,.45); } 50% { box-shadow:0 0 0 6px rgba(22,163,74,0); } }
@keyframes dfx-live-ping  { 0% { transform:scale(1); box-shadow:0 0 0 0 rgba(22,163,74,.7); } 100% { transform:scale(1); box-shadow:0 0 0 12px rgba(22,163,74,0); } }
.dfseo-analytics-last-updated { font-weight:500; }

/* ── Animated aurora backdrop on the analytics page ──────────────────────── */
.dfseo-wrap--with-header { position:relative; }
.dfseo-wrap--with-header::before {
	content:''; position:fixed; inset:0; z-index:-1; pointer-events:none; opacity:.5;
	background:
		radial-gradient(40% 40% at 15% 10%, rgba(245,158,11,.10), transparent 60%),
		radial-gradient(45% 45% at 85% 15%, rgba(26,79,160,.10), transparent 60%),
		radial-gradient(40% 40% at 75% 85%, rgba(124,58,237,.07), transparent 60%);
	background-size:200% 200%; animation:dfx-aurora 22s ease-in-out infinite;
}
@keyframes dfx-aurora { 0%,100% { background-position:0% 50%; } 50% { background-position:100% 50%; } }

/* taller, breathing chart area */
#dfseo-analytics-chart { display:block; }

@media (prefers-reduced-motion: reduce) {
	.dfseo-engine-fill, .dfseo-live-dot, .dfseo-wrap--with-header::before { animation:none !important; }
	.dfseo-engine-fill { transition:none !important; }
}
.dfseo-engine-pct   { font-size:12px; color:var(--df-gray-500); width:40px; text-align:right; }

.dfseo-decay-row { display:flex; align-items:center; gap:12px; padding:10px 16px; border-bottom:1px solid var(--df-gray-100); font-size:13px; }
.dfseo-decay-row:last-child { border-bottom:none; }
.dfseo-decay-title { flex:1; }
.dfseo-decay-title a { color:var(--df-blue); }
.dfseo-decay-change { font-weight:700; color:var(--df-red); }
</style>

<script>
jQuery(function($){
	var ajaxNonce = <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>;
	var restBase  = <?php echo wp_json_encode( rest_url( 'dfseo/v1/analytics' ) ); ?>;
	var chart     = null;
	var chartFirst = true;
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// Smoothly roll a number element from its current value to a new target
	function rollNumber($el, target) {
		target = parseInt(target) || 0;
		var from = parseInt(($el.text() || '').replace(/[^0-9]/g, '')) || 0;
		if (reduceMotion || from === target) { $el.text(target.toLocaleString()); return; }
		var dur = 700, start = null;
		function step(ts) {
			if (!start) start = ts;
			var p = Math.min((ts - start) / dur, 1);
			var eased = 1 - Math.pow(1 - p, 3);
			$el.text(Math.round(from + (target - from) * eased).toLocaleString());
			if (p < 1) { requestAnimationFrame(step); }
			else { $el.text(target.toLocaleString()); }
		}
		requestAnimationFrame(step);
	}

	function trendArrow(current, prev) {
		if (!prev || prev === 0) return '<span class="trend-flat">— <?php echo esc_js( __( 'no comparison data', 'dadsfam-seo' ) ); ?></span>';
		var pct = Math.round(((current - prev) / prev) * 100);
		if (pct > 0)  return '<span class="trend-up">↑ +' + pct + '% <?php echo esc_js( __( 'vs prev period', 'dadsfam-seo' ) ); ?></span>';
		if (pct < 0)  return '<span class="trend-down">↓ ' + pct + '% <?php echo esc_js( __( 'vs prev period', 'dadsfam-seo' ) ); ?></span>';
		return '<span class="trend-flat">→ <?php echo esc_js( __( 'no change', 'dadsfam-seo' ) ); ?></span>';
	}

	function loadAnalytics(days) {
		$.ajax({
			url: restBase + '?days=' + days,
			method: 'GET',
			beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', ajaxNonce); }
		}).done(function(data){
			if (data.error) return;

			// ── Normalise data (handle empty arrays from PHP) ───────────────
			var clicks      = parseInt((data.totals      || {}).clicks)      || 0;
			var impressions = parseInt((data.totals      || {}).impressions) || 0;
			var prevClicks  = parseInt((data.prev_totals || {}).clicks)      || 0;
			var prevImp     = parseInt((data.prev_totals || {}).impressions) || 0;
			var engines     = (data.engine_stats && !Array.isArray(data.engine_stats)) ? data.engine_stats : {};
			var decay       = Array.isArray(data.decay) ? data.decay : [];
			var topPosts    = Array.isArray(data.top_posts) ? data.top_posts : [];
			var timeline    = Array.isArray(data.timeline) ? data.timeline : [];

			// ── Summary cards ──────────────────────────────────────────────
			try {
				$('#dfseo-total-clicks, #dfseo-total-impressions').addClass('dfx-counting');
				rollNumber($('#dfseo-total-clicks'), clicks);
				rollNumber($('#dfseo-total-impressions'), impressions);
				$('#dfseo-clicks-trend').html(trendArrow(clicks, prevClicks));
				$('#dfseo-impressions-trend').html(trendArrow(impressions, prevImp));

				var engKeys   = Object.keys(engines).sort(function(a,b){ return engines[b]-engines[a]; });
				var topEngine = engKeys[0] || '—';
				var topCount  = engines[topEngine] || 0;
				$('#dfseo-top-engine').text(topEngine);
				$('#dfseo-engine-sub').text(topCount.toLocaleString() + ' <?php echo esc_js( __( 'visits (all time)', 'dadsfam-seo' ) ); ?>');
				$('#dfseo-decay-count').text(decay.length);
			} catch(e) { console.error('DFSEO analytics cards:', e); }

			// ── Top posts ─────────────────────────────────────────────
			try {
				if (topPosts.length === 0) {
					$('#dfseo-top-posts-body').html('<tr><td colspan="2" class="dfseo-muted" style="text-align:center;padding:20px"><?php echo esc_js( __( 'No data yet.', 'dadsfam-seo' ) ); ?></td></tr>');
				} else {
					var rows = topPosts.map(function(p){
						return '<tr><td><a href="'+p.url+'" target="_blank">'+$('<span>').text(p.title).html()+'</a></td><td style="text-align:right;font-weight:700">'+parseInt(p.clicks).toLocaleString()+'</td></tr>';
					});
					$('#dfseo-top-posts-body').html(rows.join(''));
				}
			} catch(e) { console.error('DFSEO top posts:', e); }

			// ── Engine breakdown ────────────────────────────────────────
			try {
				var total_engine = Object.values(engines).reduce(function(a,b){ return a+b; }, 0);
				if (total_engine === 0) {
					$('#dfseo-engine-breakdown').html('<p class="dfseo-muted" style="text-align:center;padding:20px"><?php echo esc_js( __( 'No data yet — appears once organic traffic arrives.', 'dadsfam-seo' ) ); ?></p>');
				} else {
					var bars = Object.keys(engines).sort(function(a,b){ return engines[b]-engines[a]; }).slice(0,8).map(function(name, i){
						var pct = Math.round((engines[name] / total_engine) * 100);
						return '<div class="dfseo-engine-bar"><span class="dfseo-engine-name">'+name+'</span><div class="dfseo-engine-track"><div class="dfseo-engine-fill" data-pct="'+pct+'" style="width:0%;transition-delay:'+(i*90)+'ms"></div></div><span class="dfseo-engine-pct">'+pct+'%</span></div>';
					});
					$('#dfseo-engine-breakdown').html(bars.join(''));
					// fluidly fill the bars after they're in the DOM
					requestAnimationFrame(function(){ requestAnimationFrame(function(){
						$('#dfseo-engine-breakdown .dfseo-engine-fill').each(function(){
							this.style.width = (reduceMotion ? this.getAttribute('data-pct') : this.getAttribute('data-pct')) + '%';
						});
					}); });
				}
			} catch(e) { console.error('DFSEO engine breakdown:', e); $('#dfseo-engine-breakdown').html('<p class="dfseo-muted" style="text-align:center;padding:20px">Unable to load engine data.</p>'); }

			// ── Content decay ──────────────────────────────────────────
			try {
				if (decay.length === 0) {
					$('#dfseo-decay-section').html('<p class="dfseo-muted" style="text-align:center;padding:20px">✅ <?php echo esc_js( __( 'No significant traffic drops detected — great job keeping content fresh!', 'dadsfam-seo' ) ); ?></p>');
				} else {
					var decayRows = decay.map(function(d){
						return '<div class="dfseo-decay-row"><div class="dfseo-decay-title"><a href="'+d.url+'" target="_blank">'+$('<span>').text(d.title).html()+'</a></div><div><span class="dfseo-muted" style="font-size:12px">'+d.prev+' → '+d.current+' <?php echo esc_js( __( 'clicks', 'dadsfam-seo' ) ); ?></span></div><div class="dfseo-decay-change">'+d.change+'%</div></div>';
					});
					$('#dfseo-decay-section').html(decayRows.join(''));
				}
			} catch(e) { console.error('DFSEO decay:', e); $('#dfseo-decay-section').html('<p class="dfseo-muted" style="text-align:center;padding:20px">Unable to load decay data.</p>'); }

			// ── Chart (last — so it never blocks other sections) ───────
			try {
				var labels = timeline.map(function(r){ return r.date_recorded; });
				var clicks_data = timeline.map(function(r){ return parseInt(r.clicks)||0; });
				if (labels.length === 0) {
					$('#dfseo-analytics-chart').hide();
					$('#dfseo-chart-empty').show();
				} else {
					$('#dfseo-analytics-chart').show();
					$('#dfseo-chart-empty').hide();

					if (chart) {
						// Update in place — never destroy/recreate (kills lag + flicker)
						chart.data.labels = labels;
						chart.data.datasets[0].data = clicks_data;
						chart.update('none');
					} else {
					var ctx = document.getElementById('dfseo-analytics-chart').getContext('2d');

					// Gradient fill under the line (matches container height)
					var grad = ctx.createLinearGradient(0, 0, 0, 280);
					grad.addColorStop(0,   'rgba(245,158,11,.35)');
					grad.addColorStop(0.5, 'rgba(245,158,11,.12)');
					grad.addColorStop(1,   'rgba(245,158,11,0)');

					// Glow plugin — draws a soft shadow under the line
					var glowPlugin = {
						id: 'dfseoGlow',
						beforeDatasetsDraw: function(c) {
							var cx = c.ctx;
							cx.save();
							cx.shadowColor = 'rgba(217,119,6,.45)';
							cx.shadowBlur = 14;
							cx.shadowOffsetY = 4;
						},
						afterDatasetsDraw: function(c) { c.ctx.restore(); }
					};

					chart = new Chart(ctx, {
						type: 'line',
						data: {
							labels: labels,
							datasets: [{
								label: '<?php echo esc_js( __( 'Organic Clicks', 'dadsfam-seo' ) ); ?>',
								data: clicks_data,
								borderColor: '#d97706',
								backgroundColor: grad,
								borderWidth: 3,
								pointRadius: 0,
								pointHoverRadius: 7,
								pointHoverBackgroundColor: '#fff',
								pointHoverBorderColor: '#d97706',
								pointHoverBorderWidth: 3,
								fill: true,
								tension: 0.4,
								cubicInterpolationMode: 'monotone'
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: true,
							aspectRatio: 2.3,
							interaction: { intersect: false, mode: 'index' },
							animation: reduceMotion ? false : { duration: 550, easing: 'easeOutQuart' },
							plugins: {
								legend: { display: false },
								tooltip: {
									backgroundColor: 'rgba(17,24,39,.92)',
									titleColor: '#fbbf24',
									bodyColor: '#fff',
									padding: 12,
									cornerRadius: 10,
									displayColors: false,
									titleFont: { weight: '700' },
									callbacks: {
										label: function(c){ return c.parsed.y.toLocaleString() + ' <?php echo esc_js( __( 'clicks', 'dadsfam-seo' ) ); ?>'; }
									}
								}
							},
							scales: {
								x: { grid: { display:false }, ticks: { maxTicksLimit:10, font:{size:11}, color:'#9ca3af' } },
								y: { beginAtZero: true, grid: { color:'rgba(0,0,0,.04)' }, ticks: { precision:0, font:{size:11}, color:'#9ca3af' } }
							}
						},
						plugins: [glowPlugin]
					});
					chartFirst = false;
					}
				}
			} catch(e) {
				console.error('DFSEO chart:', e);
				$('#dfseo-analytics-chart').hide();
				$('#dfseo-chart-empty').show().find('strong').text('Chart unavailable — <?php echo esc_js( __( 'check browser console for details.', 'dadsfam-seo' ) ); ?>');
			}

			$('.dfseo-analytics-last-updated').text('· <?php echo esc_js( __( 'updated', 'dadsfam-seo' ) ); ?> ' + new Date().toLocaleTimeString());
			var $dot = $('#dfseo-analytics-live .dfseo-live-dot');
			$dot.addClass('dfx-ping'); setTimeout(function(){ $dot.removeClass('dfx-ping'); }, 700);
		}).fail(function(){
			$('#dfseo-top-posts-body').html('<tr><td colspan="2" class="dfseo-muted" style="text-align:center;padding:16px"><?php echo esc_js( __( 'Could not load analytics. Please refresh.', 'dadsfam-seo' ) ); ?></td></tr>');
			$('#dfseo-engine-breakdown, #dfseo-decay-section').html('<p class="dfseo-muted" style="text-align:center;padding:16px"><?php echo esc_js( __( 'Could not load data.', 'dadsfam-seo' ) ); ?></p>');
		});
	}

	// Date range buttons
	var currentDays = 30;
	$('.dfseo-range-btn').on('click', function(){
		$('.dfseo-range-btn').removeClass('active');
		$(this).addClass('active');
		currentDays = $(this).data('days');
		loadAnalytics(currentDays);
		loadGscKeywords(currentDays);
	});

	// ── Live auto-refresh every 45s (pauses when the tab is hidden) ────
	setInterval(function(){
		if (document.hidden) return;
		loadAnalytics(currentDays);
	}, 45000);

	// ── Google Search Console keywords ─────────────────────────────────
	var gscRest = <?php echo wp_json_encode( rest_url( 'dfseo/v1/gsc/keywords' ) ); ?>;
	function loadGscKeywords(days) {
		var $box = $('#dfseo-gsc-keywords');
		$box.html('<p class="dfseo-muted" style="text-align:center;padding:20px"><?php echo esc_js( __( 'Loading keyword data from Google…', 'dadsfam-seo' ) ); ?></p>');
		$.ajax({
			url: gscRest + '?days=' + days,
			method: 'GET',
			beforeSend: function(xhr){ xhr.setRequestHeader('X-WP-Nonce', ajaxNonce); }
		}).done(function(d){
			try {
				if (d.error === 'not_configured') {
					$box.html('<div style="padding:20px;text-align:center">'
						+ '<p style="margin:0 0 8px;font-size:13px"><strong><?php echo esc_js( __( 'See the actual keywords people type into Google to find your site', 'dadsfam-seo' ) ); ?></strong> — <?php echo esc_js( __( 'clicks, impressions, CTR and ranking position per keyword.', 'dadsfam-seo' ) ); ?></p>'
						+ '<p class="dfseo-muted" style="font-size:12px;margin:0 0 6px"><?php echo esc_js( __( 'One-time setup: paste your Google service account JSON under Settings → Instant Indexing → Google, enable the "Search Console API" in Google Cloud, and add the service account email as a user in Search Console.', 'dadsfam-seo' ) ); ?></p>'
						+ '<a href="<?php echo esc_url( admin_url( 'admin.php?page=dfseo-settings&tab=indexing#idx-config' ) ); ?>" class="dfseo-btn dfseo-btn-primary dfseo-btn-sm" style="margin-top:6px"><?php echo esc_js( __( 'Open Setup', 'dadsfam-seo' ) ); ?></a>'
						+ '</div>');
					return;
				}
				if (d.error === 'auth_failed') {
					$box.html('<p class="dfseo-muted" style="text-align:center;padding:20px">⚠️ <?php echo esc_js( __( 'Could not authenticate with Google. Check that the Search Console API is enabled in Google Cloud and the JSON key is valid.', 'dadsfam-seo' ) ); ?></p>');
					return;
				}
				if (d.error === 'site_not_found') {
					$box.html('<p class="dfseo-muted" style="text-align:center;padding:20px">⚠️ <?php echo esc_js( __( 'Google could not find this site in your Search Console account. Add the service account email as a user (Full permission) in Search Console → Settings → Users.', 'dadsfam-seo' ) ); ?></p>');
					return;
				}
				var kws = d.keywords || [];
				if (!kws.length) {
					$box.html('<p class="dfseo-muted" style="text-align:center;padding:20px"><?php echo esc_js( __( 'Connected ✓ — no keyword data for this period yet. Google typically shows data with a 2-3 day delay.', 'dadsfam-seo' ) ); ?></p>');
					return;
				}
				$('#dfseo-gsc-meta').text((d.cached ? '<?php echo esc_js( __( 'cached', 'dadsfam-seo' ) ); ?> · ' : '') + d.site);
				var rows = kws.map(function(k, i){
					var posColor = k.position <= 10 ? '#16a34a' : (k.position <= 20 ? '#d97706' : '#6b7280');
					return '<tr>'
						+ '<td style="color:#9ca3af;width:24px">' + (i+1) + '</td>'
						+ '<td style="font-weight:600">' + $('<span>').text(k.query).html() + '</td>'
						+ '<td style="text-align:right">' + k.clicks.toLocaleString() + '</td>'
						+ '<td style="text-align:right">' + k.impressions.toLocaleString() + '</td>'
						+ '<td style="text-align:right">' + k.ctr + '%</td>'
						+ '<td style="text-align:right;font-weight:700;color:' + posColor + '">' + k.position + '</td>'
						+ '</tr>';
				});
				$box.html('<table class="dfseo-table"><thead><tr>'
					+ '<th></th>'
					+ '<th><?php echo esc_js( __( 'Keyword', 'dadsfam-seo' ) ); ?></th>'
					+ '<th style="text-align:right"><?php echo esc_js( __( 'Clicks', 'dadsfam-seo' ) ); ?></th>'
					+ '<th style="text-align:right"><?php echo esc_js( __( 'Impressions', 'dadsfam-seo' ) ); ?></th>'
					+ '<th style="text-align:right">CTR</th>'
					+ '<th style="text-align:right"><?php echo esc_js( __( 'Position', 'dadsfam-seo' ) ); ?></th>'
					+ '</tr></thead><tbody>' + rows.join('') + '</tbody></table>');
			} catch(e) { console.error('DFSEO GSC:', e); }
		}).fail(function(){
			$box.html('<p class="dfseo-muted" style="text-align:center;padding:20px"><?php echo esc_js( __( 'Could not load keyword data.', 'dadsfam-seo' ) ); ?></p>');
		});
	}

	// Load on init
	loadAnalytics(30);
	loadGscKeywords(30);
});
</script>
