<?php
/**
 * SEO & Readability Analysis Engine.
 *
 * Analyses a post against its focus keyword and returns a structured
 * score object used by both the meta-box live preview and REST API.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Analysis {

	// ─── Score boundaries ───────────────────────────────────────────────────
	const SCORE_GREAT       = 80;
	const SCORE_OK          = 50;
	const SCORE_POOR        = 0;

	// ─── Check weights (total ≈ 100) ────────────────────────────────────────
	const WEIGHTS = [
		// Keyword checks
		'keyword_in_title'         => 9,
		'keyword_in_meta_desc'     => 8,
		'keyword_in_url'           => 6,
		'keyword_in_intro'         => 7,
		'keyword_density'          => 6,
		'keyword_in_h1'            => 5,
		'keyword_in_headings'      => 3,
		'keyword_in_alt'           => 3,

		// Technical
		'title_length'             => 5,
		'meta_desc_length'         => 5,
		'has_h1'                   => 4,
		'has_subheadings'          => 3,
		'content_length'           => 6,
		'has_images'               => 3,
		'all_images_have_alt'      => 3,
		'has_internal_links'       => 4,
		'has_external_links'       => 2,
		'no_keyword_stuffing'      => 3,

		// Readability
		'readability_sentence_len' => 4,
		'readability_paragraph'    => 3,
		'readability_transition'   => 2,
		'readability_passive'      => 2,
		'readability_fleschkincaid'=> 3,

		// GEO — citability in AI answer engines (ChatGPT, AI Overviews, Perplexity…)
		'geo_question_heading'     => 3,
		'geo_has_list_or_table'    => 2,
		'geo_direct_answer'        => 3,
	];

	// Transition words (English) - improves readability score
	const TRANSITION_WORDS = [
		'because', 'therefore', 'furthermore', 'however', 'meanwhile',
		'additionally', 'consequently', 'nevertheless', 'moreover',
		'accordingly', 'alternatively', 'similarly', 'although',
		'despite', 'whereas', 'since', 'while', 'unless', 'once',
		'whenever', 'finally', 'first', 'second', 'third', 'also',
		'besides', 'specifically', 'notably', 'especially', 'indeed',
		'generally', 'particularly', 'essentially', 'typically',
	];

	// ─── Boot ───────────────────────────────────────────────────────────────

	public function __construct() {
		add_action( 'save_post', [ $this, 'update_post_score' ], 20 );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes(): void {
		register_rest_route( 'dfseo/v1', '/analyse', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'rest_analyse' ],
			'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
			'args'                => [
				'post_id'         => [ 'type' => 'integer', 'required' => true ],
				'focus_keyword'   => [ 'type' => 'string',  'default'  => '' ],
				'title'           => [ 'type' => 'string',  'default'  => '' ],
				'meta_desc'       => [ 'type' => 'string',  'default'  => '' ],
				'content'         => [ 'type' => 'string',  'default'  => '' ],
			],
		] );

		register_rest_route( 'dfseo/v1', '/link-suggestions', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'rest_link_suggestions' ],
			'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
			'args'                => [
				'post_id' => [ 'type' => 'integer', 'required' => true ],
				'keyword' => [ 'type' => 'string',  'default'  => '' ],
				'title'   => [ 'type' => 'string',  'default'  => '' ],
			],
		] );
	}

	/**
	 * Suggest existing published posts/pages to link to from the current post.
	 * Relevance: focus keyword match > title-word overlap. Free feature.
	 */
	public function rest_link_suggestions( WP_REST_Request $request ): WP_REST_Response {
		$post_id = (int) $request['post_id'];
		$keyword = sanitize_text_field( (string) $request['keyword'] );
		$title   = sanitize_text_field( (string) $request['title'] );

		// Build search terms from the focus keyword and title words.
		$terms = [];
		if ( $keyword !== '' ) $terms[] = $keyword;
		if ( $title !== '' ) {
			// pull meaningful words (4+ chars) from the title
			$stop = [ 'the','and','for','with','your','from','that','this','have','how','what','why','when','will','best','are','you' ];
			foreach ( preg_split( '/\s+/', strtolower( $title ) ) as $w ) {
				$w = preg_replace( '/[^a-z0-9]/', '', $w );
				if ( strlen( $w ) >= 4 && ! in_array( $w, $stop, true ) ) $terms[] = $w;
			}
		}
		$terms = array_slice( array_unique( $terms ), 0, 6 );

		if ( empty( $terms ) ) {
			return new WP_REST_Response( [ 'suggestions' => [], 'reason' => 'no_terms' ], 200 );
		}

		// Current post permalink so we can detect existing links.
		$current_content = (string) get_post_field( 'post_content', $post_id );

		$scored = [];
		$types  = array_values( get_post_types( [ 'public' => true ] ) );
		unset( $types['attachment'] );

		foreach ( $terms as $i => $term ) {
			$q = new WP_Query( [
				'post_type'           => $types,
				'post_status'         => 'publish',
				's'                   => $term,
				'posts_per_page'      => 8,
				'post__not_in'        => [ $post_id ],
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'orderby'             => 'relevance',
			] );
			foreach ( $q->posts as $p ) {
				$url = get_permalink( $p->ID );
				if ( ! isset( $scored[ $p->ID ] ) ) {
					$already = ( $url && strpos( $current_content, $url ) !== false );
					$scored[ $p->ID ] = [
						'id'            => $p->ID,
						'title'         => html_entity_decode( get_the_title( $p->ID ), ENT_QUOTES, 'UTF-8' ),
						'url'           => $url,
						'type'          => get_post_type( $p->ID ),
						'already_linked'=> $already,
						'score'         => 0,
						'matched'       => [],
					];
				}
				// earlier terms (focus keyword first) weigh more
				$scored[ $p->ID ]['score'] += ( 10 - $i );
				$scored[ $p->ID ]['matched'][] = $term;
			}
			wp_reset_postdata();
		}

		// Sort: not-yet-linked first, then by score
		usort( $scored, static function ( $a, $b ) {
			if ( $a['already_linked'] !== $b['already_linked'] ) {
				return $a['already_linked'] ? 1 : -1;
			}
			return $b['score'] <=> $a['score'];
		} );

		$out = array_slice( array_values( $scored ), 0, 8 );
		foreach ( $out as &$o ) {
			$o['matched'] = array_values( array_unique( $o['matched'] ) );
		}

		return new WP_REST_Response( [
			'suggestions' => $out,
			'terms'       => $terms,
		], 200 );
	}

	public function rest_analyse( WP_REST_Request $request ): WP_REST_Response {
		$post_id = (int) $request['post_id'];
		$result  = $this->analyse_post( $post_id, [
			'focus_keyword' => sanitize_text_field( $request['focus_keyword'] ),
			'title'         => sanitize_text_field( $request['title'] ),
			'meta_desc'     => sanitize_textarea_field( $request['meta_desc'] ),
			'content'       => wp_kses_post( $request['content'] ),
		] );
		return new WP_REST_Response( $result, 200 );
	}

	// ─── Main analysis method ───────────────────────────────────────────────

	/**
	 * Analyse a post and return the full result array.
	 *
	 * @param int   $post_id       Post ID.
	 * @param array $override_data Optional overrides (for live AJAX preview).
	 * @return array
	 */
	public function analyse_post( int $post_id, array $override_data = [] ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $this->empty_result();
		}

		// Gather data
		$focus_kw   = $override_data['focus_keyword'] ?? (string) get_post_meta( $post_id, '_dfseo_focus_keyword', true );
		$seo_title  = $override_data['title']         ?? (string) get_post_meta( $post_id, '_dfseo_title', true );
		$seo_desc   = $override_data['meta_desc']     ?? (string) get_post_meta( $post_id, '_dfseo_meta_desc', true );
		$content    = $override_data['content']       ?? $post->post_content;

		// Derived helpers
		$kw          = strtolower( trim( $focus_kw ) );
		$has_kw      = $kw !== '';
		$plain_title = strtolower( wp_strip_all_tags( $seo_title ?: $post->post_title ) );
		$plain_desc  = strtolower( wp_strip_all_tags( $seo_desc ) );
		$plain_url   = strtolower( urldecode( get_permalink( $post_id ) ) );
		$plain_content_raw = $content;
		$plain_content = strtolower( wp_strip_all_tags( $content ) );
		$word_count  = $this->word_count( $plain_content );
		$sentences   = $this->get_sentences( $plain_content );
		$paragraphs  = $this->get_paragraphs( $plain_content );

		// DOM parse for heading/image checks
		$dom    = $this->get_dom( $content );
		$h1s    = $dom ? $dom->getElementsByTagName( 'h1' ) : null;
		$h2s    = $dom ? $dom->getElementsByTagName( 'h2' ) : null;
		$h3s    = $dom ? $dom->getElementsByTagName( 'h3' ) : null;
		$imgs   = $dom ? $dom->getElementsByTagName( 'img' ) : null;
		$links  = $dom ? $dom->getElementsByTagName( 'a' )   : null;

		$first_para = $this->get_first_paragraph_text( $plain_content_raw );

		$checks  = [];
		$total_w = 0;
		$total_s = 0;

		foreach ( self::WEIGHTS as $id => $weight ) {
			$result = $this->run_check( $id, $weight, compact(
				'kw', 'has_kw', 'plain_title', 'plain_desc', 'plain_url',
				'plain_content', 'word_count', 'sentences', 'paragraphs',
				'h1s', 'h2s', 'h3s', 'imgs', 'links',
				'first_para', 'seo_title', 'seo_desc', 'focus_kw', 'dom'
			) );
			$checks[ $id ] = $result;
			$total_w += $weight;
			$total_s += $result['score'];
		}

		$raw_score = $total_w > 0 ? round( ( $total_s / $total_w ) * 100 ) : 0;
		$raw_score = max( 0, min( 100, $raw_score ) );

		$readability = $this->compute_readability_score( $sentences, $plain_content );

		return [
			'has_keyword'       => $has_kw,
			'focus_keyword'     => $focus_kw,
			'seo_score'         => $raw_score,
			'seo_grade'         => $this->grade( $raw_score ),
			'readability_score' => $readability['score'],
			'readability_grade' => $this->grade( $readability['score'] ),
			'word_count'        => $word_count,
			'checks'            => $checks,
			'readability_data'  => $readability,
		];
	}

	// ─── Individual check runner ────────────────────────────────────────────

	private function run_check( string $id, int $weight, array $d ): array {
		$kw       = $d['kw'];
		$has_kw   = $d['has_kw'];
		$result   = null;

		switch ( $id ) {

			case 'keyword_in_title':
				if ( ! $has_kw ) { return $this->na_check( $id, $weight ); }
				$ok = strpos( $d['plain_title'], $kw ) !== false;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? sprintf( __( 'Focus keyword "%s" found in SEO title.', 'dadsfam-seo' ), esc_html( $kw ) )
						: sprintf( __( 'Add the focus keyword "%s" to the SEO title.', 'dadsfam-seo' ), esc_html( $kw ) )
				);
				break;

			case 'keyword_in_meta_desc':
				if ( ! $has_kw ) { return $this->na_check( $id, $weight ); }
				$ok = ! empty( $d['plain_desc'] ) && strpos( $d['plain_desc'], $kw ) !== false;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? sprintf( __( 'Focus keyword found in meta description.', 'dadsfam-seo' ) )
						: sprintf( __( 'Add the focus keyword to the meta description.', 'dadsfam-seo' ) )
				);
				break;

			case 'keyword_in_url':
				if ( ! $has_kw ) { return $this->na_check( $id, $weight ); }
				$ok = strpos( $d['plain_url'], rawurlencode( $kw ) ) !== false
				   || strpos( $d['plain_url'], str_replace( ' ', '-', $kw ) ) !== false;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? __( 'Focus keyword appears in the URL slug.', 'dadsfam-seo' )
						: __( 'Consider including the focus keyword in the URL/slug.', 'dadsfam-seo' )
				);
				break;

			case 'keyword_in_intro':
				if ( ! $has_kw ) { return $this->na_check( $id, $weight ); }
				$ok = strpos( strtolower( $d['first_para'] ), $kw ) !== false;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? __( 'Focus keyword used in the opening paragraph. Great!', 'dadsfam-seo' )
						: __( 'Use the focus keyword in the first paragraph of your content.', 'dadsfam-seo' )
				);
				break;

			case 'keyword_density':
				if ( ! $has_kw || $d['word_count'] < 50 ) { return $this->na_check( $id, $weight ); }
				$kw_words = count( explode( ' ', $kw ) );
				$content_words = $d['word_count'];
				$kw_count = substr_count( $d['plain_content'], $kw );
				$density  = $content_words > 0 ? ( $kw_count * $kw_words / $content_words ) * 100 : 0;
				if ( $density >= 0.5 && $density <= 2.5 ) {
					$msg = sprintf( __( 'Keyword density is %.1f%% — perfect range (0.5–2.5%%).', 'dadsfam-seo' ), $density );
					$score = 1.0;
				} elseif ( $density < 0.5 ) {
					$msg = sprintf( __( 'Keyword density is %.1f%% — use the keyword a bit more in your content.', 'dadsfam-seo' ), $density );
					$score = 0.5;
				} else {
					$msg = sprintf( __( 'Keyword density is %.1f%% — you may be keyword stuffing. Aim for 0.5–2.5%%.', 'dadsfam-seo' ), $density );
					$score = 0.2;
				}
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'keyword_in_h1':
				if ( ! $has_kw ) { return $this->na_check( $id, $weight ); }
				$h1_text = '';
				if ( $d['h1s'] && $d['h1s']->length ) {
					$h1_text = strtolower( wp_strip_all_tags( $d['h1s']->item(0)->nodeValue ) );
				}
				$ok = ! empty( $h1_text ) && strpos( $h1_text, $kw ) !== false;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? __( 'Focus keyword found in the H1 heading.', 'dadsfam-seo' )
						: __( 'Add the focus keyword to the H1 heading for better on-page relevance.', 'dadsfam-seo' )
				);
				break;

			case 'keyword_in_headings':
				if ( ! $has_kw ) { return $this->na_check( $id, $weight ); }
				$found = false;
				foreach ( [ $d['h2s'], $d['h3s'] ] as $nodelist ) {
					if ( ! $nodelist ) continue;
					foreach ( $nodelist as $node ) {
						if ( strpos( strtolower( $node->nodeValue ), $kw ) !== false ) {
							$found = true;
							break 2;
						}
					}
				}
				$result = $this->check( $id, $weight, $found,
					$found
						? __( 'Focus keyword found in a sub-heading (H2/H3).', 'dadsfam-seo' )
						: __( 'Use the focus keyword in at least one H2 or H3 sub-heading.', 'dadsfam-seo' )
				);
				break;

			case 'keyword_in_alt':
				if ( ! $has_kw || ! $d['imgs'] || ! $d['imgs']->length ) { return $this->na_check( $id, $weight ); }
				$found = false;
				foreach ( $d['imgs'] as $img ) {
					$alt = strtolower( $img->getAttribute( 'alt' ) );
					if ( strpos( $alt, $kw ) !== false ) { $found = true; break; }
				}
				$result = $this->check( $id, $weight, $found,
					$found
						? __( 'Focus keyword appears in at least one image alt text.', 'dadsfam-seo' )
						: __( 'Add the focus keyword to an image alt attribute.', 'dadsfam-seo' )
				);
				break;

			case 'title_length':
				$len  = mb_strlen( wp_strip_all_tags( $d['seo_title'] ) );
				$ok   = $len >= 40 && $len <= 60;
				$mild = $len > 0 && $len < 70;
				$score = $ok ? 1.0 : ( $mild ? 0.5 : 0.0 );
				$msg  = $ok
					? sprintf( __( 'SEO title is %d characters — ideal length.', 'dadsfam-seo' ), $len )
					: ( $len < 40 ? sprintf( __( 'SEO title is too short (%d chars). Aim for 40–60.', 'dadsfam-seo' ), $len )
						: sprintf( __( 'SEO title may be truncated in SERPs (%d chars). Keep it under 60.', 'dadsfam-seo' ), $len ) );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'meta_desc_length':
				$len  = mb_strlen( wp_strip_all_tags( $d['seo_desc'] ) );
				$ok   = $len >= 120 && $len <= 160;
				$mild = $len > 0 && $len < 180;
				$score = $ok ? 1.0 : ( $mild && $len >= 70 ? 0.5 : 0.0 );
				$msg  = $len === 0
					? __( 'No meta description set. Add one to improve SERP click-through rates.', 'dadsfam-seo' )
					: ( $ok
						? sprintf( __( 'Meta description is %d characters — perfect.', 'dadsfam-seo' ), $len )
						: sprintf( __( 'Meta description is %d characters. Aim for 120–160.', 'dadsfam-seo' ), $len ) );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'has_h1':
				$has = $d['h1s'] && $d['h1s']->length > 0;
				$one = $d['h1s'] && $d['h1s']->length === 1;
				$score = $one ? 1.0 : ( $has ? 0.5 : 0.0 );
				$msg  = ! $has
					? __( 'No H1 heading found. Add one that includes your focus keyword.', 'dadsfam-seo' )
					: ( $one ? __( 'Page has exactly one H1 heading — ideal.', 'dadsfam-seo' )
						: __( 'Page has multiple H1 headings. Use only one H1 per page.', 'dadsfam-seo' ) );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'has_subheadings':
				$count = ( $d['h2s'] ? $d['h2s']->length : 0 ) + ( $d['h3s'] ? $d['h3s']->length : 0 );
				$ok    = $count > 0;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? sprintf( _n( 'Found %d sub-heading — great structure.', 'Found %d sub-headings — great structure.', $count, 'dadsfam-seo' ), $count )
						: __( 'Add H2/H3 sub-headings to improve content structure and readability.', 'dadsfam-seo' )
				);
				break;

			case 'content_length':
				$wc = $d['word_count'];
				if ( $wc >= 900 )       { $score = 1.0; $msg = sprintf( __( 'Excellent content length: %d words.', 'dadsfam-seo' ), $wc ); }
				elseif ( $wc >= 600 )   { $score = 0.85; $msg = sprintf( __( 'Good content length: %d words.', 'dadsfam-seo' ), $wc ); }
				elseif ( $wc >= 300 )   { $score = 0.6; $msg = sprintf( __( 'Content is %d words — aim for 600+ for better rankings.', 'dadsfam-seo' ), $wc ); }
				else                    { $score = 0.2; $msg = sprintf( __( 'Content is too thin: %d words. Add more depth.', 'dadsfam-seo' ), $wc ); }
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'has_images':
				$count = $d['imgs'] ? $d['imgs']->length : 0;
				$ok    = $count > 0;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? sprintf( _n( 'Content includes %d image.', 'Content includes %d images.', $count, 'dadsfam-seo' ), $count )
						: __( 'Add images to make your content more engaging.', 'dadsfam-seo' )
				);
				break;

			case 'all_images_have_alt':
				if ( ! $d['imgs'] || ! $d['imgs']->length ) { return $this->na_check( $id, $weight ); }
				$missing = 0;
				foreach ( $d['imgs'] as $img ) {
					if ( trim( $img->getAttribute( 'alt' ) ) === '' ) $missing++;
				}
				$ok    = $missing === 0;
				$score = $ok ? 1.0 : max( 0, 1 - ( $missing / $d['imgs']->length ) ) * 0.5;
				$msg   = $ok
					? __( 'All images have alt text — great for accessibility and SEO.', 'dadsfam-seo' )
					: sprintf( _n( '%d image is missing alt text.', '%d images are missing alt text.', $missing, 'dadsfam-seo' ), $missing );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'has_internal_links':
				$site = home_url();
				$int  = 0;
				if ( $d['links'] ) {
					foreach ( $d['links'] as $a ) {
						$href = $a->getAttribute( 'href' );
						if ( $href && strpos( $href, $site ) !== false ) $int++;
					}
				}
				$ok = $int > 0;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? sprintf( _n( 'Content has %d internal link.', 'Content has %d internal links.', $int, 'dadsfam-seo' ), $int )
						: __( 'Add internal links to related content to improve crawlability.', 'dadsfam-seo' )
				);
				break;

			case 'has_external_links':
				$ext = 0;
				$site = home_url();
				if ( $d['links'] ) {
					foreach ( $d['links'] as $a ) {
						$href = $a->getAttribute( 'href' );
						if ( $href && substr( $href, 0, 4 ) === 'http' && strpos( $href, $site ) === false ) $ext++;
					}
				}
				$ok = $ext > 0;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? sprintf( _n( 'Content has %d external link.', 'Content has %d external outbound links.', $ext, 'dadsfam-seo' ), $ext )
						: __( 'Consider linking to authoritative external sources to add credibility.', 'dadsfam-seo' )
				);
				break;

			case 'no_keyword_stuffing':
				if ( ! $has_kw || $d['word_count'] < 50 ) { return $this->na_check( $id, $weight ); }
				$kw_count = substr_count( $d['plain_content'], $kw );
				$ok = $kw_count <= 8 || ( $kw_count / $d['word_count'] ) < 0.03;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? __( 'Keyword usage looks natural, no stuffing detected.', 'dadsfam-seo' )
						: sprintf( __( 'Keyword appears %d times — reduce repetition to avoid a keyword-stuffing penalty.', 'dadsfam-seo' ), $kw_count )
				);
				break;

			case 'readability_sentence_len':
				if ( empty( $d['sentences'] ) ) { return $this->na_check( $id, $weight ); }
				$long  = array_filter( $d['sentences'], fn( $s ) => $this->word_count( $s ) > 20 );
				$pct   = count( $d['sentences'] ) > 0 ? count( $long ) / count( $d['sentences'] ) : 0;
				$ok    = $pct < 0.25;
				$score = $ok ? 1.0 : ( $pct < 0.5 ? 0.5 : 0.0 );
				$msg   = $ok
					? __( 'Sentence length is good — most sentences are easy to read.', 'dadsfam-seo' )
					: sprintf( __( '%.0f%% of sentences are too long. Aim to keep sentences under 20 words.', 'dadsfam-seo' ), $pct * 100 );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'readability_paragraph':
				if ( empty( $d['paragraphs'] ) ) { return $this->na_check( $id, $weight ); }
				$long  = array_filter( $d['paragraphs'], fn( $p ) => $this->word_count( $p ) > 150 );
				$ok    = count( $long ) === 0;
				$result = $this->check( $id, $weight, $ok,
					$ok
						? __( 'Paragraph length is manageable — great for readability.', 'dadsfam-seo' )
						: sprintf( _n( '%d paragraph is very long. Break it up for better readability.', '%d paragraphs are very long. Break them up.', count( $long ), 'dadsfam-seo' ), count( $long ) )
				);
				break;

			case 'readability_transition':
				if ( $d['word_count'] < 100 ) { return $this->na_check( $id, $weight ); }
				$sentence_count = count( $d['sentences'] );
				$with_transition = 0;
				foreach ( $d['sentences'] as $s ) {
					$first_words = implode( ' ', array_slice( explode( ' ', trim( $s ) ), 0, 4 ) );
					foreach ( self::TRANSITION_WORDS as $tw ) {
						if ( stripos( $first_words, $tw ) !== false ) { $with_transition++; break; }
					}
				}
				$pct  = $sentence_count > 0 ? $with_transition / $sentence_count : 0;
				$ok   = $pct >= 0.3;
				$score = $ok ? 1.0 : ( $pct >= 0.1 ? 0.5 : 0.0 );
				$msg  = $ok
					? sprintf( __( 'Good use of transition words (%.0f%% of sentences).', 'dadsfam-seo' ), $pct * 100 )
					: sprintf( __( 'Only %.0f%% of sentences use transition words. Aim for 30%% for better flow.', 'dadsfam-seo' ), $pct * 100 );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'readability_passive':
				$passive_indicators = [ ' was ', ' were ', ' been ', ' being ', ' is being ', ' are being ', ' were being ', ' has been ', ' have been ', ' will be ' ];
				$passive_count = 0;
				foreach ( $d['sentences'] as $s ) {
					foreach ( $passive_indicators as $pi ) {
						if ( stripos( $s, $pi ) !== false ) { $passive_count++; break; }
					}
				}
				$sentence_count = count( $d['sentences'] );
				$pct   = $sentence_count > 0 ? $passive_count / $sentence_count : 0;
				$ok    = $pct < 0.1;
				$score = $ok ? 1.0 : ( $pct < 0.2 ? 0.6 : 0.2 );
				$msg   = $ok
					? __( 'Great — minimal passive voice detected.', 'dadsfam-seo' )
					: sprintf( __( '%.0f%% passive voice sentences detected. Use active voice more often.', 'dadsfam-seo' ), $pct * 100 );
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			case 'readability_fleschkincaid':
				$fk   = $this->flesch_kincaid( $d['plain_content'] );
				if ( $fk >= 60 )      { $score = 1.0; $msg = sprintf( __( 'Flesch Reading Ease: %.0f — easy to read.', 'dadsfam-seo' ), $fk ); }
				elseif ( $fk >= 30 )  { $score = 0.6; $msg = sprintf( __( 'Flesch Reading Ease: %.0f — moderately difficult. Simplify sentence structure.', 'dadsfam-seo' ), $fk ); }
				else                  { $score = 0.2; $msg = sprintf( __( 'Flesch Reading Ease: %.0f — very difficult to read. Shorten sentences and use simpler words.', 'dadsfam-seo' ), $fk ); }
				$result = [ 'id' => $id, 'score' => $score * $weight, 'weight' => $weight, 'status' => $this->score_to_status( $score ), 'message' => $msg ];
				break;

			// ── GEO checks ──────────────────────────────────────────────────
			case 'geo_question_heading':
				$found = false;
				foreach ( [ $d['h2s'], $d['h3s'] ] as $nodelist ) {
					if ( ! $nodelist ) continue;
					foreach ( $nodelist as $h ) {
						$t = strtolower( trim( $h->textContent ) );
						if ( $t === '' ) continue;
						// question mark, or starts with an interrogative word
						if ( strpos( $t, '?' ) !== false
							|| preg_match( '/^(what|how|why|when|where|who|which|can|does|is|are|should|do)\b/', $t ) ) {
							$found = true; break 2;
						}
					}
				}
				$result = $this->check( $id, $weight, $found,
					$found
						? __( 'Has a question-style heading — AI answer engines love these.', 'dadsfam-seo' )
						: __( 'Add a question-style heading (e.g. "What is…?", "How do I…?"). AI engines pull answers from question-shaped sections.', 'dadsfam-seo' )
				);
				break;

			case 'geo_has_list_or_table':
				$has = false;
				if ( $d['dom'] instanceof DOMDocument ) {
					foreach ( [ 'ul', 'ol', 'table' ] as $tag ) {
						if ( $d['dom']->getElementsByTagName( $tag )->length > 0 ) { $has = true; break; }
					}
				}
				$result = $this->check( $id, $weight, $has,
					$has
						? __( 'Contains a list or table — structured data is highly citable by AI.', 'dadsfam-seo' )
						: __( 'Add a bulleted list, numbered steps, or a comparison table. AI Overviews and Perplexity frequently quote structured content.', 'dadsfam-seo' )
				);
				break;

			case 'geo_direct_answer':
				// First paragraph should be a concise, self-contained answer (≤ ~360 chars)
				$intro = trim( $d['first_para'] ?? '' );
				$len   = mb_strlen( $intro );
				if ( $len === 0 ) {
					$result = $this->check( $id, $weight, false,
						__( 'Open with a short, direct answer in the first paragraph — AI engines quote concise opening summaries.', 'dadsfam-seo' ) );
				} elseif ( $len <= 360 ) {
					$result = $this->check( $id, $weight, true,
						__( 'Opens with a concise, quotable answer — ideal for AI citations.', 'dadsfam-seo' ) );
				} else {
					$result = [ 'id' => $id, 'score' => 0.5 * $weight, 'weight' => $weight, 'status' => 'ok',
						'message' => __( 'Your opening paragraph is long. A tight 2–3 sentence summary up top is easier for AI engines to quote.', 'dadsfam-seo' ) ];
				}
				break;

			default:
				$result = $this->na_check( $id, $weight );
		}

		return $result;
	}

	// ─── Helpers ────────────────────────────────────────────────────────────

	private function check( string $id, int $weight, bool $pass, string $message ): array {
		return [
			'id'      => $id,
			'score'   => $pass ? $weight : 0,
			'weight'  => $weight,
			'status'  => $pass ? 'good' : 'bad',
			'message' => $message,
		];
	}

	private function na_check( string $id, int $weight ): array {
		return [ 'id' => $id, 'score' => $weight * 0.5, 'weight' => $weight, 'status' => 'na', 'message' => '' ];
	}

	private function score_to_status( float $score ): string {
		if ( $score >= 0.85 ) return 'good';
		if ( $score >= 0.5  ) return 'ok';
		return 'bad';
	}

	private function grade( int $score ): string {
		if ( $score >= self::SCORE_GREAT ) return 'great';
		if ( $score >= self::SCORE_OK    ) return 'ok';
		return 'poor';
	}

	private function word_count( string $text ): int {
		$text = preg_replace( '/\s+/', ' ', trim( $text ) );
		return $text ? str_word_count( $text ) : 0;
	}

	private function get_sentences( string $text ): array {
		// Split on ., !, ?
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		preg_match_all( '/[^.!?]*[.!?]+/u', $text, $matches );
		return array_filter( array_map( 'trim', $matches[0] ), fn( $s ) => strlen( $s ) > 5 );
	}

	private function get_paragraphs( string $text ): array {
		$paras = preg_split( '/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY );
		return array_filter( array_map( 'trim', $paras ?: [] ), fn( $p ) => strlen( $p ) > 20 );
	}

	private function get_first_paragraph_text( string $html ): string {
		if ( empty( $html ) ) return '';
		// Try to get first <p> contents
		if ( preg_match( '/<p[^>]*>(.*?)<\/p>/si', $html, $m ) ) {
			return wp_strip_all_tags( $m[1] );
		}
		// Fallback: first 300 chars of plain text
		return substr( wp_strip_all_tags( $html ), 0, 300 );
	}

	private function get_dom( string $html ): ?DOMDocument {
		if ( empty( $html ) ) return null;
		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<html><meta charset="utf-8"/><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		return $dom;
	}

	private function flesch_kincaid( string $text ): float {
		$words    = $this->word_count( $text );
		$sents    = count( $this->get_sentences( $text ) );
		$syllables = $this->count_syllables( $text );
		if ( $words < 5 || $sents < 1 ) return 50;
		return 206.835 - ( 1.015 * ( $words / $sents ) ) - ( 84.6 * ( $syllables / $words ) );
	}

	private function count_syllables( string $text ): int {
		$words = explode( ' ', preg_replace( '/[^a-z ]/', '', strtolower( $text ) ) );
		$total = 0;
		foreach ( $words as $w ) {
			if ( empty( $w ) ) continue;
			$w      = rtrim( $w, 'e' );
			$count  = preg_match_all( '/[aeiou]+/i', $w, $tmp );
			$total += max( 1, $count );
		}
		return $total;
	}

	private function compute_readability_score( array $sentences, string $plain ): array {
		$fk     = $this->flesch_kincaid( $plain );
		$score  = (int) min( 100, max( 0, $fk ) );
		return [ 'score' => $score, 'flesch_kincaid' => round( $fk, 1 ) ];
	}

	private function empty_result(): array {
		return [
			'has_keyword'       => false,
			'focus_keyword'     => '',
			'seo_score'         => 0,
			'seo_grade'         => 'poor',
			'readability_score' => 0,
			'readability_grade' => 'poor',
			'word_count'        => 0,
			'checks'            => [],
			'readability_data'  => [],
		];
	}

	// ─── Save score to post meta ─────────────────────────────────────────────

	public function update_post_score( int $post_id ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
		$result = $this->analyse_post( $post_id );
		update_post_meta( $post_id, '_dfseo_score',         $result['seo_score'] );
		update_post_meta( $post_id, '_dfseo_score_grade',   $result['seo_grade'] );
		update_post_meta( $post_id, '_dfseo_word_count',    $result['word_count'] );
	}
}
