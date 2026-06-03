<?php
/**
 * Breadcrumbs — generates semantic HTML breadcrumb trail + schema data.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Breadcrumbs {

	public function __construct() {
		// Shortcode
		add_shortcode( 'dfseo_breadcrumbs', [ $this, 'render_html' ] );
		// Template function support
	}

	/**
	 * Render breadcrumb HTML.
	 */
	public function render_html( array $args = [] ): string {
		if ( get_option( 'dfseo_breadcrumbs_enable', '1' ) !== '1' ) return '';
		$items = $this->get_breadcrumb_items();
		if ( count( $items ) < 2 ) return '';

		$sep   = esc_html( get_option( 'dfseo_breadcrumbs_separator', '›' ) );
		$html  = '<nav class="dfseo-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'dadsfam-seo' ) . '">';
		$html .= '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';

		foreach ( $items as $i => $item ) {
			$is_last = ( $i === count( $items ) - 1 );
			$html   .= sprintf(
				'<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">%s<meta itemprop="position" content="%d"/>',
				$is_last
					? '<span itemprop="name">' . esc_html( $item['name'] ) . '</span>'
					: '<a href="' . esc_url( $item['url'] ) . '" itemprop="item"><span itemprop="name">' . esc_html( $item['name'] ) . '</span></a>',
				$i + 1
			);
			$html .= '</li>';
			if ( ! $is_last ) $html .= ' <span class="dfseo-bc-sep" aria-hidden="true">' . $sep . '</span> ';
		}
		$html .= '</ol></nav>';
		return $html;
	}

	/**
	 * Get array of breadcrumb items [name, url].
	 * @return array<int, array{name:string, url:string}>
	 */
	public function get_breadcrumb_items(): array {
		$items    = [];
		$home_lbl = (string) get_option( 'dfseo_breadcrumbs_home_label', __( 'Home', 'dadsfam-seo' ) );
		$items[]  = [ 'name' => $home_lbl, 'url' => home_url( '/' ) ];

		if ( is_singular() ) {
			global $post;
			// Categories (for posts)
			if ( $post->post_type === 'post' ) {
				$cats = get_the_category( $post->ID );
				if ( $cats ) {
					$cat     = $cats[0];
					$parents = array_reverse( get_ancestors( $cat->term_id, 'category', 'taxonomy' ) );
					foreach ( $parents as $parent_id ) {
						$parent  = get_term( $parent_id, 'category' );
						$items[] = [ 'name' => $parent->name, 'url' => get_term_link( $parent ) ];
					}
					$items[] = [ 'name' => $cat->name, 'url' => get_term_link( $cat ) ];
				}
			}
			// Pages hierarchy
			if ( $post->post_type === 'page' && $post->post_parent ) {
				$parents = array_reverse( get_ancestors( $post->ID, 'page', 'post_type' ) );
				foreach ( $parents as $parent_id ) {
					$items[] = [ 'name' => get_the_title( $parent_id ), 'url' => get_permalink( $parent_id ) ];
				}
			}
			$items[] = [ 'name' => get_the_title( $post->ID ), 'url' => '' ];

		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && $term->parent ) {
				$parent  = get_term( $term->parent, $term->taxonomy );
				$items[] = [ 'name' => $parent->name, 'url' => get_term_link( $parent ) ];
			}
			$items[] = [ 'name' => $term->name, 'url' => '' ];

		} elseif ( is_author() ) {
			$user    = get_queried_object();
			$items[] = [ 'name' => $user->display_name, 'url' => '' ];

		} elseif ( is_search() ) {
			$items[] = [ 'name' => sprintf( __( 'Search: %s', 'dadsfam-seo' ), get_search_query() ), 'url' => '' ];

		} elseif ( is_404() ) {
			$items[] = [ 'name' => __( '404 – Page Not Found', 'dadsfam-seo' ), 'url' => '' ];

		} elseif ( is_archive() ) {
			$items[] = [ 'name' => get_the_archive_title(), 'url' => '' ];
		}

		return $items;
	}
}
