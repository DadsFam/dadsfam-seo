<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dfseo-settings-section">
	<h2><?php esc_html_e( 'Indexing Controls', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( '"Noindex" tells Google to exclude a page from search results. Archive pages are often thin or duplicate content — noindexing them focuses Google\'s attention on your important posts and pages instead.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<?php
		$noindex_opts = [
			'dfseo_noindex_archives' => [ __( 'Noindex date archive pages', 'dadsfam-seo' ),   __( 'Pages like /2024/03/ — these are rarely useful in search results. Recommended: on.', 'dadsfam-seo' ) ],
			'dfseo_noindex_author'   => [ __( 'Noindex author archive pages', 'dadsfam-seo' ),  __( 'Pages like /author/admin/ — hide these if you have only one author (they duplicate your homepage). Recommended: on for single-author sites.', 'dadsfam-seo' ) ],
			'dfseo_noindex_tags'     => [ __( 'Noindex tag archive pages', 'dadsfam-seo' ),     __( 'Tag archives are often very similar to category archives. If your tags are loose or numerous, noindex them to avoid diluting your crawl budget.', 'dadsfam-seo' ) ],
			'dfseo_noindex_paged'    => [ __( 'Noindex paginated pages', 'dadsfam-seo' ),       __( 'Pages 2, 3, 4… of archives. Recommended: on — the canonical first page handles the SEO value.', 'dadsfam-seo' ) ],
		];
		foreach ( $noindex_opts as $key => [$label, $tip] ) : ?>
		<tr>
			<th><?php echo esc_html( $label ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( get_option( $key ), '1' ); ?>><?php esc_html_e( 'Enable', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php echo esc_html( $tip ); ?></p>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>

	<h2><?php esc_html_e( 'Breadcrumbs', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'Breadcrumbs show visitors where they are on your site (e.g. Home › Blog › Post Title) and help Google understand your site structure. Google also shows them in search results instead of the plain URL.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<tr>
			<th><?php esc_html_e( 'Enable Breadcrumbs', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_breadcrumbs_enable" value="1" <?php checked( get_option( 'dfseo_breadcrumbs_enable', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Enable breadcrumbs shortcode', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Once enabled, place the shortcode [dfseo_breadcrumbs] anywhere in your theme or page templates to output breadcrumbs. Schema markup is included automatically.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_breadcrumbs_separator"><?php esc_html_e( 'Separator Character', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="text" name="dfseo_breadcrumbs_separator" id="dfseo_breadcrumbs_separator" value="<?php echo esc_attr( get_option( 'dfseo_breadcrumbs_separator', '›' ) ); ?>" class="dfseo-input dfseo-input-sm">
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Character shown between crumbs. The › arrow (›) and » double-arrow are popular choices. You can use any character or emoji.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_breadcrumbs_home_label"><?php esc_html_e( 'Home Label', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="text" name="dfseo_breadcrumbs_home_label" id="dfseo_breadcrumbs_home_label" value="<?php echo esc_attr( get_option( 'dfseo_breadcrumbs_home_label', 'Home' ) ); ?>" class="dfseo-input dfseo-input-sm">
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Text for the first crumb that links to your homepage. "Home" is standard. You could use your site name or a 🏠 emoji if your design supports it.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Redirects & 404 Tracking', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'Broken links (404 errors) hurt your SEO because Google wastes crawl budget on pages that don\'t exist, and visitors hit a dead end. These settings help you catch and fix them.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<tr>
			<th><?php esc_html_e( 'Enable Redirect Processing', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_redirect_enable" value="1" <?php checked( get_option( 'dfseo_redirect_enable', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Process redirect rules on 404 pages', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'When on, any redirect rules you create under the Redirects tab are applied automatically. A visitor hitting a removed page gets sent to the right place instead of a dead end. Recommended: on.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Log 404 Errors', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_track_404" value="1" <?php checked( get_option( 'dfseo_track_404', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Record every 404 error to the database', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Keeps a log of all broken URLs visitors and Google hit. View them under Redirects → 404 Log (Premium) and create redirects with one click. Recommended: on.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Auto-Redirect on URL Change', 'dadsfam-seo' ); ?> <span class="dfseo-badge-pro" style="background:#fef3c7;color:#d97706;padding:1px 8px;border-radius:99px;font-size:10px;font-weight:700">⭐ PREMIUM</span></th>
			<td>
				<label class="dfseo-check-label"><input type="checkbox" name="dfseo_auto_redirect_slug" value="1" <?php checked( get_option( 'dfseo_auto_redirect_slug', '1' ), '1' ); ?>>
				<?php esc_html_e( 'Automatically create a 301 redirect when a published post or page URL changes', 'dadsfam-seo' ); ?></label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'If you edit the slug of a live post, the old URL would normally 404 and lose its Google ranking. With this on, DadsFam SEO instantly creates a 301 redirect from the old URL to the new one — so visitors and search engines never hit a dead link, and your ranking transfers. This is the same feature Yoast and Rank Math charge premium for. Recommended: on.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Custom robots.txt', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'robots.txt is a plain-text file that tells search engine crawlers which pages they are and aren\'t allowed to crawl. DadsFam SEO manages this file for you automatically — only edit the field below if you have specific rules to add. Leave empty for sensible defaults.', 'dadsfam-seo' ); ?></p>
	<textarea name="dfseo_robots_txt_custom" rows="10" class="dfseo-textarea dfseo-textarea-code"><?php echo esc_textarea( get_option( 'dfseo_robots_txt_custom', '' ) ); ?></textarea>
	<p><a href="<?php echo esc_url( home_url( '/robots.txt' ) ); ?>" target="_blank"><?php esc_html_e( 'View your current robots.txt →', 'dadsfam-seo' ); ?></a></p>
</div>
