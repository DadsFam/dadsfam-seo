<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dfseo-settings-section">
	<h2><?php esc_html_e( 'Social Media Profiles', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint"><?php esc_html_e( 'Add your social profile URLs. These are included in your Organisation schema as sameAs links and used in OpenGraph tags.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<?php
		$socials = [
			'dfseo_social_facebook'  => [ '📘 Facebook',           'https://facebook.com/yourpage' ],
			'dfseo_social_twitter'   => [ '𝕏 X / Twitter',         'https://twitter.com/yourhandle' ],
			'dfseo_social_instagram' => [ '📸 Instagram',          'https://instagram.com/yourpage' ],
			'dfseo_social_linkedin'  => [ '💼 LinkedIn',           'https://linkedin.com/company/...' ],
			'dfseo_social_youtube'   => [ '▶️ YouTube',            'https://youtube.com/c/...' ],
			'dfseo_social_tiktok'    => [ '🎵 TikTok',             'https://tiktok.com/@yourhandle' ],
			'dfseo_social_pinterest' => [ '📌 Pinterest',          'https://pinterest.com/yourpage' ],
			'dfseo_social_threads'   => [ '🧵 Threads',            'https://threads.net/@yourhandle' ],
			'dfseo_social_whatsapp'  => [ '💬 WhatsApp Business',  'https://wa.me/27xxxxxxxxxx' ],
			'dfseo_social_mastodon'  => [ '🐘 Mastodon',           'https://mastodon.social/@yourhandle' ],
			'dfseo_social_github'    => [ '🐙 GitHub',             'https://github.com/yourorg' ],
			'dfseo_social_telegram'  => [ '✈️ Telegram',           'https://t.me/yourchannel' ],
		];
		foreach ( $socials as $key => [ $label, $placeholder ] ) : ?>
		<tr>
			<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="url" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
				value="<?php echo esc_attr( get_option( $key, '' ) ); ?>"
				class="dfseo-input" placeholder="<?php echo esc_attr( $placeholder ); ?>"></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<th><label for="dfseo_social_fb_app_id"><?php esc_html_e( 'Facebook App ID', 'dadsfam-seo' ); ?></label></th>
			<td><input type="text" name="dfseo_social_fb_app_id" id="dfseo_social_fb_app_id"
				value="<?php echo esc_attr( get_option( 'dfseo_social_fb_app_id', '' ) ); ?>"
				class="dfseo-input dfseo-input-sm" placeholder="123456789"></td>
		</tr>
		<tr>
			<th>
				<label for="dfseo_social_custom"><?php esc_html_e( 'Additional Profiles', 'dadsfam-seo' ); ?></label>
			</th>
			<td>
				<textarea name="dfseo_social_custom" id="dfseo_social_custom" rows="4" class="dfseo-textarea"
					placeholder="https://soundcloud.com/yourpage&#10;https://open.spotify.com/artist/...&#10;https://twitch.tv/yourhandle"><?php echo esc_textarea( get_option( 'dfseo_social_custom', '' ) ); ?></textarea>
				<p class="dfseo-hint"><?php esc_html_e( 'Any other profile URLs not listed above — one per line. All will be added to your Organisation schema as sameAs links.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Twitter / X Card Settings', 'dadsfam-seo' ); ?></h2>
	<table class="dfseo-settings-table">
		<tr>
			<th><label for="dfseo_twitter_site"><?php esc_html_e( 'Twitter @username', 'dadsfam-seo' ); ?></label></th>
			<td><input type="text" name="dfseo_twitter_site" id="dfseo_twitter_site"
				value="<?php echo esc_attr( get_option( 'dfseo_twitter_site', '' ) ); ?>"
				class="dfseo-input dfseo-input-sm" placeholder="@yourhandle"></td>
		</tr>
		<tr>
			<th><label for="dfseo_twitter_card_type"><?php esc_html_e( 'Card Type', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_twitter_card_type" id="dfseo_twitter_card_type" class="dfseo-select">
					<option value="summary_large_image" <?php selected( get_option( 'dfseo_twitter_card_type' ), 'summary_large_image' ); ?>><?php esc_html_e( 'Summary with Large Image (recommended)', 'dadsfam-seo' ); ?></option>
					<option value="summary" <?php selected( get_option( 'dfseo_twitter_card_type' ), 'summary' ); ?>><?php esc_html_e( 'Summary', 'dadsfam-seo' ); ?></option>
				</select>
			</td>
		</tr>
	</table>
</div>
