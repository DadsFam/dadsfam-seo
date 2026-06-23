<?php
/**
 * DadsFam SEO — GEO (Generative Engine Optimization)
 *
 * Helps your content get discovered, understood, and cited by AI answer
 * engines: ChatGPT / OpenAI, Google AI Overviews & Gemini, Perplexity,
 * Claude, Bing Copilot, and others.
 *
 * Provides:
 *   1. llms.txt — an emerging standard (llmstxt.org) that gives AI models a
 *      clean, curated markdown map of your most important content, served at
 *      /llms.txt. Like robots.txt, but for large language models.
 *   2. AI crawler controls — allow or block individual AI bots (GPTBot,
 *      ClaudeBot, Google-Extended, PerplexityBot, CCBot, Bytespider, …) right
 *      from your robots.txt, with sensible defaults.
 *   3. Speakable schema — marks the parts of a page best suited to voice
 *      assistants and AI read-aloud.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_GEO {

	const CACHE_KEY = 'dfseo_llms_txt_cache';
	const CACHE_TTL = 12 * HOUR_IN_SECONDS;

	/**
	 * Known AI crawler user-agents.
	 * key => [ label, purpose ]
	 */
	const AI_BOTS = [
		'GPTBot'             => [ 'OpenAI GPTBot',        'Trains ChatGPT / OpenAI models' ],
		'OAI-SearchBot'      => [ 'OpenAI SearchBot',     'Powers ChatGPT Search results' ],
		'ChatGPT-User'       => [ 'ChatGPT-User',         'Fetches pages when a ChatGPT user clicks a link' ],
		'Google-Extended'    => [ 'Google-Extended',      'Trains Gemini & powers AI Overviews' ],
		'ClaudeBot'          => [ 'ClaudeBot (Anthropic)','Trains Claude models' ],
		'Claude-Web'         => [ 'Claude-Web',           'Fetches pages for Claude users' ],
		'PerplexityBot'      => [ 'PerplexityBot',        'Powers Perplexity answer engine' ],
		'Applebot-Extended'  => [ 'Applebot-Extended',    'Trains Apple Intelligence' ],
		'Amazonbot'          => [ 'Amazonbot',            'Powers Alexa & Amazon AI' ],
		'CCBot'              => [ 'CCBot (Common Crawl)',  'Open dataset used to train many AI models' ],
		'Bytespider'         => [ 'Bytespider (TikTok)',  'Trains ByteDance / TikTok AI' ],
		'cohere-ai'          => [ 'Cohere AI',            'Trains Cohere models' ],
		'Meta-ExternalAgent' => [ 'Meta AI',              'Trains Meta (Llama) models' ],
	];

	public function __construct() {
		add_action( 'init',              [ $this, 'register_rewrite' ] );
		add_action( 'template_redirect', [ $this, 'maybe_serve_llms_txt' ] );
		add_filter( 'robots_txt',        [ $this, 'add_ai_directives' ], 30, 2 );

		// Refresh the llms.txt cache when content changes
		add_action( 'save_post',    [ $this, 'bust_cache' ] );
		add_action( 'deleted_post', [ $this, 'bust_cache' ] );
	}

	// ─── llms.txt ─────────────────────────────────────────────────────────────

	public function register_rewrite(): void {
		add_rewrite_rule( '^llms\.txt$', 'index.php?dfseo_llms=1', 'top' );
		add_rewrite_tag( '%dfseo_llms%', '([0-1])' );
	}

	public function bust_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	public function maybe_serve_llms_txt(): void {
		if ( (string) get_query_var( 'dfseo_llms' ) !== '1' ) return;
		if ( get_option( 'dfseo_geo_llms_enable', '1' ) !== '1' ) {
			status_header( 404 );
			exit;
		}

		header( 'Content-Type: text/markdown; charset=' . get_bloginfo( 'charset' ) );
		header( 'X-Robots-Tag: noindex, follow', true );

		$cached = get_transient( self::CACHE_KEY );
		if ( is_string( $cached ) && $cached !== '' ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — generated markdown
			echo $cached;
			exit;
		}

		$out = $this->build_llms_txt();
		set_transient( self::CACHE_KEY, $out, self::CACHE_TTL );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — generated markdown
		echo $out;
		exit;
	}

	/**
	 * Build the llms.txt markdown document.
	 */
	private function build_llms_txt(): string {
		$site_name = get_bloginfo( 'name' );
		$tagline   = get_bloginfo( 'description' );
		$home      = home_url( '/' );

		// Optional owner-supplied summary
		$summary = trim( (string) get_option( 'dfseo_geo_llms_summary', '' ) );

		$lines   = [];
		$lines[] = '# ' . $site_name;
		$lines[] = '';
		if ( $tagline ) {
			$lines[] = '> ' . $tagline;
			$lines[] = '';
		}
		if ( $summary ) {
			$lines[] = $summary;
			$lines[] = '';
		}
		$lines[] = sprintf( 'Website: %s', $home );
		$lines[] = '';

		// ── Key pages ────────────────────────────────────────────────────────
		$pages = get_posts( [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		] );
		if ( $pages ) {
			$lines[] = '## Key Pages';
			$lines[] = '';
			foreach ( $pages as $p ) {
				if ( get_post_meta( $p->ID, '_dfseo_robots_noindex', true ) === '1' ) continue;
				$lines[] = $this->llms_line( $p );
			}
			$lines[] = '';
		}

		// ── Recent posts (and any public CPTs) ─────────────────────────────────
		$cpts = array_values( get_post_types( [ 'public' => true, '_builtin' => false ] ) );
		$types = array_merge( [ 'post' ], $cpts );

		$posts = get_posts( [
			'post_type'      => $types,
			'post_status'    => 'publish',
			'posts_per_page' => (int) get_option( 'dfseo_geo_llms_post_count', 50 ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		] );
		if ( $posts ) {
			$lines[] = '## Articles';
			$lines[] = '';
			foreach ( $posts as $p ) {
				if ( get_post_meta( $p->ID, '_dfseo_robots_noindex', true ) === '1' ) continue;
				$lines[] = $this->llms_line( $p );
			}
			$lines[] = '';
		}

		$lines[] = '---';
		$lines[] = sprintf( 'Generated by DadsFam SEO · Sitemap: %s', home_url( '/sitemap.xml' ) );

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * One markdown link line: - [Title](url): description
	 */
	private function llms_line( WP_Post $p ): string {
		$title = get_the_title( $p );
		$url   = get_permalink( $p );

		// Prefer the SEO meta description, fall back to the excerpt
		$desc = (string) get_post_meta( $p->ID, '_dfseo_meta_desc', true );
		if ( $desc === '' ) {
			$desc = has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( wp_strip_all_tags( (string) $p->post_content ), 25, '…' );
		}
		$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $desc ) ) );

		$line = sprintf( '- [%s](%s)', $title, $url );
		if ( $desc !== '' ) {
			$line .= ': ' . $desc;
		}
		return $line;
	}

	// ─── AI crawler controls in robots.txt ─────────────────────────────────────

	public function add_ai_directives( string $output, bool $public ): string {
		if ( ! $public ) return $output;                          // whole site blocked already
		if ( get_option( 'dfseo_geo_ai_control', '1' ) !== '1' ) return $output;

		$mode    = (string) get_option( 'dfseo_geo_ai_mode', 'allow' ); // allow | block | custom
		$blocked = (array) get_option( 'dfseo_geo_ai_blocked', [] );

		$rules = "\n# AI crawler directives — managed by DadsFam SEO (GEO)\n";

		if ( $mode === 'allow' ) {
			// Explicitly welcome AI crawlers (helps you get cited in AI answers)
			$rules .= "# All major AI crawlers are allowed to read this site.\n";
			foreach ( array_keys( self::AI_BOTS ) as $bot ) {
				$rules .= "User-agent: {$bot}\nAllow: /\n\n";
			}
		} elseif ( $mode === 'block' ) {
			$rules .= "# All major AI crawlers are blocked from this site.\n";
			foreach ( array_keys( self::AI_BOTS ) as $bot ) {
				$rules .= "User-agent: {$bot}\nDisallow: /\n\n";
			}
		} else { // custom — block only the selected bots, allow the rest
			foreach ( self::AI_BOTS as $bot => $_meta ) {
				$rules .= "User-agent: {$bot}\n";
				$rules .= in_array( $bot, $blocked, true ) ? "Disallow: /\n\n" : "Allow: /\n\n";
			}
		}

		// Advertise llms.txt to agents that look for it
		if ( get_option( 'dfseo_geo_llms_enable', '1' ) === '1' ) {
			$rules .= '# LLM content map: ' . home_url( '/llms.txt' ) . "\n";
		}

		return $output . $rules;
	}

	// ─── Speakable schema fragment (used by the schema module) ──────────────────

	/**
	 * Returns a SpeakableSpecification node for the current singular post,
	 * or null if disabled. Targets the title and meta description / first para.
	 */
	public static function speakable_node(): ?array {
		if ( get_option( 'dfseo_geo_speakable', '1' ) !== '1' ) return null;
		if ( ! is_singular() ) return null;

		return [
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => [ 'h1', '.entry-content p:first-of-type' ],
		];
	}
}
