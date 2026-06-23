<?php
/**
 * Plugin header partial — shared across all DadsFam SEO admin pages.
 *
 * ALL internal variables are prefixed $_h_ to avoid colliding with
 * variables already set by the calling page (e.g. $key, $is_active).
 *
 * Usage: set $dfseo_page = 'dashboard' then include this file.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$_h_page      = isset( $dfseo_page ) ? $dfseo_page : sanitize_key( str_replace( 'dfseo-', '', $_GET['page'] ?? 'dashboard' ) );
$_h_premium   = dfseo_is_premium();
$_h_admin_url = admin_url( 'admin.php' );
$_h_host      = (string) parse_url( home_url(), PHP_URL_HOST );

$_h_nav = [
	'dashboard' => [ 'label' => '🏠 Dashboard',    'page' => 'dfseo-dashboard',  'pro' => false ],
	'settings'  => [ 'label' => '⚙️ Settings',      'page' => 'dfseo-settings',   'pro' => false ],
	'redirects' => [ 'label' => '🔀 Redirects',     'page' => 'dfseo-redirects',  'pro' => true  ],
	'analytics' => [ 'label' => '📈 Analytics',     'page' => 'dfseo-analytics',  'pro' => true  ],
	'bulk-edit' => [ 'label' => '✏️ Bulk Editor',   'page' => 'dfseo-bulk-edit',  'pro' => true  ],
	'license'   => [ 'label' => '🔑 License',       'page' => 'dfseo-license',    'pro' => false ],
	'changelog' => [ 'label' => '📋 Changelog',     'page' => 'dfseo-changelog',  'pro' => false ],
];
?>
<div class="dfseo-header">
	<div class="dfseo-header-top">
		<div class="dfseo-header-identity">
			<span class="dfseo-header-icon">🏆</span>
			<div>
				<span class="dfseo-header-title">DadsFam SEO</span>
				<span class="dfseo-header-sep">/</span>
				<span class="dfseo-header-page-label">
					<?php echo esc_html( ucwords( str_replace( '-', ' ', $_h_page ) ) ); ?>
				</span>
				<div class="dfseo-header-url"><?php echo esc_html( $_h_host ); ?></div>
			</div>
		</div>
		<div class="dfseo-header-badges">
			<button type="button" class="dfseo-theme-toggle" id="dfseo-theme-toggle" title="<?php esc_attr_e( 'Switch theme (auto / light / dark)', 'dadsfam-seo' ); ?>" aria-label="<?php esc_attr_e( 'Switch colour theme', 'dadsfam-seo' ); ?>">
				<span class="dfseo-theme-ico dfseo-theme-ico-auto">🌗</span>
				<span class="dfseo-theme-ico dfseo-theme-ico-light">☀️</span>
				<span class="dfseo-theme-ico dfseo-theme-ico-dark">🌙</span>
			</button>
			<?php if ( $_h_premium ) : ?>
				<span class="dfseo-chip dfseo-chip-pro">⭐ PRO</span>
			<?php else : ?>
				<a href="<?php echo esc_url( add_query_arg( 'page', 'dfseo-license', $_h_admin_url ) ); ?>" class="dfseo-chip dfseo-chip-free">
					Free — Upgrade ↗
				</a>
			<?php endif; ?>
			<span class="dfseo-chip dfseo-chip-version">v<?php echo esc_html( DFSEO_VERSION ); ?></span>
		</div>
	</div>

	<nav class="dfseo-header-nav" role="navigation">
		<?php foreach ( $_h_nav as $_h_slug => $_h_item ) :
			$_h_tab_active  = ( $_h_page === $_h_slug );
			$_h_tab_locked  = ( $_h_item['pro'] && ! $_h_premium );
			$_h_tab_href    = esc_url( add_query_arg( 'page', $_h_item['page'], $_h_admin_url ) );
			$_h_tab_classes = 'dfseo-header-tab'
				. ( $_h_tab_active ? ' active' : '' )
				. ( $_h_tab_locked ? ' locked' : '' );
		?>
			<a href="<?php echo $_h_tab_href; ?>" class="<?php echo esc_attr( $_h_tab_classes ); ?>">
				<?php echo esc_html( $_h_item['label'] ); ?>
				<?php if ( $_h_tab_locked ) : ?><span class="dfseo-tab-lock">🔒</span><?php endif; ?>
			</a>
		<?php endforeach; ?>
	</nav>
</div>
<?php
// Clean up — unset all internal header variables so they can't leak into the calling page
unset( $_h_page, $_h_premium, $_h_admin_url, $_h_host, $_h_nav,
       $_h_slug, $_h_item, $_h_tab_active, $_h_tab_locked, $_h_tab_href, $_h_tab_classes );
