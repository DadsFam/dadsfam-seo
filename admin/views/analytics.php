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
		<span class="dfseo-analytics-last-updated dfseo-muted"></span>
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
				<canvas id="dfseo-analytics-chart" height="200" style="max-height:220px"></canvas>
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
.dfseo-engine-fill  { background:var(--df-blue); height:8px; border-radius:99px; transition:width .4s; }
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
				$('#dfseo-total-clicks').text(clicks.toLocaleString());
				$('#dfseo-total-impressions').text(impressions.toLocaleString());
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
					var bars = Object.keys(engines).sort(function(a,b){ return engines[b]-engines[a]; }).slice(0,8).map(function(name){
						var pct = Math.round((engines[name] / total_engine) * 100);
						return '<div class="dfseo-engine-bar"><span class="dfseo-engine-name">'+name+'</span><div class="dfseo-engine-track"><div class="dfseo-engine-fill" style="width:'+pct+'%"></div></div><span class="dfseo-engine-pct">'+pct+'%</span></div>';
					});
					$('#dfseo-engine-breakdown').html(bars.join(''));
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
					if (chart) { chart.destroy(); }
					var ctx = document.getElementById('dfseo-analytics-chart').getContext('2d');
					chart = new Chart(ctx, {
						type: 'line',
						data: {
							labels: labels,
							datasets: [{
								label: '<?php echo esc_js( __( 'Organic Clicks', 'dadsfam-seo' ) ); ?>',
								data: clicks_data,
								borderColor: '#1a4fa0',
								backgroundColor: 'rgba(26,79,160,.08)',
								borderWidth: 2, pointRadius: 3, fill: true, tension: 0.3
							}]
						},
						options: {
							responsive: true,
							plugins: { legend: { display: false } },
							scales: {
								x: { grid: { display:false }, ticks: { maxTicksLimit:10, font:{size:11} } },
								y: { beginAtZero: true, ticks: { precision:0, font:{size:11} } }
							}
						}
					});
				}
			} catch(e) {
				console.error('DFSEO chart:', e);
				$('#dfseo-analytics-chart').hide();
				$('#dfseo-chart-empty').show().find('strong').text('Chart unavailable — <?php echo esc_js( __( 'check browser console for details.', 'dadsfam-seo' ) ); ?>');
			}

			$('.dfseo-analytics-last-updated').text('<?php echo esc_js( __( 'Updated', 'dadsfam-seo' ) ); ?>: ' + new Date().toLocaleTimeString());
		}).fail(function(){
			$('#dfseo-top-posts-body').html('<tr><td colspan="2" class="dfseo-muted" style="text-align:center;padding:16px"><?php echo esc_js( __( 'Could not load analytics. Please refresh.', 'dadsfam-seo' ) ); ?></td></tr>');
			$('#dfseo-engine-breakdown, #dfseo-decay-section').html('<p class="dfseo-muted" style="text-align:center;padding:16px"><?php echo esc_js( __( 'Could not load data.', 'dadsfam-seo' ) ); ?></p>');
		});
	}

	// Date range buttons
	$('.dfseo-range-btn').on('click', function(){
		$('.dfseo-range-btn').removeClass('active');
		$(this).addClass('active');
		loadAnalytics($(this).data('days'));
	});

	// Load on init
	loadAnalytics(30);
});
</script>
