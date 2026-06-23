<?php
/**
 * JSON-LD Structured Data (Schema.org).
 *
 * Free:    Article, WebPage, BreadcrumbList, Organization, WebSite, SearchAction
 * Premium: FAQPage, HowTo, Recipe, VideoObject, LocalBusiness, Product, Review, Event
 *
 * @package DadsFam_SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DFSEO_Schema {

	public function __construct() {
		add_action( 'wp_head', [ $this, 'output_schema' ], 5 );
	}

	public function output_schema(): void {
		$schemas = array_filter( [
			$this->website_schema(),
			$this->organization_schema(),
			$this->webpage_schema(),
			$this->post_schema(),
			$this->breadcrumb_schema(),
			// Premium
			dfseo_is_premium() ? $this->faq_schema()    : null,
			dfseo_is_premium() ? $this->howto_schema()  : null,
			dfseo_is_premium() ? $this->video_schema()  : null,
			dfseo_is_premium() ? $this->product_schema(): null,
			dfseo_is_premium() ? $this->event_schema()  : null,
			dfseo_is_premium() ? $this->local_business_schema() : null,
		] );

		foreach ( $schemas as $schema ) {
			if ( ! empty( $schema ) ) {
				echo '<script type="application/ld+json">' . "\n";
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
				echo "\n</script>\n";
			}
		}
	}

	// ─── WebSite ────────────────────────────────────────────────────────────

	private function website_schema(): ?array {
		if ( ! is_front_page() && ! is_home() ) return null;
		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
		];
		// Sitelinks SearchBox
		if ( get_option( 'dfseo_schema_search_box', '1' ) === '1' ) {
			$schema['potentialAction'] = [
				'@type'       => 'SearchAction',
				'target'      => [
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				],
				'query-input' => 'required name=search_term_string',
			];
		}
		return $schema;
	}

	// ─── Organization ───────────────────────────────────────────────────────

	private function organization_schema(): ?array {
		if ( ! is_front_page() ) return null;
		$type = (string) get_option( 'dfseo_schema_org_type', 'Organization' );
		$name = (string) get_option( 'dfseo_schema_org_name', get_bloginfo( 'name' ) );
		$logo = (string) get_option( 'dfseo_schema_org_logo', '' );

		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => $type,
			'@id'      => home_url( '/#organization' ),
			'name'     => $name,
			'url'      => home_url( '/' ),
		];
		if ( $logo ) {
			$schema['logo'] = [
				'@type' => 'ImageObject',
				'url'   => $logo,
			];
		}
		// Social profiles
		$profiles = array_filter( [
			get_option( 'dfseo_social_facebook',  '' ),
			get_option( 'dfseo_social_twitter',   '' ),
			get_option( 'dfseo_social_instagram', '' ),
			get_option( 'dfseo_social_linkedin',  '' ),
			get_option( 'dfseo_social_youtube',   '' ),
			get_option( 'dfseo_social_tiktok',    '' ),
			get_option( 'dfseo_social_pinterest', '' ),
			get_option( 'dfseo_social_threads',   '' ),
			get_option( 'dfseo_social_whatsapp',  '' ),
			get_option( 'dfseo_social_mastodon',  '' ),
			get_option( 'dfseo_social_github',    '' ),
			get_option( 'dfseo_social_telegram',  '' ),
		] );
		// Custom additional profiles (one URL per line)
		$custom_raw = (string) get_option( 'dfseo_social_custom', '' );
		foreach ( array_filter( array_map( 'trim', explode( "\n", $custom_raw ) ) ) as $url ) {
			if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$profiles[] = $url;
			}
		}
		if ( $profiles ) $schema['sameAs'] = array_values( $profiles );

		return $schema;
	}

	// ─── WebPage ────────────────────────────────────────────────────────────

	private function webpage_schema(): ?array {
		global $post;
		if ( ! is_singular() ) return null;

		$meta  = dfseo()->meta;
		$type  = (string) get_post_meta( $post->ID, '_dfseo_schema_type', true ) ?: 'WebPage';
		$title = $meta->get_page_title();
		$desc  = $meta->get_meta_description();

		$speakable = class_exists( 'DFSEO_GEO' ) ? DFSEO_GEO::speakable_node() : null;

		return array_filter( [
			'@context'        => 'https://schema.org',
			'@type'           => $type,
			'@id'             => get_permalink( $post->ID ) . '#webpage',
			'url'             => get_permalink( $post->ID ),
			'name'            => $title,
			'description'     => $desc,
			'isPartOf'        => [ '@id' => home_url( '/#website' ) ],
			'datePublished'   => mysql2date( 'c', $post->post_date_gmt, false ),
			'dateModified'    => mysql2date( 'c', $post->post_modified_gmt, false ),
			'inLanguage'      => get_locale(),
			'speakable'       => $speakable,
		] );
	}

	// ─── Article ────────────────────────────────────────────────────────────

	private function post_schema(): ?array {
		global $post;
		if ( ! is_singular( 'post' ) ) return null;

		$img_url = '';
		if ( has_post_thumbnail() ) {
			$img_url = get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '';
		}

		$author_id = (int) $post->post_author;
		$schema    = array_filter( [
			'@context'        => 'https://schema.org',
			'@type'           => 'Article',
			'@id'             => get_permalink( $post->ID ) . '#article',
			'headline'        => get_the_title( $post->ID ),
			'description'     => dfseo()->meta->get_meta_description(),
			'datePublished'   => mysql2date( 'c', $post->post_date_gmt, false ),
			'dateModified'    => mysql2date( 'c', $post->post_modified_gmt, false ),
			'author'          => [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $author_id ),
				'url'   => get_author_posts_url( $author_id ),
			],
			'publisher'       => [
				'@type' => 'Organization',
				'@id'   => home_url( '/#organization' ),
				'name'  => get_bloginfo( 'name' ),
			],
			'mainEntityOfPage'=> [ '@id' => get_permalink( $post->ID ) . '#webpage' ],
			'image'           => $img_url ? [ '@type' => 'ImageObject', 'url' => $img_url ] : null,
			'wordCount'       => (int) get_post_meta( $post->ID, '_dfseo_word_count', true ) ?: null,
		] );

		return $schema;
	}

	// ─── BreadcrumbList ──────────────────────────────────────────────────────

	private function breadcrumb_schema(): ?array {
		if ( get_option( 'dfseo_breadcrumbs_enable', '1' ) !== '1' ) return null;
		$items = dfseo()->breadcrumbs->get_breadcrumb_items();
		if ( count( $items ) < 2 ) return null;

		$list = [];
		foreach ( $items as $i => $item ) {
			$entry = [
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $item['name'],
			];
			if ( $item['url'] ) $entry['item'] = $item['url'];
			$list[] = $entry;
		}

		return [
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $list,
		];
	}

	// ─── Premium schemas ──────────────────────────────────────────────────

	private function faq_schema(): ?array {
		global $post;
		if ( ! is_singular() ) return null;
		$faqs = get_post_meta( $post->ID, '_dfseo_faq_items', true );
		if ( empty( $faqs ) || ! is_array( $faqs ) ) return null;

		$entities = [];
		foreach ( $faqs as $faq ) {
			if ( empty( $faq['q'] ) || empty( $faq['a'] ) ) continue;
			$entities[] = [
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $faq['q'] ),
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text'  => wp_kses_post( $faq['a'] ),
				],
			];
		}
		if ( empty( $entities ) ) return null;

		return [
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		];
	}

	private function howto_schema(): ?array {
		global $post;
		if ( ! is_singular() ) return null;
		$data = get_post_meta( $post->ID, '_dfseo_howto', true );
		if ( empty( $data ) || ! is_array( $data ) ) return null;

		$steps = [];
		foreach ( $data['steps'] ?? [] as $step ) {
			if ( empty( $step['name'] ) ) continue;
			$s = [ '@type' => 'HowToStep', 'name' => $step['name'] ];
			if ( ! empty( $step['text'] ) )  $s['text']  = $step['text'];
			if ( ! empty( $step['image'] ) ) $s['image'] = [ '@type' => 'ImageObject', 'url' => $step['image'] ];
			$steps[] = $s;
		}
		if ( empty( $steps ) ) return null;

		$schema = [
			'@context'  => 'https://schema.org',
			'@type'     => 'HowTo',
			'name'      => $data['name'] ?? get_the_title(),
			'step'      => $steps,
		];
		if ( ! empty( $data['description'] ) ) $schema['description'] = $data['description'];
		if ( ! empty( $data['total_time'] ) )  $schema['totalTime']   = 'PT' . (int) $data['total_time'] . 'M';
		return $schema;
	}

	private function video_schema(): ?array {
		global $post;
		if ( ! is_singular() ) return null;
		$data = get_post_meta( $post->ID, '_dfseo_video', true );
		if ( empty( $data ) || empty( $data['url'] ) ) return null;

		return array_filter( [
			'@context'     => 'https://schema.org',
			'@type'        => 'VideoObject',
			'name'         => $data['name'] ?? get_the_title(),
			'description'  => $data['description'] ?? '',
			'contentUrl'   => $data['url'],
			'thumbnailUrl' => $data['thumbnail'] ?? '',
			'uploadDate'   => $data['upload_date'] ?? mysql2date( 'c', $post->post_date_gmt, false ),
			'duration'     => ! empty( $data['duration'] ) ? 'PT' . $data['duration'] : null,
		] );
	}

	private function product_schema(): ?array {
		global $post;
		if ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) return null;
		$product = wc_get_product( $post->ID );
		if ( ! $product ) return null;

		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => 'Product',
			'name'     => $product->get_name(),
			'sku'      => $product->get_sku(),
			'url'      => get_permalink( $post->ID ),
		];
		$img = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( $img ) $schema['image'] = $img;
		if ( $product->get_description() ) $schema['description'] = wp_strip_all_tags( $product->get_description() );

		$schema['offers'] = [
			'@type'         => 'Offer',
			'price'         => $product->get_price(),
			'priceCurrency' => get_woocommerce_currency(),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink( $post->ID ),
		];

		return $schema;
	}

	private function event_schema(): ?array {
		global $post;
		if ( ! is_singular() ) return null;
		$data = get_post_meta( $post->ID, '_dfseo_event', true );
		if ( empty( $data ) || empty( $data['name'] ) ) return null;

		$schema = array_filter( [
			'@context'    => 'https://schema.org',
			'@type'       => 'Event',
			'name'        => $data['name'],
			'startDate'   => $data['start_date'] ?? '',
			'endDate'     => $data['end_date'] ?? null,
			'description' => $data['description'] ?? null,
			'eventStatus' => 'https://schema.org/EventScheduled',
			'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		] );
		if ( ! empty( $data['location'] ) ) {
			$schema['location'] = [ '@type' => 'Place', 'name' => $data['location'] ];
		}
		if ( ! empty( $data['organizer'] ) ) {
			$schema['organizer'] = [ '@type' => 'Organization', 'name' => $data['organizer'] ];
		}
		return $schema;
	}

	private function local_business_schema(): ?array {
		if ( ! is_front_page() && ! is_page() ) return null;
		if ( get_option( 'dfseo_schema_org_type', 'Organization' ) === 'Organization' ) return null;

		$data = [
			'type'     => (string) get_option( 'dfseo_local_schema_type',   'LocalBusiness' ),
			'name'     => (string) get_option( 'dfseo_local_name',          get_bloginfo( 'name' ) ),
			'address'  => (string) get_option( 'dfseo_local_address',       '' ),
			'city'     => (string) get_option( 'dfseo_local_city',          '' ),
			'state'    => (string) get_option( 'dfseo_local_state',         '' ),
			'zip'      => (string) get_option( 'dfseo_local_zip',           '' ),
			'country'  => (string) get_option( 'dfseo_local_country',       '' ),
			'phone'    => (string) get_option( 'dfseo_local_phone',         '' ),
			'email'    => (string) get_option( 'dfseo_local_email',         '' ),
			'lat'      => (string) get_option( 'dfseo_local_lat',           '' ),
			'lng'      => (string) get_option( 'dfseo_local_lng',           '' ),
			'hours'    => (array)  get_option( 'dfseo_local_opening_hours', [] ),
		];

		$schema = array_filter( [
			'@context' => 'https://schema.org',
			'@type'    => $data['type'],
			'name'     => $data['name'],
			'url'      => home_url( '/' ),
			'telephone'=> $data['phone'] ?: null,
			'email'    => $data['email'] ?: null,
			'address'  => $data['address'] ? [
				'@type'           => 'PostalAddress',
				'streetAddress'   => $data['address'],
				'addressLocality' => $data['city'],
				'addressRegion'   => $data['state'],
				'postalCode'      => $data['zip'],
				'addressCountry'  => $data['country'],
			] : null,
		] );

		if ( $data['lat'] && $data['lng'] ) {
			$schema['geo'] = [ '@type' => 'GeoCoordinates', 'latitude' => $data['lat'], 'longitude' => $data['lng'] ];
		}
		if ( ! empty( $data['hours'] ) ) {
			$schema['openingHoursSpecification'] = $data['hours'];
		}
		return $schema;
	}
}
