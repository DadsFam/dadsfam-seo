<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dfseo-settings-section">
	<h2><?php esc_html_e( 'General SEO Settings', 'dadsfam-seo' ); ?></h2>

	<table class="dfseo-settings-table">
		<tr>
			<th><label for="dfseo_separator"><?php esc_html_e( 'Title Separator', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_separator" id="dfseo_separator" class="dfseo-select dfseo-select-sm">
					<?php foreach ( [ '–', '-', '|', '•', '›', '«', '»', '~', '/', '⋅' ] as $sep ) : ?>
						<option value="<?php echo esc_attr( $sep ); ?>" <?php selected( get_option( 'dfseo_separator' ), $sep ); ?>><?php echo esc_html( $sep ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'This character sits between your page title and site name in search results — e.g. "About Us – DadsFam". The en-dash (–) is the most commonly used.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_title_template"><?php esc_html_e( 'Default Title Template', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="text" name="dfseo_title_template" id="dfseo_title_template" value="<?php echo esc_attr( get_option( 'dfseo_title_template', '%title% %sep% %sitename%' ) ); ?>" class="dfseo-input">
				<p class="dfseo-hint">💡 <?php esc_html_e( 'The default pattern for all page titles. Available variables:', 'dadsfam-seo' ); ?> <code>%title%</code> = <?php esc_html_e('page title', 'dadsfam-seo'); ?>, <code>%sitename%</code> = <?php esc_html_e('your site name', 'dadsfam-seo'); ?>, <code>%sep%</code> = <?php esc_html_e('separator above', 'dadsfam-seo'); ?>, <code>%tagline%</code>, <code>%category%</code>, <code>%year%</code>. <?php esc_html_e('Individual posts can override this from the SEO meta box.', 'dadsfam-seo'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_home_title"><?php esc_html_e( 'Homepage Title', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="text" name="dfseo_home_title" id="dfseo_home_title" value="<?php echo esc_attr( get_option( 'dfseo_home_title' ) ); ?>" class="dfseo-input">
				<p class="dfseo-hint">💡 <?php esc_html_e( "The title shown in Google for your homepage. If left empty, the template above is used. Aim for 50–60 characters and include your primary keyword.", 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_home_description"><?php esc_html_e( 'Homepage Meta Description', 'dadsfam-seo' ); ?></label></th>
			<td>
				<textarea name="dfseo_home_description" id="dfseo_home_description" rows="3" class="dfseo-textarea"><?php echo esc_textarea( get_option( 'dfseo_home_description' ) ); ?></textarea>
				<p class="dfseo-hint">💡 <?php esc_html_e( "This appears under your site title in Google search results. Think of it as a mini-advert — write something compelling that makes people want to click. Aim for 120–160 characters.", 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_og_default_image"><?php esc_html_e( 'Default Social Share Image', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="url" name="dfseo_og_default_image" id="dfseo_og_default_image" value="<?php echo esc_attr( get_option( 'dfseo_og_default_image' ) ); ?>" class="dfseo-input" placeholder="https://...">
				<p class="dfseo-hint">💡 <?php esc_html_e( "Used when a post doesn't have a featured image. This is the image Facebook, WhatsApp, and LinkedIn show when someone shares your page. Recommended size: 1200×630 pixels.", 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>

	<h3><?php esc_html_e( 'Site Verification', 'dadsfam-seo' ); ?></h3>
	<p class="dfseo-hint">💡 <?php esc_html_e( "These codes prove to search engines that you own this site and give you access to their free webmaster tools — where you can see crawl errors, submitted sitemaps, and which keywords bring visitors.", 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<?php
		$verify = [
			'dfseo_google_verify'    => [ __( '🟢 Google Search Console', 'dadsfam-seo' ), 'google488b50570eb5568d', __( 'In Google Search Console → Settings → Ownership verification → HTML tag, you\'ll see a meta tag like: <meta name="google-site-verification" content="google488b50570eb5568d" />. Paste ONLY the value inside content="..." — e.g. google488b50570eb5568d — nothing else, no quotes, no extra text.', 'dadsfam-seo' ) ],
			'dfseo_bing_verify'      => [ __( '🔵 Bing Webmaster Tools',  'dadsfam-seo' ), 'msvalidate.01 content value', __( 'Covers 30%+ of desktop searches including Microsoft Edge and Copilot AI results.', 'dadsfam-seo' ) ],
			'dfseo_yandex_verify'    => [ __( '🟠 Yandex Webmaster',      'dadsfam-seo' ), 'yandex verification code',    __( 'Important if you have visitors from Russia or Eastern Europe.', 'dadsfam-seo' ) ],
			'dfseo_pinterest_verify' => [ __( '📌 Pinterest',             'dadsfam-seo' ), 'p:domain_verify content value', __( 'Verifies your website in Pinterest business settings. Enables rich pins for your content.', 'dadsfam-seo' ) ],
			'dfseo_norton_verify'    => [ __( '🛡️ Norton Safe Web',        'dadsfam-seo' ), 'norton-safeweb-site-verification', __( 'Marks your site as safe in Norton security products.', 'dadsfam-seo' ) ],
		];
		foreach ( $verify as $key => [$label, $placeholder, $tip] ) :
		?>
		<tr>
			<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_option( $key ) ); ?>" class="dfseo-input" placeholder="<?php echo esc_attr( $placeholder ); ?>">
				<p class="dfseo-hint"><?php echo esc_html( $tip ); ?></p>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
