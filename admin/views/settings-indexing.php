<?php
/**
 * Settings — Instant Indexing tab
 * Layout: Submit → Configuration → History  (matches SiteSEO pattern)
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$indexing   = new DFSEO_Indexing();
$key        = $indexing->get_key();
$key_url    = home_url( '/' . $key . '.txt' );
$auto       = get_option( DFSEO_Indexing::OPT_AUTO_SUBMIT, '1' ) === '1';
$google_key = get_option( DFSEO_Indexing::OPT_GOOGLE_KEY, '' );    // simple API key
$google_json= get_option( DFSEO_Indexing::OPT_GOOGLE_JSON, '' );   // service account JSON
$log        = $indexing->get_log();
$premium    = dfseo_is_premium();
$nonce      = wp_create_nonce( 'dfseo_meta_box' );
$ajax_url   = admin_url( 'admin-ajax.php' );
?>
<div class="dfseo-settings-section">

<!-- ── Tab switcher ───────────────────────────────────────────────────── -->
<div class="dfseo-idx-tabs">
	<button type="button" class="dfseo-idx-tab active" data-panel="submit">📤 <?php esc_html_e( 'Submit URLs', 'dadsfam-seo' ); ?></button>
	<button type="button" class="dfseo-idx-tab" data-panel="config">⚙️ <?php esc_html_e( 'Configuration', 'dadsfam-seo' ); ?></button>
	<button type="button" class="dfseo-idx-tab" data-panel="history">📋 <?php esc_html_e( 'History', 'dadsfam-seo' ); ?> <span class="dfseo-idx-badge"><?php echo count( $log ); ?></span></button>
</div>

<!-- ══ Panel: Submit ════════════════════════════════════════════════════ -->
<div class="dfseo-idx-panel active" id="dfseo-idx-submit">
	<div class="dfseo-idx-info-box">
		<strong>ℹ️ <?php esc_html_e( 'How does this work?', 'dadsfam-seo' ); ?></strong>
		<ol>
			<li><?php esc_html_e( 'Configure your API keys in the Configuration tab.', 'dadsfam-seo' ); ?></li>
			<li><?php esc_html_e( 'Enter the URLs you want to index below, one per line.', 'dadsfam-seo' ); ?></li>
			<li><?php esc_html_e( 'Select search engines and the action type.', 'dadsfam-seo' ); ?></li>
			<li><?php esc_html_e( 'Click "Submit URLs" — results appear instantly in History.', 'dadsfam-seo' ); ?></li>
		</ol>
		<p class="dfseo-hint" style="margin:8px 0 0">💡 <?php esc_html_e( 'Posts are also submitted automatically on publish/update/delete when Auto-Submit is enabled. You can submit up to 100 URLs at once here.', 'dadsfam-seo' ); ?></p>
	</div>

	<div class="dfseo-idx-row" style="gap:24px;align-items:flex-start">
		<!-- URL list -->
		<div style="flex:1">
			<label class="dfseo-label" for="dfseo-idx-urls"><?php esc_html_e( 'Submit URLs for Indexing', 'dadsfam-seo' ); ?></label>
			<textarea id="dfseo-idx-urls" rows="8" class="dfseo-textarea" placeholder="https://www.example.com/page-1&#10;https://www.example.com/page-2&#10;https://www.example.com/page-3"></textarea>
			<p class="dfseo-hint">💡 <?php esc_html_e( 'One URL per line. Must be full URLs including https://', 'dadsfam-seo' ); ?></p>
		</div>

		<!-- Engine + action options -->
		<div style="min-width:220px">
			<div class="dfseo-label" style="margin-bottom:8px"><?php esc_html_e( 'Search Engines', 'dadsfam-seo' ); ?></div>
			<label class="dfseo-check-label" style="margin-bottom:6px">
				<input type="checkbox" id="dfseo-idx-bing" checked>
				<span>📘 <?php esc_html_e( 'Bing / IndexNow', 'dadsfam-seo' ); ?></span>
			</label>
			<label class="dfseo-check-label" style="margin-bottom:16px">
				<input type="checkbox" id="dfseo-idx-google" <?php checked( ! empty( $google_json ) ); ?> <?php if ( ! $premium ) echo 'disabled title="' . esc_attr__( 'Google API requires Premium', 'dadsfam-seo' ) . '"'; ?>>
				<span>🔍 <?php esc_html_e( 'Google', 'dadsfam-seo' ); ?><?php if ( ! $premium ) echo ' <small class="dfseo-muted">(' . esc_html__( 'Premium', 'dadsfam-seo' ) . ')</small>'; ?></span>
			</label>

			<div class="dfseo-label" style="margin-bottom:8px"><?php esc_html_e( 'Which action for Google?', 'dadsfam-seo' ); ?></div>
			<label class="dfseo-check-label" style="flex-direction:row;align-items:center;gap:6px;margin-bottom:6px">
				<input type="radio" name="dfseo-idx-action" value="URL_UPDATED" checked>
				<?php esc_html_e( 'Update URLs', 'dadsfam-seo' ); ?>
			</label>
			<label class="dfseo-check-label" style="flex-direction:row;align-items:center;gap:6px;margin-bottom:20px">
				<input type="radio" name="dfseo-idx-action" value="URL_DELETED">
				<?php esc_html_e( 'Remove URLs', 'dadsfam-seo' ); ?>
			</label>

			<button type="button" id="dfseo-idx-submit-btn" class="dfseo-btn dfseo-btn-primary" style="width:100%">
				<span class="dfseo-btn-text">⚡ <?php esc_html_e( 'Submit URLs', 'dadsfam-seo' ); ?></span>
				<span class="dfseo-btn-loading" style="display:none">⏳ <?php esc_html_e( 'Submitting…', 'dadsfam-seo' ); ?></span>
			</button>
		</div>
	</div>

	<!-- Live results table -->
	<div id="dfseo-idx-submit-results" style="display:none;margin-top:20px">
		<h4 style="margin:0 0 8px"><?php esc_html_e( 'Submission Results', 'dadsfam-seo' ); ?></h4>
		<table class="dfseo-table" id="dfseo-idx-results-table">
			<thead><tr>
				<th><?php esc_html_e( 'URL', 'dadsfam-seo' ); ?></th>
				<th>🔍 <?php esc_html_e( 'Google', 'dadsfam-seo' ); ?></th>
				<th>📘 <?php esc_html_e( 'Bing / IndexNow', 'dadsfam-seo' ); ?></th>
			</tr></thead>
			<tbody id="dfseo-idx-results-body"></tbody>
		</table>
	</div>
</div>

<!-- ══ Panel: Configuration ═════════════════════════════════════════════ -->
<div class="dfseo-idx-panel" id="dfseo-idx-config">

	<!-- IndexNow Key -->
	<div class="dfseo-idx-card">
		<h3>📘 <?php esc_html_e( 'Bing / IndexNow', 'dadsfam-seo' ); ?> <span class="dfseo-badge-free">FREE</span></h3>
		<p class="dfseo-hint"><?php esc_html_e( 'IndexNow instantly notifies Bing, Yandex, DuckDuckGo, Seznam, Naver, and other participating engines. Works automatically — no extra account needed.', 'dadsfam-seo' ); ?></p>
		<table class="dfseo-settings-table">
			<tr>
				<th><?php esc_html_e( 'Instant Indexing Bing API Key', 'dadsfam-seo' ); ?></th>
				<td>
					<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
						<input type="text" id="dfseo-indexnow-key-display" class="dfseo-input" value="<?php echo esc_attr( $key ); ?>" readonly style="font-family:monospace;flex:1">
						<button type="button" id="dfseo-indexnow-regen" class="dfseo-btn dfseo-btn-ghost"><?php esc_html_e( 'Generate key', 'dadsfam-seo' ); ?></button>
					</div>
					<p class="dfseo-hint" style="margin-top:6px">
						<?php esc_html_e( 'The verification file is automatically served at:', 'dadsfam-seo' ); ?>
						<a href="<?php echo esc_url( $key_url ); ?>" target="_blank" id="dfseo-key-file-url"><code><?php echo esc_html( $key_url ); ?></code></a>
					</p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Auto-Submit on Publish', 'dadsfam-seo' ); ?></th>
				<td>
					<label class="dfseo-check-label">
						<input type="checkbox" name="dfseo_indexing_auto_submit" value="1" <?php checked( $auto ); ?>>
						<?php esc_html_e( 'Automatically submit to IndexNow whenever a post is published, updated, or removed', 'dadsfam-seo' ); ?>
					</label>
					<p class="dfseo-hint">💡 <?php esc_html_e( 'Recommended: on. One submission covers Bing, DuckDuckGo, Yandex, and all IndexNow participants at once.', 'dadsfam-seo' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<!-- Google -->
	<div class="dfseo-idx-card" style="margin-top:20px">
		<h3>🔍 <?php esc_html_e( 'Google Indexing API', 'dadsfam-seo' ); ?> <span class="dfseo-badge-pro">⭐ PREMIUM</span></h3>
		<p class="dfseo-hint"><?php esc_html_e( 'Submit URLs directly to Google for priority crawling. Requires a Google Cloud service account with the Indexing API enabled and Owner permission in Google Search Console.', 'dadsfam-seo' ); ?></p>

		<?php if ( ! $premium ) : ?>
			<div style="padding:16px;background:var(--df-amber-light);border-radius:var(--df-radius);border:1px solid var(--df-amber);font-size:13px">
				⭐ <?php printf( esc_html__( 'Activate your licence to use the Google Indexing API. %sUnlock Premium →%s', 'dadsfam-seo' ), '<a href="' . esc_url( admin_url( 'admin.php?page=dfseo-license' ) ) . '">', '</a>' ); ?>
			</div>
		<?php else : ?>
			<div class="dfseo-idx-setup-steps">
				<strong><?php esc_html_e( 'Quick setup:', 'dadsfam-seo' ); ?></strong>
				<ol style="margin:6px 0 0;padding-left:20px;font-size:13px;line-height:1.9">
					<li><?php printf( esc_html__( '%sGoogle Cloud Console%s → Enable the Indexing API → Create a Service Account → Download JSON key.', 'dadsfam-seo' ), '<a href="https://console.cloud.google.com" target="_blank">', '</a>' ); ?></li>
					<li><?php printf( esc_html__( '%sGoogle Search Console%s → Settings → Users → add the service account email as an Owner.', 'dadsfam-seo' ), '<a href="https://search.google.com/search-console" target="_blank">', '</a>' ); ?></li>
					<li><?php esc_html_e( 'Paste the full JSON key contents below.', 'dadsfam-seo' ); ?></li>
				</ol>
			</div>
			<table class="dfseo-settings-table" style="margin-top:12px">
				<tr>
					<th><?php esc_html_e( 'Instant Indexing Google API Key', 'dadsfam-seo' ); ?></th>
					<td>
						<input type="text" name="dfseo_google_indexing_key" class="dfseo-input"
							value="<?php echo esc_attr( $google_key ); ?>"
							placeholder="AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX">
						<p class="dfseo-hint">💡 <?php esc_html_e( 'Paste your Google API key exactly as-is — it starts with AIzaSy. Get it from Google Cloud Console → Credentials → Create API Key, restricted to the Indexing API.', 'dadsfam-seo' ); ?></p>
					</td>
				</tr>
			</table>
		<?php endif; ?>
	</div>
</div>

<!-- ══ Panel: History ════════════════════════════════════════════════════ -->
<div class="dfseo-idx-panel" id="dfseo-idx-history">
	<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
		<div>
			<strong><?php esc_html_e( 'Most Recent Indexing API Requests', 'dadsfam-seo' ); ?></strong>
			<span class="dfseo-muted" style="margin-left:8px;font-size:12px">(<?php echo count( $log ); ?> <?php esc_html_e( 'entries', 'dadsfam-seo' ); ?>)</span>
		</div>
		<button type="button" id="dfseo-clear-log" class="dfseo-btn dfseo-btn-ghost dfseo-btn-sm">🗑 <?php esc_html_e( 'Clear History', 'dadsfam-seo' ); ?></button>
	</div>
	<?php if ( empty( $log ) ) : ?>
		<p class="dfseo-hint" style="text-align:center;padding:32px 0"><?php esc_html_e( 'No submissions yet. Submit some URLs from the Submit URLs tab.', 'dadsfam-seo' ); ?></p>
	<?php else : ?>
		<table class="dfseo-table">
			<thead><tr>
				<th><?php esc_html_e( 'Time & Date', 'dadsfam-seo' ); ?></th>
				<th><?php esc_html_e( 'URLs', 'dadsfam-seo' ); ?></th>
				<th>🔍 <?php esc_html_e( 'Google Response', 'dadsfam-seo' ); ?></th>
				<th>📘 <?php esc_html_e( 'Bing Response', 'dadsfam-seo' ); ?></th>
			</tr></thead>
			<tbody>
				<?php foreach ( $log as $entry ) :
					$g = $entry['google_response'] ?? 'N/A';
					$b = $entry['bing_response']  ?? 'N/A';
					$g_ok = in_array( $g, ['200','202'], true );
					$b_ok = in_array( $b, ['200','202'], true );
				?>
				<tr>
					<td style="white-space:nowrap;font-size:12px;color:var(--df-gray-500)"><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $entry['time'] ) ); ?></td>
					<td><a href="<?php echo esc_url( $entry['url'] ); ?>" target="_blank" rel="noopener noreferrer" style="word-break:break-all;font-size:13px"><?php echo esc_html( $entry['url'] ); ?></a></td>
					<td><?php if ( $g_ok ) : ?><span class="dfseo-score-badge great"><?php echo esc_html( $g ); ?></span>
						<?php elseif ( $g === 'N/A' ) : ?><span class="dfseo-muted"><?php echo esc_html( $g . ' (Auto)' ); ?></span>
						<?php else : ?><span class="dfseo-score-badge poor"><?php echo esc_html( $g ); ?></span><?php endif; ?>
					</td>
					<td><?php if ( $b_ok ) : ?><span class="dfseo-score-badge great"><?php echo esc_html( $b . ' (Auto)' ); ?></span>
						<?php elseif ( $b === 'N/A' ) : ?><span class="dfseo-muted"><?php echo esc_html( $b ); ?></span>
						<?php else : ?><span class="dfseo-score-badge poor"><?php echo esc_html( $b ); ?></span><?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

</div><!-- .dfseo-settings-section -->

<style>
.dfseo-idx-tabs { display:flex; gap:4px; border-bottom:2px solid var(--df-gray-200); margin-bottom:20px; }
.dfseo-idx-tab  { background:none; border:none; padding:10px 18px 12px; font-size:13px; font-weight:600; color:var(--df-gray-500); cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:color .15s, border-color .15s; }
.dfseo-idx-tab:hover { color:var(--df-gray-800); }
.dfseo-idx-tab.active { color:var(--df-blue); border-bottom-color:var(--df-amber); }
.dfseo-idx-badge { background:var(--df-gray-200); color:var(--df-gray-600); border-radius:99px; padding:1px 7px; font-size:11px; font-weight:700; margin-left:4px; }
.dfseo-idx-panel { display:none; }
.dfseo-idx-panel.active { display:block; }
.dfseo-idx-info-box { background:#eff6ff; border-left:4px solid var(--df-blue); border-radius:0 8px 8px 0; padding:14px 18px; margin-bottom:20px; font-size:13px; line-height:1.7; }
.dfseo-idx-info-box ol { margin:6px 0 0; padding-left:18px; }
.dfseo-idx-row { display:flex; gap:16px; flex-wrap:wrap; }
.dfseo-idx-card { background:#fff; border:1px solid var(--df-gray-200); border-radius:var(--df-radius); padding:20px 24px; }
.dfseo-idx-card h3 { margin:0 0 6px; font-size:15px; display:flex; align-items:center; gap:8px; }
.dfseo-idx-setup-steps { background:var(--df-gray-50); border-radius:var(--df-radius-sm); padding:12px 16px; font-size:13px; margin-bottom:4px; }
.dfseo-badge-free { background:var(--df-green-light); color:var(--df-green); padding:2px 10px; border-radius:99px; font-size:11px; font-weight:700; }
.dfseo-badge-pro  { background:var(--df-amber-light); color:var(--df-amber-dark); padding:2px 10px; border-radius:99px; font-size:11px; font-weight:700; }
#dfseo-idx-results-body td { font-size:12px; word-break:break-all; }
</style>

<script>
jQuery(function($){
	var nonce   = <?php echo wp_json_encode( $nonce ); ?>;
	var ajaxUrl = <?php echo wp_json_encode( $ajax_url ); ?>;

	// ── Tab switching — hash persists active tab through page reloads ──────
	function switchTab(panel) {
		$('.dfseo-idx-tab').removeClass('active');
		$('.dfseo-idx-panel').removeClass('active');
		$('.dfseo-idx-tab[data-panel="' + panel + '"]').addClass('active');
		$('#dfseo-idx-' + panel).addClass('active');
		if (history.replaceState) {
			history.replaceState(null, '', location.pathname + location.search + '#idx-' + panel);
		}
	}
	$('.dfseo-idx-tab').on('click', function(){
		switchTab($(this).data('panel'));
	});
	// Restore tab from URL hash on page load (survives Save Settings reload)
	var hash = location.hash.replace('#idx-', '');
	if (hash === 'config' || hash === 'history' || hash === 'submit') {
		switchTab(hash);
	}

	// ── Generate new IndexNow key ──────────────────────────────────────────
	$('#dfseo-indexnow-regen').on('click', function(){
		if (!confirm(<?php echo wp_json_encode( __( 'Generate a new key? The old verification file URL will stop working.', 'dadsfam-seo' ) ); ?>)) return;
		$.post(ajaxUrl, {action:'dfseo_generate_indexnow_key', nonce}, function(r){
			if (r.success) {
				$('#dfseo-indexnow-key-display').val(r.data.key);
				$('#dfseo-key-file-url').attr('href', r.data.file_url).find('code').text(r.data.file_url);
			}
		});
	});

	// ── Bulk submit ────────────────────────────────────────────────────────
	$('#dfseo-idx-submit-btn').on('click', function(){
		var urls    = $('#dfseo-idx-urls').val().trim();
		var doBing  = $('#dfseo-idx-bing').is(':checked') ? '1' : '';
		var doGoogle= $('#dfseo-idx-google').is(':checked') ? '1' : '';
		var action  = $('input[name="dfseo-idx-action"]:checked').val();

		if (!urls) { alert(<?php echo wp_json_encode( __( 'Please enter at least one URL.', 'dadsfam-seo' ) ); ?>); return; }

		var $btn = $(this);
		$btn.find('.dfseo-btn-text').hide();
		$btn.find('.dfseo-btn-loading').show();
		$btn.prop('disabled', true);

		$.post(ajaxUrl, {
			action: 'dfseo_bulk_index_submit',
			nonce: nonce,
			urls: urls,
			action_type: action,
			do_bing: doBing,
			do_google: doGoogle
		}, function(r){
			$btn.find('.dfseo-btn-text').show();
			$btn.find('.dfseo-btn-loading').hide();
			$btn.prop('disabled', false);

			if (r.success && r.data.results) {
				var rows = r.data.results.map(function(res){
					var gBadge = res.g_result === 'N/A' ? '<span class="dfseo-muted">N/A (Auto)</span>'
						: (res.g_result === '200' || res.g_result === '202')
							? '<span class="dfseo-score-badge great">'+res.g_result+'</span>'
							: '<span class="dfseo-score-badge poor">'+res.g_result+'</span>';
					var bBadge = res.b_result === 'N/A' ? '<span class="dfseo-muted">N/A</span>'
						: (res.b_result === '200' || res.b_result === '202')
							? '<span class="dfseo-score-badge great">'+res.b_result+' ✓</span>'
							: '<span class="dfseo-score-badge poor">'+res.b_result+'</span>';
					return '<tr><td><a href="'+res.url+'" target="_blank">'+res.url+'</a></td><td>'+gBadge+'</td><td>'+bBadge+'</td></tr>';
				});
				$('#dfseo-idx-results-body').html(rows.join(''));
				$('#dfseo-idx-submit-results').show();
			} else {
				alert(r.data ? (r.data.message || <?php echo wp_json_encode( __( 'Submission failed.', 'dadsfam-seo' ) ); ?>) : <?php echo wp_json_encode( __( 'Submission failed.', 'dadsfam-seo' ) ); ?>);
			}
		}).fail(function(){ $btn.prop('disabled', false); });
	});

	// ── Clear log ──────────────────────────────────────────────────────────
	$('#dfseo-clear-log').on('click', function(){
		if (!confirm(<?php echo wp_json_encode( __( 'Clear all history?', 'dadsfam-seo' ) ); ?>)) return;
		$.post(ajaxUrl, {action:'dfseo_clear_indexing_log', nonce}, function(r){
			if (r.success) location.reload();
		});
	});
});
</script>
