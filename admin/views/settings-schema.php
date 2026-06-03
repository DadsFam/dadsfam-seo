<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dfseo-settings-section">
	<h2><?php esc_html_e( 'Organisation / Website Schema', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'Schema markup is invisible code that tells Google exactly what your site is about. It powers rich results like your business name in the Knowledge Panel, star ratings, and FAQ dropdowns directly in search results.', 'dadsfam-seo' ); ?></p>
	<table class="dfseo-settings-table">
		<tr>
			<th><label for="dfseo_schema_org_type"><?php esc_html_e( 'Site Type', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_schema_org_type" id="dfseo_schema_org_type" class="dfseo-select">
					<?php foreach ( [ 'Organization' => __( '🏢 Organisation (default)', 'dadsfam-seo' ), 'Person' => __( '👤 Person / Personal Blog', 'dadsfam-seo' ), 'LocalBusiness' => __( '📍 Local Business', 'dadsfam-seo' ), 'Corporation' => __( '🏗️ Corporation', 'dadsfam-seo' ), 'NewsMediaOrganization' => __( '📰 News Media', 'dadsfam-seo' ) ] as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( get_option( 'dfseo_schema_org_type', 'Organization' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Choose what best describes your site. "Organisation" works for most businesses. Use "Person" for a personal blog or portfolio. This affects how Google categorises you in its Knowledge Graph.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_schema_org_name"><?php esc_html_e( 'Organisation Name', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="text" name="dfseo_schema_org_name" id="dfseo_schema_org_name" value="<?php echo esc_attr( get_option( 'dfseo_schema_org_name', get_bloginfo( 'name' ) ) ); ?>" class="dfseo-input">
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Your official business or brand name, exactly as you want it to appear in Google. Keep it consistent with how you appear on social media and Google Business Profile.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_schema_org_logo"><?php esc_html_e( 'Logo URL', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="url" name="dfseo_schema_org_logo" id="dfseo_schema_org_logo" value="<?php echo esc_attr( get_option( 'dfseo_schema_org_logo' ) ); ?>" class="dfseo-input" placeholder="https://...">
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Your logo is included in Article schema and can appear in Google\'s Knowledge Panel. Google recommends: PNG or WebP, at least 112×112 pixels, square preferred. Upload to your Media Library and paste the URL here.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Sitelinks Search Box', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label">
					<input type="checkbox" name="dfseo_schema_search_box" value="1" <?php checked( get_option( 'dfseo_schema_search_box', '1' ), '1' ); ?>>
					<?php esc_html_e( 'Enable SearchAction schema on the homepage', 'dadsfam-seo' ); ?>
				</label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'When enabled, Google may show a search box directly in the search results for your site — letting people search your content without even visiting your homepage first. Recommended: on.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>
</div>
