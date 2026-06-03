<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dfseo-settings-section">
	<h2><?php esc_html_e( 'XML Sitemap', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'Your sitemap is a map of all your pages that you hand directly to Google and Bing. It\'s already live — the settings below let you control exactly what\'s included.', 'dadsfam-seo' ); ?></p>
	<p>
		<?php printf( esc_html__( 'Your sitemap: %s', 'dadsfam-seo' ), '<a href="' . esc_url( home_url('/sitemap.xml') ) . '" target="_blank"><strong>' . esc_url( home_url('/sitemap.xml') ) . '</strong></a>' ); ?>
		&nbsp;—&nbsp;
		<a href="https://search.google.com/search-console/sitemaps" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Submit to Google →', 'dadsfam-seo' ); ?></a>
	</p>
	<table class="dfseo-settings-table">
		<tr>
			<th><?php esc_html_e( 'Post Types to Include', 'dadsfam-seo' ); ?></th>
			<td>
				<?php
				$enabled = (array) get_option( 'dfseo_sitemap_post_types', [ 'post', 'page' ] );
				foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $pt ) :
					if ( $pt->name === 'attachment' ) continue;
				?>
				<label class="dfseo-check-label">
					<input type="checkbox" name="dfseo_sitemap_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $enabled, true ) ); ?>>
					<?php echo esc_html( $pt->label ); ?>
				</label>
				<?php endforeach; ?>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Only tick content types that are meaningful for visitors — skip internal post types or anything that shouldn\'t appear in search results.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Taxonomies to Include', 'dadsfam-seo' ); ?></th>
			<td>
				<?php
				$enabled_tax = (array) get_option( 'dfseo_sitemap_taxonomies', [ 'category', 'post_tag' ] );
				foreach ( get_taxonomies( [ 'public' => true ], 'objects' ) as $tax ) :
				?>
				<label class="dfseo-check-label">
					<input type="checkbox" name="dfseo_sitemap_taxonomies[]" value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( in_array( $tax->name, $enabled_tax, true ) ); ?>>
					<?php echo esc_html( $tax->label ); ?>
				</label>
				<?php endforeach; ?>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Category and tag archive pages can appear in Google. Only include taxonomies that have meaningful archive pages with real content. Disable any that are thin or auto-generated.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="dfseo_sitemap_exclude_ids"><?php esc_html_e( 'Exclude Specific Posts', 'dadsfam-seo' ); ?></label></th>
			<td>
				<input type="text" name="dfseo_sitemap_exclude_ids" id="dfseo_sitemap_exclude_ids"
					value="<?php echo esc_attr( implode( ', ', (array) get_option( 'dfseo_sitemap_exclude_ids', [] ) ) ); ?>"
					class="dfseo-input" placeholder="<?php esc_attr_e( 'e.g. 5, 12, 89', 'dadsfam-seo' ); ?>">
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Comma-separated post/page IDs to exclude. Find the ID by hovering over the post title in the WordPress post list — it shows in the URL at the bottom of the browser. Useful for "Thank You" pages, test pages, or private landing pages.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Auto-Ping Search Engines', 'dadsfam-seo' ); ?></th>
			<td>
				<label class="dfseo-check-label">
					<input type="checkbox" name="dfseo_ping_engines" value="1" <?php checked( get_option( 'dfseo_ping_engines', '1' ), '1' ); ?>>
					<?php esc_html_e( 'Notify Google and Bing whenever you publish a new post', 'dadsfam-seo' ); ?>
				</label>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'When you publish, DadsFam SEO immediately tells Google and Bing your sitemap has been updated — so they crawl your new content faster instead of waiting until their next scheduled visit. Recommended: on.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
	</table>
</div>
