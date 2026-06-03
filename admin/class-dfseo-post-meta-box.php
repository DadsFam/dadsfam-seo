<?php
/**
 * Post meta box — SEO panel below the editor.
 * Tabs: General | Readability | Social | Advanced | Schema (premium)
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Post_Meta_Box {

	// Post types to show the meta box on
	private array $supported_post_types = [];

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post',      [ $this, 'save_meta_box' ], 10, 2 );
	}

	// ─── Registration ───────────────────────────────────────────────────────

	public function register_meta_boxes(): void {
		$post_types = array_filter(
			get_post_types( [ 'public' => true ] ),
			static fn( $pt ) => $pt !== 'attachment'
		);
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'dfseo_meta_box',
				'<img src="' . esc_url( DFSEO_URL . 'assets/images/icon-16.png' ) . '" width="16" style="vertical-align:-3px;margin-right:6px" alt="">' . esc_html__( 'DadsFam SEO', 'dadsfam-seo' ),
				[ $this, 'render_meta_box' ],
				$pt,
				'normal',
				'high'
			);
		}
	}

	// ─── Render ─────────────────────────────────────────────────────────────

	public function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'dfseo_meta_box_save', 'dfseo_meta_box_nonce' );

		$focus_kw  = (string) get_post_meta( $post->ID, '_dfseo_focus_keyword', true );
		$seo_title = (string) get_post_meta( $post->ID, '_dfseo_title',         true );
		$meta_desc = (string) get_post_meta( $post->ID, '_dfseo_meta_desc',     true );
		$canonical = (string) get_post_meta( $post->ID, '_dfseo_canonical',     true );
		$noindex   = (string) get_post_meta( $post->ID, '_dfseo_noindex',       true );
		$nofollow  = (string) get_post_meta( $post->ID, '_dfseo_nofollow',      true );
		$noarchive = (string) get_post_meta( $post->ID, '_dfseo_noarchive',     true );
		$nosnippet = (string) get_post_meta( $post->ID, '_dfseo_nosnippet',     true );
		$og_img_id = (int)    get_post_meta( $post->ID, '_dfseo_og_image_id',   true );
		$score     = (int)    get_post_meta( $post->ID, '_dfseo_score',         true );
		$schema_type = (string) get_post_meta( $post->ID, '_dfseo_schema_type', true ) ?: 'WebPage';

		$og_img_url = $og_img_id ? wp_get_attachment_image_url( $og_img_id, 'thumbnail' ) : '';
		$post_title = $post->post_title;
		$permalink  = get_permalink( $post->ID );
		$sitename   = get_bloginfo( 'name' );
		$separator  = get_option( 'dfseo_separator', '–' );
		$premium    = dfseo_is_premium();

		// Assemble SERP title preview
		$preview_title = $seo_title ?: ( $post_title . ' ' . $separator . ' ' . $sitename );
		$preview_url   = $permalink;
		$preview_desc  = $meta_desc ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '…' );
		?>
		<div class="dfseo-metabox" id="dfseo-metabox" data-post-id="<?php echo esc_attr( $post->ID ); ?>">

			<!-- Score badge -->
			<div class="dfseo-mb-header">
				<div class="dfseo-mb-score-wrap">
					<div class="dfseo-score-circle <?php echo esc_attr( $this->score_class( $score ) ); ?>" id="dfseo-score-circle">
						<svg viewBox="0 0 36 36" class="dfseo-score-svg">
							<path class="dfseo-score-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
							<path class="dfseo-score-fill" stroke-dasharray="<?php echo esc_attr( $score ); ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
						</svg>
						<span class="dfseo-score-num" id="dfseo-score-num"><?php echo esc_html( $score ); ?></span>
					</div>
					<div class="dfseo-mb-score-labels">
						<strong class="dfseo-score-label" id="dfseo-score-label"><?php echo esc_html( $this->score_label( $score ) ); ?></strong>
						<span><?php esc_html_e( 'SEO Score', 'dadsfam-seo' ); ?></span>
					</div>
				</div>
				<div class="dfseo-mb-actions">
					<button type="button" class="dfseo-btn dfseo-btn-secondary dfseo-analyse-btn" id="dfseo-run-analysis">
						<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Analyse', 'dadsfam-seo' ); ?>
					</button>
				</div>
			</div>

			<!-- SERP Preview -->
			<div class="dfseo-serp-preview" id="dfseo-serp-preview">
				<div class="dfseo-serp-toggle">
					<button type="button" class="dfseo-serp-mode active" data-mode="desktop"><?php esc_html_e( 'Desktop', 'dadsfam-seo' ); ?></button>
					<button type="button" class="dfseo-serp-mode" data-mode="mobile"><?php esc_html_e( 'Mobile', 'dadsfam-seo' ); ?></button>
				</div>
				<div class="dfseo-serp-result desktop" id="dfseo-serp-desktop">
					<div class="dfseo-serp-url" id="dfseo-serp-url-d"><?php echo esc_url( $preview_url ); ?></div>
					<div class="dfseo-serp-title" id="dfseo-serp-title-d"><?php echo esc_html( $preview_title ); ?></div>
					<div class="dfseo-serp-desc" id="dfseo-serp-desc-d"><?php echo esc_html( $preview_desc ); ?></div>
				</div>
				<div class="dfseo-serp-result mobile" id="dfseo-serp-mobile" style="display:none">
					<div class="dfseo-serp-site-m"><?php echo esc_html( $sitename ); ?></div>
					<div class="dfseo-serp-title-m" id="dfseo-serp-title-m"><?php echo esc_html( $preview_title ); ?></div>
					<div class="dfseo-serp-desc-m" id="dfseo-serp-desc-m"><?php echo esc_html( $preview_desc ); ?></div>
				</div>
			</div>

			<!-- Tabs -->
			<div class="dfseo-tabs">
				<nav class="dfseo-tab-nav" role="tablist">
					<button role="tab" aria-selected="true"  class="dfseo-tab active" data-tab="general">
						<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'General', 'dadsfam-seo' ); ?>
					</button>
					<button role="tab" aria-selected="false" class="dfseo-tab" data-tab="readability">
						<span class="dashicons dashicons-editor-paragraph"></span> <?php esc_html_e( 'Readability', 'dadsfam-seo' ); ?>
					</button>
					<button role="tab" aria-selected="false" class="dfseo-tab" data-tab="social">
						<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Social', 'dadsfam-seo' ); ?>
					</button>
					<button role="tab" aria-selected="false" class="dfseo-tab" data-tab="advanced">
						<span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e( 'Advanced', 'dadsfam-seo' ); ?>
					</button>
					<?php if ( $premium ) : ?>
					<button role="tab" aria-selected="false" class="dfseo-tab" data-tab="schema">
						<span class="dashicons dashicons-schema"></span> <?php esc_html_e( 'Schema', 'dadsfam-seo' ); ?>
					</button>
					<button role="tab" aria-selected="false" class="dfseo-tab dfseo-tab-ai" data-tab="ai">
						✨ <?php esc_html_e( 'AI Tools', 'dadsfam-seo' ); ?>
					</button>
					<?php endif; ?>
				</nav>

				<!-- Tab: General -->
				<div class="dfseo-tab-content active" id="dfseo-tab-general">
					<div class="dfseo-field-group">
						<label class="dfseo-label" for="dfseo_focus_keyword">
							<?php esc_html_e( 'Focus Keyword', 'dadsfam-seo' ); ?>
							<span class="dfseo-help" data-tip="<?php esc_attr_e( 'The main keyword you want this page to rank for. Analysis will check how well your content is optimised for this keyword.', 'dadsfam-seo' ); ?>">?</span>
						</label>
						<input type="text" id="dfseo_focus_keyword" name="_dfseo_focus_keyword"
							value="<?php echo esc_attr( $focus_kw ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. best coffee in johannesburg', 'dadsfam-seo' ); ?>"
							class="dfseo-input" autocomplete="off">
					</div>

					<div class="dfseo-field-group">
						<label class="dfseo-label" for="dfseo_title">
							<?php esc_html_e( 'SEO Title', 'dadsfam-seo' ); ?>
							<span class="dfseo-char-counter" id="dfseo-title-counter">
								<span id="dfseo-title-count"><?php echo esc_html( mb_strlen( $seo_title ) ); ?></span>/60
							</span>
						</label>
						<input type="text" id="dfseo_title" name="_dfseo_title"
							value="<?php echo esc_attr( $seo_title ); ?>"
							placeholder="<?php echo esc_attr( $preview_title ); ?>"
							class="dfseo-input" maxlength="90">
						<div class="dfseo-progress-bar">
							<div class="dfseo-progress-fill" id="dfseo-title-progress" style="width:<?php echo esc_attr( min( 100, round( mb_strlen( $seo_title ) / 60 * 100 ) ) ); ?>%"></div>
						</div>
						<p class="dfseo-hint"><?php esc_html_e( 'Ideal: 50–60 characters. Leave empty to use the post title template.', 'dadsfam-seo' ); ?></p>
					</div>

					<div class="dfseo-field-group">
						<label class="dfseo-label" for="dfseo_meta_desc">
							<?php esc_html_e( 'Meta Description', 'dadsfam-seo' ); ?>
							<span class="dfseo-char-counter" id="dfseo-desc-counter">
								<span id="dfseo-desc-count"><?php echo esc_html( mb_strlen( $meta_desc ) ); ?></span>/160
							</span>
						</label>
						<textarea id="dfseo_meta_desc" name="_dfseo_meta_desc" rows="3"
							class="dfseo-textarea" maxlength="320"
							placeholder="<?php esc_attr_e( 'Write a compelling description that includes your focus keyword…', 'dadsfam-seo' ); ?>"><?php echo esc_textarea( $meta_desc ); ?></textarea>
						<div class="dfseo-progress-bar">
							<div class="dfseo-progress-fill" id="dfseo-desc-progress" style="width:<?php echo esc_attr( min( 100, round( mb_strlen( $meta_desc ) / 160 * 100 ) ) ); ?>%"></div>
						</div>
						<p class="dfseo-hint"><?php esc_html_e( 'Ideal: 120–160 characters. Leave empty to auto-generate from content.', 'dadsfam-seo' ); ?></p>
					</div>

					<!-- Analysis results panel -->
					<div class="dfseo-analysis-panel" id="dfseo-analysis-panel">
						<div class="dfseo-analysis-loading" id="dfseo-analysis-loading" style="display:none">
							<span class="dfseo-spinner"></span> <?php esc_html_e( 'Running analysis…', 'dadsfam-seo' ); ?>
						</div>
						<div id="dfseo-analysis-results"></div>
					</div>
				</div>

				<!-- Tab: Readability -->
				<div class="dfseo-tab-content" id="dfseo-tab-readability" style="display:none">
					<div class="dfseo-readability-summary" id="dfseo-readability-summary">
						<p class="dfseo-muted"><?php esc_html_e( 'Run an analysis to see readability results.', 'dadsfam-seo' ); ?></p>
					</div>
				</div>

				<!-- Tab: Social -->
				<div class="dfseo-tab-content" id="dfseo-tab-social" style="display:none">
					<h4><?php esc_html_e( 'Open Graph / Facebook', 'dadsfam-seo' ); ?></h4>

					<div class="dfseo-field-group">
						<label class="dfseo-label"><?php esc_html_e( 'Social Share Image', 'dadsfam-seo' ); ?></label>
						<div class="dfseo-og-image-wrap" id="dfseo-og-image-wrap">
							<?php if ( $og_img_url ) : ?>
								<img src="<?php echo esc_url( $og_img_url ); ?>" class="dfseo-og-thumb" id="dfseo-og-thumb">
							<?php else : ?>
								<div class="dfseo-og-placeholder" id="dfseo-og-thumb">
									<span class="dashicons dashicons-format-image"></span>
									<span><?php esc_html_e( 'No image set — falls back to featured image', 'dadsfam-seo' ); ?></span>
								</div>
							<?php endif; ?>
							<input type="hidden" name="_dfseo_og_image_id" id="dfseo_og_image_id" value="<?php echo esc_attr( $og_img_id ); ?>">
						</div>
						<button type="button" class="dfseo-btn dfseo-btn-secondary" id="dfseo-og-image-btn">
							<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Select Image', 'dadsfam-seo' ); ?>
						</button>
						<?php if ( $og_img_id ) : ?>
							<button type="button" class="dfseo-btn dfseo-btn-ghost" id="dfseo-og-image-remove"><?php esc_html_e( 'Remove', 'dadsfam-seo' ); ?></button>
						<?php endif; ?>
						<p class="dfseo-hint"><?php esc_html_e( 'Recommended: 1200×630px. Used for Facebook, LinkedIn, and other social shares.', 'dadsfam-seo' ); ?></p>
					</div>

					<div class="dfseo-og-preview" id="dfseo-og-preview">
						<div class="dfseo-og-card">
							<div class="dfseo-og-card-img" id="dfseo-og-card-img" style="<?php echo $og_img_url ? 'background-image:url(' . esc_url( $og_img_url ) . ')' : ''; ?>"></div>
							<div class="dfseo-og-card-body">
								<div class="dfseo-og-card-site"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></div>
								<div class="dfseo-og-card-title" id="dfseo-og-card-title"><?php echo esc_html( $seo_title ?: $post_title ); ?></div>
								<div class="dfseo-og-card-desc" id="dfseo-og-card-desc"><?php echo esc_html( $meta_desc ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 20 ) ); ?></div>
							</div>
						</div>
					</div>
				</div>

				<!-- Tab: Advanced -->
				<div class="dfseo-tab-content" id="dfseo-tab-advanced" style="display:none">

					<div class="dfseo-field-group">
						<label class="dfseo-label" for="dfseo_canonical">
							<?php esc_html_e( 'Canonical URL', 'dadsfam-seo' ); ?>
						</label>
						<input type="url" id="dfseo_canonical" name="_dfseo_canonical"
							value="<?php echo esc_attr( $canonical ); ?>"
							placeholder="<?php echo esc_attr( $permalink ); ?>"
							class="dfseo-input">
						<p class="dfseo-hint"><?php esc_html_e( 'Leave empty to use the default permalink. Set a custom URL only if this content is duplicated elsewhere.', 'dadsfam-seo' ); ?></p>
					</div>

					<div class="dfseo-field-group">
						<label class="dfseo-label"><?php esc_html_e( 'Robots Directives', 'dadsfam-seo' ); ?></label>
						<div class="dfseo-checkboxes">
							<label class="dfseo-check-label">
								<input type="checkbox" name="_dfseo_noindex" value="1" <?php checked( $noindex, '1' ); ?>>
								<?php esc_html_e( 'noindex — Prevent search engines from indexing this page', 'dadsfam-seo' ); ?>
							</label>
							<label class="dfseo-check-label">
								<input type="checkbox" name="_dfseo_nofollow" value="1" <?php checked( $nofollow, '1' ); ?>>
								<?php esc_html_e( 'nofollow — Prevent search engines from following links on this page', 'dadsfam-seo' ); ?>
							</label>
							<label class="dfseo-check-label">
								<input type="checkbox" name="_dfseo_noarchive" value="1" <?php checked( $noarchive, '1' ); ?>>
								<?php esc_html_e( 'noarchive — Prevent search engines from caching this page', 'dadsfam-seo' ); ?>
							</label>
							<label class="dfseo-check-label">
								<input type="checkbox" name="_dfseo_nosnippet" value="1" <?php checked( $nosnippet, '1' ); ?>>
								<?php esc_html_e( 'nosnippet — Prevent search engines from showing a text snippet in results', 'dadsfam-seo' ); ?>
							</label>
						</div>
					</div>

					<div class="dfseo-field-group">
						<label class="dfseo-label" for="dfseo_schema_type">
							<?php esc_html_e( 'Schema Type', 'dadsfam-seo' ); ?>
						</label>
						<select id="dfseo_schema_type" name="_dfseo_schema_type" class="dfseo-select">
							<?php
							$types = [ 'WebPage' => 'Web Page', 'AboutPage' => 'About Page', 'ContactPage' => 'Contact Page', 'FAQPage' => 'FAQ Page', 'Article' => 'Article', 'BlogPosting' => 'Blog Post', 'NewsArticle' => 'News Article', 'CollectionPage' => 'Collection Page', 'ItemPage' => 'Item Page' ];
							foreach ( $types as $val => $label ) {
								printf( '<option value="%s"%s>%s</option>', esc_attr( $val ), selected( $schema_type, $val, false ), esc_html( $label ) );
							}
							?>
						</select>
					</div>
				</div>

				<?php if ( $premium ) : ?>
				<!-- Tab: Schema (premium) -->
				<div class="dfseo-tab-content" id="dfseo-tab-schema" style="display:none">
					<?php $this->render_schema_tab( $post ); ?>
				</div>

				<!-- Tab: AI Tools (premium) -->
				<div class="dfseo-tab-content" id="dfseo-tab-ai" style="display:none">
					<?php $this->render_ai_tab( $post ); ?>
				</div>
				<?php endif; ?>

			</div><!-- .dfseo-tabs -->
		</div><!-- .dfseo-metabox -->
		<?php
	}

	// ─── Schema tab (premium) ───────────────────────────────────────────────

	private function render_schema_tab( WP_Post $post ): void {
		$faqs = (array) get_post_meta( $post->ID, '_dfseo_faq_items', true );
		?>
		<div class="dfseo-schema-section">
			<h4><?php esc_html_e( 'FAQ Schema', 'dadsfam-seo' ); ?></h4>
			<p class="dfseo-hint"><?php esc_html_e( 'Add FAQs to generate rich results in Google search.', 'dadsfam-seo' ); ?></p>
			<div id="dfseo-faq-list">
				<?php foreach ( $faqs as $i => $faq ) : ?>
					<div class="dfseo-faq-item" data-index="<?php echo esc_attr( $i ); ?>">
						<input type="text" class="dfseo-input dfseo-faq-q" placeholder="<?php esc_attr_e( 'Question…', 'dadsfam-seo' ); ?>" value="<?php echo esc_attr( $faq['q'] ?? '' ); ?>">
						<textarea class="dfseo-textarea dfseo-faq-a" rows="2" placeholder="<?php esc_attr_e( 'Answer…', 'dadsfam-seo' ); ?>"><?php echo esc_textarea( $faq['a'] ?? '' ); ?></textarea>
						<button type="button" class="dfseo-btn dfseo-btn-ghost dfseo-faq-remove"><?php esc_html_e( 'Remove', 'dadsfam-seo' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="dfseo-btn dfseo-btn-secondary" id="dfseo-faq-add">
				+ <?php esc_html_e( 'Add FAQ', 'dadsfam-seo' ); ?>
			</button>
			<button type="button" class="dfseo-btn dfseo-btn-ai" id="dfseo-faq-ai-generate">
				✨ <?php esc_html_e( 'Generate with AI', 'dadsfam-seo' ); ?>
			</button>
			<input type="hidden" name="_dfseo_faq_items" id="dfseo_faq_items" value="">
		</div>
		<?php
	}

	// ─── AI tab (premium) ───────────────────────────────────────────────────

	private function render_ai_tab( WP_Post $post ): void {
		?>
		<div class="dfseo-ai-panel">
			<div class="dfseo-ai-intro">
				<span class="dfseo-ai-badge">✨ Powered by Claude AI</span>
				<p><?php esc_html_e( 'Use AI to generate and optimise your SEO content in seconds.', 'dadsfam-seo' ); ?></p>
			</div>

			<div class="dfseo-ai-actions">
				<div class="dfseo-ai-card" id="dfseo-ai-meta">
					<h4>🎯 <?php esc_html_e( 'Generate Meta Tags', 'dadsfam-seo' ); ?></h4>
					<p><?php esc_html_e( 'Automatically write the perfect SEO title and meta description for your content.', 'dadsfam-seo' ); ?></p>
					<button type="button" class="dfseo-btn dfseo-btn-ai dfseo-ai-action" data-action="generate-meta">
						<span class="dfseo-btn-text"><?php esc_html_e( 'Generate Meta Tags', 'dadsfam-seo' ); ?></span>
						<span class="dfseo-btn-loading" style="display:none"><?php esc_html_e( 'Generating…', 'dadsfam-seo' ); ?></span>
					</button>
				</div>

				<div class="dfseo-ai-card" id="dfseo-ai-keywords">
					<h4>🔍 <?php esc_html_e( 'Keyword Suggestions', 'dadsfam-seo' ); ?></h4>
					<p><?php esc_html_e( 'Get 10 relevant keyword ideas including long-tail and semantic variations.', 'dadsfam-seo' ); ?></p>
					<button type="button" class="dfseo-btn dfseo-btn-ai dfseo-ai-action" data-action="suggest-keywords">
						<span class="dfseo-btn-text"><?php esc_html_e( 'Suggest Keywords', 'dadsfam-seo' ); ?></span>
						<span class="dfseo-btn-loading" style="display:none"><?php esc_html_e( 'Searching…', 'dadsfam-seo' ); ?></span>
					</button>
				</div>

				<div class="dfseo-ai-card" id="dfseo-ai-optimize">
					<h4>📈 <?php esc_html_e( 'Content Optimisation', 'dadsfam-seo' ); ?></h4>
					<p><?php esc_html_e( 'Get specific, actionable recommendations to improve your content\'s SEO score.', 'dadsfam-seo' ); ?></p>
					<button type="button" class="dfseo-btn dfseo-btn-ai dfseo-ai-action" data-action="optimize-content">
						<span class="dfseo-btn-text"><?php esc_html_e( 'Optimise Content', 'dadsfam-seo' ); ?></span>
						<span class="dfseo-btn-loading" style="display:none"><?php esc_html_e( 'Analysing…', 'dadsfam-seo' ); ?></span>
					</button>
				</div>

				<div class="dfseo-ai-card" id="dfseo-ai-titles">
					<h4>✏️ <?php esc_html_e( 'Title Variants', 'dadsfam-seo' ); ?></h4>
					<p><?php esc_html_e( 'Generate 5 alternative SEO title ideas to maximise click-through rate.', 'dadsfam-seo' ); ?></p>
					<button type="button" class="dfseo-btn dfseo-btn-ai dfseo-ai-action" data-action="generate-title-variants">
						<span class="dfseo-btn-text"><?php esc_html_e( 'Get Title Ideas', 'dadsfam-seo' ); ?></span>
						<span class="dfseo-btn-loading" style="display:none"><?php esc_html_e( 'Thinking…', 'dadsfam-seo' ); ?></span>
					</button>
				</div>

				<div class="dfseo-ai-card" id="dfseo-ai-outline">
					<h4>📋 <?php esc_html_e( 'Content Outline', 'dadsfam-seo' ); ?></h4>
					<p><?php esc_html_e( 'Generate a comprehensive SEO-optimised content outline with headings and key points.', 'dadsfam-seo' ); ?></p>
					<button type="button" class="dfseo-btn dfseo-btn-ai dfseo-ai-action" data-action="content-outline">
						<span class="dfseo-btn-text"><?php esc_html_e( 'Create Outline', 'dadsfam-seo' ); ?></span>
						<span class="dfseo-btn-loading" style="display:none"><?php esc_html_e( 'Outlining…', 'dadsfam-seo' ); ?></span>
					</button>
				</div>
			</div>

			<div class="dfseo-ai-result" id="dfseo-ai-result" style="display:none">
				<div class="dfseo-ai-result-header">
					<strong id="dfseo-ai-result-title"></strong>
					<button type="button" class="dfseo-btn-close" id="dfseo-ai-result-close">×</button>
				</div>
				<div id="dfseo-ai-result-content"></div>
			</div>
		</div>
		<?php
	}

	// ─── Save ────────────────────────────────────────────────────────────────

	public function save_meta_box( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['dfseo_meta_box_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dfseo_meta_box_nonce'] ) ), 'dfseo_meta_box_save' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( wp_is_post_revision( $post_id ) ) return;

		// Standard fields
		$string_fields = [
			'_dfseo_focus_keyword' => 'sanitize_text_field',
			'_dfseo_title'         => 'sanitize_text_field',
			'_dfseo_meta_desc'     => 'sanitize_textarea_field',
			'_dfseo_canonical'     => 'sanitize_url',
			'_dfseo_schema_type'   => 'sanitize_text_field',
		];
		foreach ( $string_fields as $key => $sanitizer ) {
			if ( isset( $_POST[ $key ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				update_post_meta( $post_id, $key, call_user_func( $sanitizer, wp_unslash( $_POST[ $key ] ) ) );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}

		// Checkbox fields
		foreach ( [ '_dfseo_noindex', '_dfseo_nofollow', '_dfseo_noarchive', '_dfseo_nosnippet' ] as $key ) {
			if ( ! empty( $_POST[ $key ] ) && $_POST[ $key ] === '1' ) {
				update_post_meta( $post_id, $key, '1' );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}

		// OG image
		if ( isset( $_POST['_dfseo_og_image_id'] ) ) {
			$img_id = (int) $_POST['_dfseo_og_image_id'];
			if ( $img_id ) update_post_meta( $post_id, '_dfseo_og_image_id', $img_id );
			else           delete_post_meta( $post_id, '_dfseo_og_image_id' );
		}

		// Premium: FAQ items
		if ( dfseo_is_premium() && isset( $_POST['_dfseo_faq_items'] ) ) {
			$raw  = json_decode( wp_unslash( $_POST['_dfseo_faq_items'] ), true );
			if ( is_array( $raw ) ) {
				$clean = array_map( static fn($f) => [
					'q' => sanitize_text_field( $f['q'] ?? '' ),
					'a' => wp_kses_post( $f['a'] ?? '' ),
				], $raw );
				update_post_meta( $post_id, '_dfseo_faq_items', $clean );
			}
		}
	}

	// ─── Helpers ────────────────────────────────────────────────────────────

	private function score_class( int $score ): string {
		if ( $score >= 80 ) return 'great';
		if ( $score >= 50 ) return 'ok';
		if ( $score > 0  ) return 'poor';
		return 'na';
	}

	private function score_label( int $score ): string {
		if ( $score >= 80 ) return __( 'Great', 'dadsfam-seo' );
		if ( $score >= 50 ) return __( 'Needs Work', 'dadsfam-seo' );
		if ( $score > 0  ) return __( 'Poor', 'dadsfam-seo' );
		return __( 'Not analysed', 'dadsfam-seo' );
	}
}
