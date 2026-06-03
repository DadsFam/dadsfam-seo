<?php // Redirects manager view
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'dadsfam-seo' ) );
?>
<?php $dfseo_page = 'redirects'; include DFSEO_PATH . 'admin/views/partials/header.php'; ?>
<div class="wrap dfseo-wrap dfseo-wrap--with-header">

	<!-- Add/Edit form (hidden by default) -->
	<div class="dfseo-box" id="dfseo-redirect-form" style="display:none">
		<h3 id="dfseo-redirect-form-title"><?php esc_html_e( 'Add Redirect', 'dadsfam-seo' ); ?></h3>
		<div class="dfseo-form-row">
			<div class="dfseo-field-group">
				<label><?php esc_html_e( 'From (Source URL or pattern)', 'dadsfam-seo' ); ?></label>
				<input type="text" id="rd-source" class="dfseo-input" placeholder="/old-page/">
			</div>
			<div class="dfseo-field-group">
				<label><?php esc_html_e( 'To (Target URL)', 'dadsfam-seo' ); ?></label>
				<input type="text" id="rd-target" class="dfseo-input" placeholder="/new-page/">
			</div>
			<div class="dfseo-field-group dfseo-field-sm">
				<label><?php esc_html_e( 'Type', 'dadsfam-seo' ); ?></label>
				<select id="rd-type" class="dfseo-select">
					<option value="301">301 Permanent</option>
					<option value="302">302 Temporary</option>
					<option value="307">307 Temporary (keep method)</option>
					<option value="410">410 Gone</option>
				</select>
			</div>
			<div class="dfseo-field-group">
				<label><?php esc_html_e( 'Note (optional)', 'dadsfam-seo' ); ?></label>
				<input type="text" id="rd-note" class="dfseo-input" placeholder="<?php esc_attr_e( 'Why this redirect exists', 'dadsfam-seo' ); ?>">
			</div>
		</div>
		<input type="hidden" id="rd-id" value="">
		<div style="margin-top:12px">
			<button type="button" id="dfseo-redirect-save" class="dfseo-btn dfseo-btn-primary"><?php esc_html_e( 'Save Redirect', 'dadsfam-seo' ); ?></button>
			<button type="button" id="dfseo-redirect-cancel" class="dfseo-btn dfseo-btn-ghost"><?php esc_html_e( 'Cancel', 'dadsfam-seo' ); ?></button>
		</div>
	</div>

	<div class="dfseo-box">
		<!-- Tabs: Redirects | 404 Log -->
		<div class="dfseo-sub-tabs" style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
		<button type="button" id="dfseo-redirect-add-btn" class="dfseo-btn dfseo-btn-primary" style="margin-left:auto">➕ <?php esc_html_e('Add Redirect', 'dadsfam-seo'); ?></button>
			<button class="dfseo-sub-tab active" data-subtab="redirects"><?php esc_html_e( 'Redirects', 'dadsfam-seo' ); ?></button>
			<button class="dfseo-sub-tab" data-subtab="404"><?php esc_html_e( '404 Log', 'dadsfam-seo' ); ?></button>
		</div>

		<div id="dfseo-redirects-list">
			<div class="dfseo-loading-overlay" style="display:none"><span class="dfseo-spinner"></span></div>
			<table class="dfseo-table" id="dfseo-redirects-table">
				<thead><tr>
					<th><?php esc_html_e( 'Source', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Target', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Type', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Hits', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'dadsfam-seo' ); ?></th>
				</tr></thead>
				<tbody id="dfseo-redirects-body"></tbody>
			</table>
		</div>

		<div id="dfseo-404-list" style="display:none">
			<table class="dfseo-table" id="dfseo-404-table">
				<thead><tr>
					<th><?php esc_html_e( 'URL', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Hits', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Last Seen', 'dadsfam-seo' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'dadsfam-seo' ); ?></th>
				</tr></thead>
				<tbody id="dfseo-404-body"></tbody>
			</table>
		</div>
	</div>
</div>
