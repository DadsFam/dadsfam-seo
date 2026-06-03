<?php
/**
 * AI Content Tools — powered by Claude API (Anthropic).
 *
 * Premium feature. Generates SEO titles, meta descriptions, keyword ideas,
 * content improvement suggestions, FAQ schema blocks, and more.
 *
 * API key is stored server-side only and calls are proxied here.
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_AI {

	const CLAUDE_API_URL  = 'https://api.anthropic.com/v1/messages';
	const CLAUDE_MODEL    = 'claude-sonnet-4-6';
	const MAX_TOKENS      = 1500;
	const ANTHROPIC_VER   = '2023-06-01';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes(): void {
		$routes = [
			'generate-meta'          => 'rest_generate_meta',
			'suggest-keywords'        => 'rest_suggest_keywords',
			'optimize-content'        => 'rest_optimize_content',
			'generate-faq'            => 'rest_generate_faq',
			'generate-title-variants' => 'rest_title_variants',
			'content-outline'         => 'rest_content_outline',
		];
		foreach ( $routes as $endpoint => $callback ) {
			register_rest_route( 'dfseo/v1/ai', '/' . $endpoint, [
				'methods'             => 'POST',
				'callback'            => [ $this, $callback ],
				'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
			] );
		}
	}

	// ─── Permission check ─────────────────────────────────────────────────

	private function check_premium(): ?WP_REST_Response {
		if ( ! dfseo_is_premium() ) {
			return new WP_REST_Response( [
				'error'   => 'premium_required',
				'message' => __( 'AI Tools require a DadsFam SEO Premium licence.', 'dadsfam-seo' ),
			], 403 );
		}
		$key = (string) get_option( 'dfseo_ai_api_key', '' );
		if ( empty( $key ) ) {
			return new WP_REST_Response( [
				'error'   => 'no_api_key',
				'message' => __( 'Please add your Anthropic API key in DadsFam SEO → Settings → AI Tools.', 'dadsfam-seo' ),
			], 400 );
		}
		return null;
	}

	// ─── Generate meta title + description ──────────────────────────────────

	public function rest_generate_meta( WP_REST_Request $request ): WP_REST_Response {
		if ( $err = $this->check_premium() ) return $err;

		$post_id = (int)    ( $request['post_id']       ?? 0 );
		$keyword = (string) ( $request['focus_keyword'] ?? '' );
		$content = wp_strip_all_tags( (string) ( $request['content'] ?? '' ) );
		$content = wp_trim_words( $content, 300 );

		$post_type = get_post_type( $post_id );

		$prompt = "You are an expert SEO copywriter. Generate an SEO-optimised title and meta description for the following WordPress {$post_type}.\n\n";
		if ( $keyword ) $prompt .= "Focus Keyword: {$keyword}\n\n";
		$prompt .= "Content excerpt:\n{$content}\n\n";
		$prompt .= "Requirements:\n";
		$prompt .= "- Title: 50–60 characters, naturally include the focus keyword near the start, compelling and click-worthy\n";
		$prompt .= "- Meta description: 120–160 characters, include focus keyword, has a clear value proposition and soft CTA\n";
		$prompt .= "- Write in active voice, no keyword stuffing\n\n";
		$prompt .= "Respond ONLY in valid JSON: {\"title\": \"...\", \"description\": \"...\"}";

		$response = $this->claude_call( $prompt );
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
		}

		$data = json_decode( $response, true );
		if ( ! is_array( $data ) ) {
			return new WP_REST_Response( [ 'error' => 'Invalid AI response', 'raw' => $response ], 500 );
		}

		return new WP_REST_Response( [
			'title'       => sanitize_text_field( $data['title'] ?? '' ),
			'description' => sanitize_textarea_field( $data['description'] ?? '' ),
		], 200 );
	}

	// ─── Keyword suggestions ─────────────────────────────────────────────────

	public function rest_suggest_keywords( WP_REST_Request $request ): WP_REST_Response {
		if ( $err = $this->check_premium() ) return $err;

		$topic   = sanitize_text_field( (string) ( $request['topic'] ?? '' ) );
		$content = wp_trim_words( wp_strip_all_tags( (string) ( $request['content'] ?? '' ) ), 200 );

		$prompt  = "You are an expert SEO keyword researcher.\n\n";
		$prompt .= "Topic/Content: {$topic}\n{$content}\n\n";
		$prompt .= "Generate 10 relevant SEO keyword suggestions, including:\n";
		$prompt .= "- 3 primary keywords (high volume, topic-relevant)\n";
		$prompt .= "- 4 long-tail variations (more specific, easier to rank)\n";
		$prompt .= "- 3 semantic/related keywords (LSI keywords)\n\n";
		$prompt .= "For each keyword estimate: intent (informational/transactional/navigational), difficulty (easy/medium/hard).\n";
		$prompt .= "Respond ONLY in valid JSON array: [{\"keyword\":\"...\",\"type\":\"primary|longtail|semantic\",\"intent\":\"...\",\"difficulty\":\"...\"}]";

		$response = $this->claude_call( $prompt );
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
		}

		$data = json_decode( $response, true );
		if ( ! is_array( $data ) ) {
			return new WP_REST_Response( [ 'error' => 'Invalid AI response', 'raw' => $response ], 500 );
		}

		return new WP_REST_Response( [ 'keywords' => $data ], 200 );
	}

	// ─── Content optimisation suggestions ───────────────────────────────────

	public function rest_optimize_content( WP_REST_Request $request ): WP_REST_Response {
		if ( $err = $this->check_premium() ) return $err;

		$keyword  = sanitize_text_field( (string) ( $request['focus_keyword'] ?? '' ) );
		$content  = wp_strip_all_tags( (string) ( $request['content'] ?? '' ) );
		$analysis = $request['analysis'] ?? [];

		$prompt  = "You are an expert SEO content strategist. Review this WordPress content and provide specific, actionable improvement recommendations.\n\n";
		if ( $keyword ) $prompt .= "Focus Keyword: {$keyword}\n\n";
		$prompt .= "Content (first 800 words):\n" . wp_trim_words( $content, 200 ) . "\n\n";

		if ( ! empty( $analysis ) ) {
			$prompt .= "Current SEO analysis score: " . ( $analysis['seo_score'] ?? 'unknown' ) . "/100\n\n";
		}

		$prompt .= "Provide 5-8 specific, actionable recommendations to improve SEO. Each should be concrete, not generic.\n";
		$prompt .= "Focus on: missing keywords, thin content areas, heading structure, internal linking opportunities, readability improvements.\n\n";
		$prompt .= "Respond ONLY in valid JSON: {\"recommendations\":[{\"priority\":\"high|medium|low\",\"category\":\"content|keyword|technical|readability\",\"title\":\"...\",\"description\":\"...\",\"how_to_fix\":\"...\"}]}";

		$response = $this->claude_call( $prompt, 2000 );
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
		}

		$data = json_decode( $response, true );
		return new WP_REST_Response( $data ?: [ 'error' => 'Invalid response' ], 200 );
	}

	// ─── FAQ schema generator ────────────────────────────────────────────────

	public function rest_generate_faq( WP_REST_Request $request ): WP_REST_Response {
		if ( $err = $this->check_premium() ) return $err;

		$topic   = sanitize_text_field( (string) ( $request['topic'] ?? '' ) );
		$count   = min( 10, max( 3, (int) ( $request['count'] ?? 5 ) ) );

		$prompt  = "Generate {$count} FAQs (frequently asked questions) for an article about: {$topic}\n\n";
		$prompt .= "Requirements:\n";
		$prompt .= "- Questions should be what people actually Google (start with Who/What/When/Where/Why/How/Can/Is/Does)\n";
		$prompt .= "- Answers should be 2-4 sentences, accurate, and helpful\n";
		$prompt .= "- Avoid repetition between questions\n\n";
		$prompt .= "Respond ONLY in valid JSON: [{\"question\":\"...\",\"answer\":\"...\"}]";

		$response = $this->claude_call( $prompt );
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
		}

		$data = json_decode( $response, true );
		return new WP_REST_Response( [ 'faqs' => $data ?: [] ], 200 );
	}

	// ─── Title variants ──────────────────────────────────────────────────────

	public function rest_title_variants( WP_REST_Request $request ): WP_REST_Response {
		if ( $err = $this->check_premium() ) return $err;

		$current = sanitize_text_field( (string) ( $request['current_title'] ?? '' ) );
		$keyword = sanitize_text_field( (string) ( $request['focus_keyword'] ?? '' ) );

		$prompt  = "Generate 5 alternative SEO title variants for the following title.\n\n";
		$prompt .= "Current title: {$current}\n";
		if ( $keyword ) $prompt .= "Focus keyword: {$keyword}\n\n";
		$prompt .= "Rules: Each title must be 50-60 characters, include the focus keyword, be compelling and click-worthy.\n";
		$prompt .= "Use different formulas: How-to, List (\"X ways to...\"), Question, Direct benefit, curiosity gap.\n\n";
		$prompt .= "Respond ONLY in valid JSON array of strings: [\"title1\",\"title2\",...]";

		$response = $this->claude_call( $prompt );
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
		}

		$data = json_decode( $response, true );
		return new WP_REST_Response( [ 'variants' => $data ?: [] ], 200 );
	}

	// ─── Content outline ─────────────────────────────────────────────────────

	public function rest_content_outline( WP_REST_Request $request ): WP_REST_Response {
		if ( $err = $this->check_premium() ) return $err;

		$topic     = sanitize_text_field( (string) ( $request['topic'] ?? '' ) );
		$keyword   = sanitize_text_field( (string) ( $request['focus_keyword'] ?? '' ) );
		$word_goal = (int) ( $request['word_count'] ?? 1500 );

		$prompt  = "Create a comprehensive SEO content outline for: {$topic}\n";
		if ( $keyword ) $prompt .= "Focus keyword: {$keyword}\n";
		$prompt .= "Target word count: {$word_goal} words\n\n";
		$prompt .= "Include: H1 title, introduction notes, H2 sections with H3 subsections, key points per section, conclusion, FAQs.\n";
		$prompt .= "Each section should note the sub-keywords to weave in naturally.\n\n";
		$prompt .= "Respond ONLY in valid JSON: {\"h1\":\"...\",\"intro\":\"...\",\"sections\":[{\"h2\":\"...\",\"subsections\":[{\"h3\":\"...\",\"key_points\":[\"...\"],\"keywords\":[\"...\"]}],\"estimated_words\":100}],\"conclusion\":\"...\",\"faqs\":[{\"q\":\"...\"}]}";

		$response = $this->claude_call( $prompt, 2500 );
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response( [ 'error' => $response->get_error_message() ], 500 );
		}

		$data = json_decode( $response, true );
		return new WP_REST_Response( $data ?: [ 'error' => 'Invalid response' ], 200 );
	}

	// ─── Core API call ───────────────────────────────────────────────────────

	/**
	 * Call the Anthropic Claude API.
	 *
	 * @param string $prompt     User prompt.
	 * @param int    $max_tokens Override default max tokens.
	 * @return string|WP_Error   Text content or error.
	 */
	private function claude_call( string $prompt, int $max_tokens = self::MAX_TOKENS ): string|WP_Error {
		$api_key = (string) get_option( 'dfseo_ai_api_key', '' );
		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', __( 'Anthropic API key not configured.', 'dadsfam-seo' ) );
		}

		$model = (string) get_option( 'dfseo_ai_model', self::CLAUDE_MODEL );

		$response = wp_remote_post( self::CLAUDE_API_URL, [
			'timeout' => 45,
			'headers' => [
				'Content-Type'      => 'application/json',
				'x-api-key'         => $api_key,
				'anthropic-version' => self::ANTHROPIC_VER,
			],
			'body' => wp_json_encode( [
				'model'      => $model,
				'max_tokens' => $max_tokens,
				'messages'   => [
					[ 'role' => 'user', 'content' => $prompt ],
				],
			] ),
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code !== 200 ) {
			$msg = $body['error']['message'] ?? "API error {$code}";
			return new WP_Error( 'api_error', $msg );
		}

		// Extract text from response
		$text = '';
		foreach ( $body['content'] ?? [] as $block ) {
			if ( ( $block['type'] ?? '' ) === 'text' ) {
				$text .= $block['text'];
			}
		}

		// Strip markdown code fences anywhere in the response
		$text = preg_replace( '/```(?:json)?\s*/i', '', $text );
		$text = str_replace( '```', '', $text );
		$text = trim( $text );

		// If there's preamble/trailing prose around the JSON, extract just the
		// JSON object or array so json_decode() succeeds reliably.
		if ( $text !== '' && $text[0] !== '{' && $text[0] !== '[' ) {
			$first_obj = strpos( $text, '{' );
			$first_arr = strpos( $text, '[' );
			$candidates = array_filter( [ $first_obj, $first_arr ], fn( $v ) => $v !== false );
			if ( ! empty( $candidates ) ) {
				$start = min( $candidates );
				$open  = $text[ $start ];
				$close = $open === '{' ? '}' : ']';
				$end   = strrpos( $text, $close );
				if ( $end !== false && $end > $start ) {
					$text = substr( $text, $start, $end - $start + 1 );
				}
			}
		}

		return trim( $text );
	}
}
