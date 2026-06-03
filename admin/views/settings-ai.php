<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( ! dfseo_is_premium() ) { DFSEO_Core::premium_overlay( 'AI Tools' ); return; } ?>
<div class="dfseo-settings-section">
	<h2>✨ <?php esc_html_e( 'AI Tools Settings', 'dadsfam-seo' ); ?></h2>

	<div class="dfseo-ai-info-box">
		<strong>✨ <?php esc_html_e( 'Powered by Anthropic Claude', 'dadsfam-seo' ); ?></strong>
		<p><?php esc_html_e( 'DadsFam SEO connects to the Anthropic Claude API to generate SEO titles, meta descriptions, keyword ideas, content outlines, and more — all from inside your post editor. Your API key is stored securely on your server and is never sent to the browser or shared with anyone.', 'dadsfam-seo' ); ?></p>
		<p><a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener noreferrer">🔑 <?php esc_html_e( 'Get your free API key from Anthropic →', 'dadsfam-seo' ); ?></a></p>
	</div>

	<table class="dfseo-settings-table">
		<tr>
			<th><label for="dfseo_ai_api_key"><?php esc_html_e( 'Anthropic API Key', 'dadsfam-seo' ); ?></label></th>
			<td>
				<?php $__saved_key = get_option( 'dfseo_ai_api_key', '' ); ?>
				<input type="text" name="dfseo_ai_api_key" id="dfseo_ai_api_key"
					value=""
					class="dfseo-input" style="font-family:monospace"
					placeholder="<?php echo $__saved_key ? esc_attr( 'sk-ant-••••••••••••' . substr( $__saved_key, -6 ) ) : 'sk-ant-api03-...'; ?>"					autocomplete="off">
				<p class="dfseo-hint" style="margin-top:4px">
					<?php if ( $__saved_key ) : ?>
						✅ <?php esc_html_e( 'API key is saved. Leave blank to keep the existing key, or paste a new one to replace it.', 'dadsfam-seo' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'No key saved yet. Paste your key above.', 'dadsfam-seo' ); ?>
					<?php endif; ?>
				</p>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Paste your Anthropic API key here. It starts with "sk-ant-". You can create one for free at console.anthropic.com — the free tier is more than enough for typical SEO use.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_ai_model"><?php esc_html_e( 'Claude Model', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_ai_model" id="dfseo_ai_model" class="dfseo-select">
					<option value="claude-sonnet-4-6" <?php selected( get_option( 'dfseo_ai_model' ), 'claude-sonnet-4-6' ); ?>>claude-sonnet-4 — ⭐ <?php esc_html_e( 'Recommended (best quality/speed balance)', 'dadsfam-seo' ); ?></option>
					<option value="claude-opus-4-6"          <?php selected( get_option( 'dfseo_ai_model' ), 'claude-opus-4-6' ); ?>>claude-opus-4 — 🧠 <?php esc_html_e( 'Highest quality (slower, more expensive)', 'dadsfam-seo' ); ?></option>
					<option value="claude-haiku-4-5-20251001"<?php selected( get_option( 'dfseo_ai_model' ), 'claude-haiku-4-5-20251001' ); ?>>claude-haiku-4 — ⚡ <?php esc_html_e( 'Fastest & cheapest (great for high-volume sites)', 'dadsfam-seo' ); ?></option>
				</select>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Not sure which to pick? Leave it on Sonnet 4 — it writes excellent SEO content and responds quickly. Switch to Haiku if you have hundreds of posts and want to keep API costs minimal.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Track Organic Traffic', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label">
					<input type="checkbox" name="dfseo_track_analytics" value="1" <?php checked( get_option( 'dfseo_track_analytics', '1' ), '1' ); ?>>
					<?php esc_html_e( 'Record organic visits from search engines', 'dadsfam-seo' ); ?>
				</label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'When on, visits that arrive from Google, Bing, Yahoo, DuckDuckGo, Yandex, and Baidu are logged per post. View the data under the Analytics tab. No cookies or personal data are stored — just referrer and page.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_analytics_retention_days"><?php esc_html_e( 'Keep Analytics For', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_analytics_retention_days" id="dfseo_analytics_retention_days" class="dfseo-select dfseo-select-sm">
					<?php foreach ( [ 30 => '30 days', 60 => '60 days', 90 => '90 days', 180 => '6 months', 365 => '1 year' ] as $days => $label ) : ?>
						<option value="<?php echo esc_attr( $days ); ?>" <?php selected( (int) get_option( 'dfseo_analytics_retention_days', 180 ), $days ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Older data is automatically deleted to keep your database lean. 180 days is a good balance between historical insight and database size.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>
</div>
