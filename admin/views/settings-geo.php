<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dfseo-settings-section">

	<div style="background:linear-gradient(135deg,#1a4fa0,#2563eb);border-radius:10px;padding:18px 22px;color:#fff;margin-bottom:22px">
		<h2 style="margin:0 0 6px;color:#fff;font-size:16px">🤖 <?php esc_html_e( 'Generative Engine Optimization (GEO)', 'dadsfam-seo' ); ?></h2>
		<p style="margin:0;font-size:13px;opacity:.92;line-height:1.6">
			<?php esc_html_e( 'Traditional SEO gets you ranked in Google\'s blue links. GEO gets your content quoted by AI answer engines — ChatGPT, Google AI Overviews, Perplexity, Gemini, Claude and Bing Copilot. As more people ask AI instead of searching, being citable by these engines is the next frontier. These tools help AI find, understand, and trust your content.', 'dadsfam-seo' ); ?>
		</p>
	</div>

	<!-- ── llms.txt ──────────────────────────────────────────────────────── -->
	<h2>📄 <?php esc_html_e( 'llms.txt — AI Content Map', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php
		printf(
			/* translators: %s: llms.txt URL */
			esc_html__( 'Just as robots.txt guides search crawlers, llms.txt (an emerging standard) gives AI models a clean, curated map of your best content in plain markdown. When enabled, it is served automatically at %s. This helps AI engines understand your site and cite your pages accurately.', 'dadsfam-seo' ),
			'<a href="' . esc_url( home_url( '/llms.txt' ) ) . '" target="_blank"><code>' . esc_html( home_url( '/llms.txt' ) ) . '</code></a>'
		);
	?></p>
	<table class="dfseo-settings-table">
		<tr>
			<th><?php esc_html_e( 'Enable llms.txt', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_geo_llms_enable" value="1" <?php checked( get_option( 'dfseo_geo_llms_enable', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Generate and serve /llms.txt automatically', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Lists your published pages and most recent articles with their descriptions. Updates automatically when you publish or edit content. Recommended: on.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_geo_llms_summary"><?php esc_html_e( 'Site Summary', 'dadsfam-seo' ); ?> <span style="font-weight:400;color:#9ca3af">(<?php esc_html_e( 'optional', 'dadsfam-seo' ); ?>)</span></label></th>
			<td>
				<?php
				$dfseo_site_name = get_bloginfo( 'name' );
				$dfseo_tagline   = get_bloginfo( 'description' );
				$dfseo_example   = trim( $dfseo_site_name . ( $dfseo_tagline ? ' — ' . $dfseo_tagline : '' ) );
				if ( $dfseo_example === '' ) { $dfseo_example = 'My site helps people with…'; }
				?>
				<textarea name="dfseo_geo_llms_summary" id="dfseo_geo_llms_summary" rows="3" class="dfseo-textarea" placeholder="<?php echo esc_attr( $dfseo_example . '. We help customers with…' ); ?>"><?php echo esc_textarea( get_option( 'dfseo_geo_llms_summary', '' ) ); ?></textarea>
				<div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
					<button type="button" class="dfseo-btn dfseo-btn-secondary dfseo-btn-sm" id="dfseo-geo-autofill" data-example="<?php echo esc_attr( $dfseo_example . '.' ); ?>">
						✨ <?php esc_html_e( 'Auto-fill from my site', 'dadsfam-seo' ); ?>
					</button>
					<span class="dfseo-muted" style="font-size:12px"><?php esc_html_e( "Not sure what to write? Tap the button and tweak it.", 'dadsfam-seo' ); ?></span>
				</div>
				<p class="dfseo-hint" style="margin-top:10px">
					💡 <strong><?php esc_html_e( 'What is this?', 'dadsfam-seo' ); ?></strong>
					<?php esc_html_e( 'One or two plain sentences telling an AI what your site is about — like how you\'d describe it to a stranger at a braai. Example:', 'dadsfam-seo' ); ?>
					<em>"<?php echo esc_html( $dfseo_example ); ?>. <?php esc_html_e( 'We sell premium WordPress plugins and offer support to small businesses.', 'dadsfam-seo' ); ?>"</em>
					<?php esc_html_e( 'Leave it blank and the plugin still works fine — this just gives the AI extra context.', 'dadsfam-seo' ); ?>
				</p>
				<script>
				(function(){
					var btn = document.getElementById('dfseo-geo-autofill');
					if (!btn) return;
					btn.addEventListener('click', function(){
						var ta = document.getElementById('dfseo_geo_llms_summary');
						if (ta && !ta.value.trim()) { ta.value = btn.getAttribute('data-example'); ta.focus(); }
						else if (ta) { ta.focus(); }
					});
				})();
				</script>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_geo_llms_post_count"><?php esc_html_e( 'Articles to include', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_geo_llms_post_count" id="dfseo_geo_llms_post_count" class="dfseo-input dfseo-input-sm">
					<?php foreach ( [ 25, 50, 100, 200 ] as $n ) : ?>
						<option value="<?php echo esc_attr( $n ); ?>" <?php selected( (int) get_option( 'dfseo_geo_llms_post_count', 50 ), $n ); ?>><?php echo esc_html( $n ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'How many of your most recent articles to list. 50 is a good balance for most sites.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>

	<!-- ── AI crawler controls ───────────────────────────────────────────── -->
	<h2 style="margin-top:28px">🕷️ <?php esc_html_e( 'AI Crawler Controls', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'Decide which AI bots may read your site, right from robots.txt. Allowing them is how you become eligible to be cited in AI answers (recommended for most sites that want visibility). Blocking them keeps your content out of AI training and answers. The choice is yours.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<tr>
			<th><?php esc_html_e( 'Manage AI crawlers', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_geo_ai_control" value="1" <?php checked( get_option( 'dfseo_geo_ai_control', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Add AI crawler directives to robots.txt', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'When off, the plugin leaves AI bots completely unmanaged (they follow your normal robots rules). Turn on to take control.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Policy', 'dadsfam-seo' ); ?></th>
			<td>
				<?php $mode = get_option( 'dfseo_geo_ai_mode', 'allow' ); ?>
				<label class="dfseo-check-label" style="margin-bottom:6px"><input type="radio" name="dfseo_geo_ai_mode" value="allow" <?php checked( $mode, 'allow' ); ?>>
				✅ <?php esc_html_e( 'Allow all AI crawlers (best for visibility — become citable in AI answers)', 'dadsfam-seo' ); ?></label><br>
				<label class="dfseo-check-label" style="margin-bottom:6px"><input type="radio" name="dfseo_geo_ai_mode" value="block" <?php checked( $mode, 'block' ); ?>>
				🚫 <?php esc_html_e( 'Block all AI crawlers (keep my content out of AI entirely)', 'dadsfam-seo' ); ?></label><br>
				<label class="dfseo-check-label"><input type="radio" name="dfseo_geo_ai_mode" value="custom" <?php checked( $mode, 'custom' ); ?>>
				⚙️ <?php esc_html_e( 'Custom — let me choose per bot below', 'dadsfam-seo' ); ?></label>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Per-bot (Custom mode)', 'dadsfam-seo' ); ?></th>
			<td>
				<?php $blocked = (array) get_option( 'dfseo_geo_ai_blocked', [] ); ?>
				<p class="dfseo-hint" style="margin-top:0"><?php esc_html_e( 'Tick a bot to BLOCK it. Unticked bots are allowed. Only applies when Policy is set to Custom.', 'dadsfam-seo' ); ?></p>
				<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:6px;margin-top:8px">
					<?php foreach ( DFSEO_GEO::AI_BOTS as $bot => $meta ) : ?>
						<label class="dfseo-check-label" style="align-items:flex-start;background:#f9fafb;padding:8px 10px;border-radius:8px">
							<input type="checkbox" name="dfseo_geo_ai_blocked[]" value="<?php echo esc_attr( $bot ); ?>" <?php checked( in_array( $bot, $blocked, true ) ); ?> style="margin-top:3px">
							<span>
								<strong style="font-size:13px"><?php echo esc_html( $meta[0] ); ?></strong>
								<span style="display:block;font-size:11px;color:#6b7280"><?php echo esc_html( $meta[1] ); ?></span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
	</table>

	<!-- ── Speakable ─────────────────────────────────────────────────────── -->
	<h2 style="margin-top:28px">🔊 <?php esc_html_e( 'Speakable Content (Voice & AI Read-Aloud)', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'Adds Speakable schema marking your headline and opening paragraph as the parts best suited to voice assistants and AI read-aloud. Helps with voice search (Google Assistant, Siri) and AI summaries.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<tr>
			<th><?php esc_html_e( 'Enable Speakable schema', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_geo_speakable" value="1" <?php checked( get_option( 'dfseo_geo_speakable', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Add SpeakableSpecification to posts and pages', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Targets your H1 and first paragraph. Recommended: on — it is a safe, lightweight addition that costs nothing and can only help.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>

	<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:14px 18px;margin-top:24px">
		<strong style="font-size:13px">✨ <?php esc_html_e( 'GEO Tip from DadsFam', 'dadsfam-seo' ); ?></strong>
		<p style="margin:6px 0 0;font-size:13px;color:#166534;line-height:1.6">
			<?php esc_html_e( 'AI engines love content that answers questions directly. Use question-style headings ("What is X?", "How do I Y?"), give a clear one- or two-sentence answer right under each heading, add an FAQ section, and back claims with specific facts and numbers. The AI Tools tab can generate FAQ sections and content outlines built exactly this way.', 'dadsfam-seo' ); ?>
		</p>
	</div>

</div>
