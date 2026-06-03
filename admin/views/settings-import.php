<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( ! dfseo_is_premium() ) { DFSEO_Core::premium_overlay( 'Data Import' ); return; } ?>
<div class="dfseo-settings-section">
	<h2><?php esc_html_e( 'Import from Other SEO Plugins', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint"><?php esc_html_e( 'Import your existing SEO data in batches of 50 posts. Your DadsFam SEO data will NOT be overwritten where it already exists. Do not navigate away during import.', 'dadsfam-seo' ); ?></p>

	<div class="dfseo-import-panels" id="dfseo-import-panels">
		<?php
		$importers = [
			'yoast'            => [ 'Yoast SEO',               'wordpress-seo/wp-seo.php',                   '🟢' ],
			'rankmath'         => [ 'Rank Math SEO',           'seo-by-rank-math/rank-math.php',             '🟠' ],
			'aioseo'           => [ 'All in One SEO',          'all-in-one-seo-pack/all_in_one_seo_pack.php','🔵' ],
			'seopress'         => [ 'SEOPress',                'wp-seopress/seopress.php',                   '🟣' ],
			'theseoframework'  => [ 'The SEO Framework',       'autodescription/autodescription.php',        '⚫' ],
			'slimsseo'         => [ 'Slim SEO',                'slim-seo/slim-seo.php',                      '🟤' ],
			'siteseo'          => [ 'SiteSEO (Softaculous)',   'site-seo/site-seo.php',                      '🔴' ],
		];
		$active_plugins = (array) get_option( 'active_plugins', [] );
		foreach ( $importers as $source => [ $name, $plugin, $icon ] ) :
			$is_active = in_array( $plugin, $active_plugins, true );
		?>
		<div class="dfseo-import-card <?php echo $is_active ? 'active' : 'inactive'; ?>">
			<h3><?php echo esc_html( $icon . ' ' . $name ); ?></h3>
			<?php if ( $is_active ) : ?>
				<p class="dfseo-ok">✓ <?php esc_html_e( 'Plugin detected', 'dadsfam-seo' ); ?></p>
				<button type="button" class="dfseo-btn dfseo-btn-primary dfseo-import-start" data-source="<?php echo esc_attr( $source ); ?>">
					<?php printf( esc_html__( 'Import from %s', 'dadsfam-seo' ), esc_html( $name ) ); ?>
				</button>
			<?php else : ?>
				<p class="dfseo-muted"><?php esc_html_e( 'Plugin not active on this site.', 'dadsfam-seo' ); ?></p>
			<?php endif; ?>
			<div class="dfseo-import-progress" id="dfseo-import-<?php echo esc_attr( $source ); ?>" style="display:none">
				<div class="dfseo-progress-bar"><div class="dfseo-progress-fill" id="dfseo-import-<?php echo esc_attr( $source ); ?>-bar" style="width:0%"></div></div>
				<span id="dfseo-import-<?php echo esc_attr( $source ); ?>-status"><?php esc_html_e( 'Starting…', 'dadsfam-seo' ); ?></span>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<div class="dfseo-import-note">
		<strong><?php esc_html_e( 'Note on SiteSEO:', 'dadsfam-seo' ); ?></strong>
		<?php esc_html_e( 'SiteSEO stores data in _siteseo_titles_title, _siteseo_titles_desc, and related meta keys. If your import shows 0 results, verify these keys exist using your database admin.', 'dadsfam-seo' ); ?>
	</div>
</div>
<style>
.dfseo-import-note {
	background: var(--df-amber-pale);
	border-left: 4px solid var(--df-amber);
	padding: 12px 16px;
	border-radius: var(--df-radius-sm);
	font-size: 12px;
	color: var(--df-gray-700);
	margin-top: 16px;
}
</style>
