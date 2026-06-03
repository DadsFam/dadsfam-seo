<?php // Bulk SEO Editor view
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
$post_types = get_post_types( [ 'public' => true ], 'objects' );
?>
<?php $dfseo_page = 'bulk-edit'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">

	<div class="dfseo-box">
		<div class="dfseo-bulk-filters">
			<select id="dfseo-bulk-pt" class="dfseo-select dfseo-select-sm">
				<?php foreach ( $post_types as $pt ) : ?>
					<?php if ( $pt->name === 'attachment' ) continue; ?>
					<option value="<?php echo esc_attr( $pt->name ); ?>"><?php echo esc_html( $pt->label ); ?></option>
				<?php endforeach; ?>
			</select>
			<select id="dfseo-bulk-filter" class="dfseo-select dfseo-select-sm">
				<option value=""><?php esc_html_e( 'All posts', 'dadsfam-seo' ); ?></option>
				<option value="no_kw"><?php esc_html_e( 'No focus keyword', 'dadsfam-seo' ); ?></option>
				<option value="no_meta"><?php esc_html_e( 'No meta description', 'dadsfam-seo' ); ?></option>
				<option value="poor_score"><?php esc_html_e( 'Poor SEO score (<50)', 'dadsfam-seo' ); ?></option>
			</select>
			<input type="search" id="dfseo-bulk-search" class="dfseo-input dfseo-input-sm" placeholder="<?php esc_attr_e( 'Search posts…', 'dadsfam-seo' ); ?>">
			<button type="button" id="dfseo-bulk-load" class="dfseo-btn dfseo-btn-secondary"><?php esc_html_e( 'Load', 'dadsfam-seo' ); ?></button>
			<button type="button" id="dfseo-bulk-save-all" class="dfseo-btn dfseo-btn-primary" style="margin-left:auto"><?php esc_html_e( 'Save All Changes', 'dadsfam-seo' ); ?></button>
		</div>

		<div id="dfseo-bulk-loading" style="display:none;padding:20px;text-align:center">
			<span class="dfseo-spinner"></span> <?php esc_html_e( 'Loading posts…', 'dadsfam-seo' ); ?>
		</div>

		<table class="dfseo-table dfseo-bulk-table" id="dfseo-bulk-table">
			<thead>
				<tr>
					<th style="width:20%"><?php esc_html_e( 'Post Title', 'dadsfam-seo' ); ?></th>
					<th style="width:15%"><?php esc_html_e( 'Focus Keyword', 'dadsfam-seo' ); ?></th>
					<th style="width:25%"><?php esc_html_e( 'SEO Title', 'dadsfam-seo' ); ?></th>
					<th style="width:30%"><?php esc_html_e( 'Meta Description', 'dadsfam-seo' ); ?></th>
					<th style="width:10%"><?php esc_html_e( 'Score', 'dadsfam-seo' ); ?></th>
				</tr>
			</thead>
			<tbody id="dfseo-bulk-tbody">
				<tr><td colspan="5" class="dfseo-muted" style="text-align:center;padding:20px"><?php esc_html_e( 'Click "Load" to fetch posts.', 'dadsfam-seo' ); ?></td></tr>
			</tbody>
		</table>

		<div class="dfseo-bulk-pagination" id="dfseo-bulk-pagination"></div>
	</div>
</div>
