<?php
/**
 * Meta tag management — title, description, robots, canonical, verification.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Meta {

	public function __construct() {
		// Remove the default title tag so we control it
		add_action( 'after_setup_theme', [ $this, 'remove_default_title_tag' ], 100 );
		add_filter( 'wp_title', '__return_empty_string', 99 );

		// Our tags
		add_action( 'wp_head', [ $this, 'output_head_tags' ],  1 );
		add_action( 'wp_head', [ $this, 'output_title_tag'  ], 1 );

		// Post list columns — specific hooks so we don't bleed into WooCommerce
		add_filter( 'manage_post_posts_columns',        [ $this, 'add_seo_column' ] );
		add_filter( 'manage_page_posts_columns',        [ $this, 'add_seo_column' ] );
		add_action( 'manage_post_posts_custom_column',  [ $this, 'render_seo_column' ], 10, 2 );
		add_action( 'manage_page_posts_custom_column',  [ $this, 'render_seo_column' ], 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns',[ $this, 'seo_column_sortable' ] );
		add_filter( 'manage_edit-page_sortable_columns',[ $this, 'seo_column_sortable' ] );
		add_action( 'pre_get_posts',                    [ $this, 'seo_column_orderby' ] );

		// WooCommerce — proper column hooks with contained layout
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'manage_product_posts_columns',        [ $this, 'add_seo_column_wc' ] );
			add_action( 'manage_product_posts_custom_column',  [ $this, 'render_seo_column' ], 10, 2 );
			add_action( 'admin_head',                          [ $this, 'wc_column_css' ] );
		}
	}

	public function remove_default_title_tag(): void {
		add_theme_support( 'title-tag' );
	}

	// ─── <title> ────────────────────────────────────────────────────────────

	public function output_title_tag(): void {
		echo '<title>' . esc_html( $this->get_page_title() ) . '</title>' . "\n";
	}

	public function get_page_title(): string {
		$sep      = (string) get_option( 'dfseo_separator', '–' );
		$sitename = get_bloginfo( 'name' );

		if ( is_singular() ) {
			global $post;
			$custom = (string) get_post_meta( $post->ID, '_dfseo_title', true );
			if ( $custom ) return $this->parse_template( $custom, $post );
			$tpl = (string) get_option( 'dfseo_title_template', '%title% %sep% %sitename%' );
			return $this->parse_template( $tpl, $post );
		}

		if ( is_home() || is_front_page() ) {
			$title = (string) get_option( 'dfseo_home_title', $sitename );
			return $title ?: $sitename;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$custom = $term ? (string) get_term_meta( $term->term_id, '_dfseo_title', true ) : '';
			if ( $custom ) return $custom;
			return ( $term ? $term->name : '' ) . " {$sep} {$sitename}";
		}

		if ( is_author() ) {
			$author = get_queried_object();
			return ( $author ? $author->display_name : '' ) . " {$sep} {$sitename}";
		}

		if ( is_search() ) {
			return sprintf( __( 'Search results for "%s"', 'dadsfam-seo' ), get_search_query() ) . " {$sep} {$sitename}";
		}

		if ( is_404() ) {
			return __( 'Page not found', 'dadsfam-seo' ) . " {$sep} {$sitename}";
		}

		if ( is_archive() ) {
			return get_the_archive_title() . " {$sep} {$sitename}";
		}

		return $sitename;
	}

	// ─── <head> tags ────────────────────────────────────────────────────────

	public function output_head_tags(): void {
		// Charset already output by WP — we add SEO tags
		$this->output_meta_description();
		$this->output_canonical();
		$this->output_robots();
		$this->output_verification_tags();
	}

	private function output_meta_description(): void {
		$desc = $this->get_meta_description();
		if ( $desc ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );
		}
	}

	public function get_meta_description(): string {
		if ( is_singular() ) {
			global $post;
			$custom = (string) get_post_meta( $post->ID, '_dfseo_meta_desc', true );
			if ( $custom ) return $custom;
			// Auto-generate from excerpt or content
			$excerpt = $post->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '…' );
			return $excerpt;
		}

		if ( is_home() || is_front_page() ) {
			return (string) get_option( 'dfseo_home_description', get_bloginfo( 'description' ) );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$custom = $term ? (string) get_term_meta( $term->term_id, '_dfseo_meta_desc', true ) : '';
			if ( $custom ) return $custom;
			return $term ? wp_strip_all_tags( $term->description ) : '';
		}

		return '';
	}

	private function output_canonical(): void {
		$url = $this->get_canonical_url();
		if ( $url ) {
			printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
		}
	}

	public function get_canonical_url(): string {
		if ( is_singular() ) {
			global $post;
			$custom = (string) get_post_meta( $post->ID, '_dfseo_canonical', true );
			if ( $custom ) return $custom;
			return (string) get_permalink( $post->ID );
		}
		if ( is_home() )          return (string) get_option( 'siteurl' ) . '/';
		if ( is_front_page() )    return (string) home_url( '/' );
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			return $term ? (string) get_term_link( $term ) : '';
		}
		if ( is_author() ) {
			$user = get_queried_object();
			return $user ? get_author_posts_url( $user->ID ) : '';
		}
		return (string) get_pagenum_link();
	}

	private function output_robots(): void {
		$directives = $this->get_robots_directives();
		if ( ! empty( $directives ) ) {
			printf( '<meta name="robots" content="%s" />' . "\n", esc_attr( implode( ', ', $directives ) ) );
		}
	}

	public function get_robots_directives(): array {
		$directives = [];

		if ( is_singular() ) {
			global $post;
			$noindex   = (string) get_post_meta( $post->ID, '_dfseo_noindex',   true );
			$nofollow  = (string) get_post_meta( $post->ID, '_dfseo_nofollow',  true );
			$noarchive = (string) get_post_meta( $post->ID, '_dfseo_noarchive', true );
			$nosnippet = (string) get_post_meta( $post->ID, '_dfseo_nosnippet', true );

			if ( $noindex   === '1' ) $directives[] = 'noindex';
			if ( $nofollow  === '1' ) $directives[] = 'nofollow';
			if ( $noarchive === '1' ) $directives[] = 'noarchive';
			if ( $nosnippet === '1' ) $directives[] = 'nosnippet';
		}

		if ( is_author()   && get_option( 'dfseo_noindex_author',   '0' ) === '1' ) $directives[] = 'noindex';
		if ( is_date()     && get_option( 'dfseo_noindex_archives', '0' ) === '1' ) $directives[] = 'noindex';
		if ( is_tag()      && get_option( 'dfseo_noindex_tags',     '0' ) === '1' ) $directives[] = 'noindex';
		if ( is_404()      ) { $directives[] = 'noindex'; $directives[] = 'follow'; }
		if ( is_search()   ) { $directives[] = 'noindex'; $directives[] = 'follow'; }
		if ( is_paged()    && get_option( 'dfseo_noindex_paged',    '0' ) === '1' ) $directives[] = 'noindex';

		if ( empty( $directives ) ) {
			$directives = [ 'index', 'follow' ];
		} else {
			// Ensure we have index or noindex
			if ( ! in_array( 'noindex', $directives ) ) $directives[] = 'index';
			if ( ! in_array( 'nofollow', $directives ) ) $directives[] = 'follow';
		}

		return array_unique( $directives );
	}

	private function output_verification_tags(): void {
		$tags = [
			'google'    => [ 'name' => 'google-site-verification',    'option' => 'dfseo_google_verify' ],
			'bing'      => [ 'name' => 'msvalidate.01',               'option' => 'dfseo_bing_verify' ],
			'yandex'    => [ 'name' => 'yandex-verification',         'option' => 'dfseo_yandex_verify' ],
			'pinterest' => [ 'name' => 'p:domain_verify',             'option' => 'dfseo_pinterest_verify' ],
			'norton'    => [ 'name' => 'norton-safeweb-site-verification', 'option' => 'dfseo_norton_verify' ],
		];
		foreach ( $tags as $tag ) {
			$val = (string) get_option( $tag['option'], '' );
			if ( $val ) {
				printf( '<meta name="%s" content="%s" />' . "\n", esc_attr( $tag['name'] ), esc_attr( $val ) );
			}
		}
	}

	// ─── Template parser ────────────────────────────────────────────────────

	public function parse_template( string $tpl, ?WP_Post $post = null ): string {
		$sep      = (string) get_option( 'dfseo_separator', '–' );
		$sitename = get_bloginfo( 'name' );
		$vars     = [
			'%title%'    => $post ? get_the_title( $post ) : $sitename,
			'%sitename%' => $sitename,
			'%sep%'      => $sep,
			'%page%'     => is_paged() ? sprintf( __( 'Page %d', 'dadsfam-seo' ), get_query_var( 'paged' ) ) : '',
			'%tagline%'  => get_bloginfo( 'description' ),
			'%category%' => $post ? implode( ', ', wp_get_post_terms( $post->ID, 'category', [ 'fields' => 'names' ] ) ) : '',
			'%year%'     => date( 'Y' ),
		];
		$result = str_replace( array_keys( $vars ), array_values( $vars ), $tpl );
		return trim( preg_replace( '/\s+/', ' ', $result ) );
	}

	// ─── Post list columns ───────────────────────────────────────────────────

	public function add_seo_column( array $columns ): array {
		$columns['dfseo_score'] = '<span title="' . esc_attr__( 'SEO Score', 'dadsfam-seo' ) . '">🏆 SEO</span>';
		return $columns;
	}

	/** WooCommerce version — abbreviated header to keep the table tidy */
	public function add_seo_column_wc( array $columns ): array {
		// Insert before 'date' so it sits neatly at the right of the table
		$date = $columns['date'] ?? null;
		unset( $columns['date'] );
		$columns['dfseo_score'] = '<span title="' . esc_attr__( 'SEO Score', 'dadsfam-seo' ) . '">🏆</span>';
		if ( $date ) $columns['date'] = $date;
		return $columns;
	}

	/** Inject narrow width CSS for the SEO column on all admin list tables */
	public function wc_column_css(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'edit-product' ) return;
		echo '<style>
			.column-dfseo_score { width:54px !important; text-align:center !important; }
			.column-dfseo_score .dfseo-col-na { color:#aaa; }
		</style>';
	}

	public function render_seo_column( string $column, int $post_id ): void {
		if ( $column !== 'dfseo_score' ) return;
		$score = (int) get_post_meta( $post_id, '_dfseo_score', true );
		$kw    = (string) get_post_meta( $post_id, '_dfseo_focus_keyword', true );
		if ( ! $kw ) {
			echo '<span class="dfseo-col-na" title="' . esc_attr__( 'No focus keyword set', 'dadsfam-seo' ) . '">—</span>';
			return;
		}
		$color = $score >= 80 ? '#16a34a' : ( $score >= 50 ? '#d97706' : '#dc2626' );
		$label = $score >= 80 ? 'Great' : ( $score >= 50 ? 'OK' : 'Poor' );
		printf(
			'<span class="dfseo-score-badge" style="background:%s;color:#fff;padding:2px 8px;border-radius:99px;font-size:12px;font-weight:600" title="%s / 100">%d</span>',
			esc_attr( $color ),
			esc_attr( $label ),
			$score
		);
	}

	public function seo_column_sortable( array $columns ): array {
		$columns['dfseo_score'] = 'dfseo_score';
		return $columns;
	}

	public function seo_column_orderby( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) return;
		if ( $query->get( 'orderby' ) === 'dfseo_score' ) {
			$query->set( 'meta_key', '_dfseo_score' );
			$query->set( 'orderby',  'meta_value_num' );
		}
	}
}
