<?php
/**
 * Local SEO — LocalBusiness schema fields & settings.
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DFSEO_Local_Seo {
	public function __construct() {
		// Settings registered via DFSEO_Settings. Schema output via DFSEO_Schema.
	}

	public static function get_data(): array {
		return [
			'type'    => get_option( 'dfseo_local_schema_type', 'LocalBusiness' ),
			'name'    => get_option( 'dfseo_local_name',    get_bloginfo('name') ),
			'address' => get_option( 'dfseo_local_address', '' ),
			'city'    => get_option( 'dfseo_local_city',    '' ),
			'state'   => get_option( 'dfseo_local_state',   '' ),
			'zip'     => get_option( 'dfseo_local_zip',     '' ),
			'country' => get_option( 'dfseo_local_country', 'ZA' ),
			'phone'   => get_option( 'dfseo_local_phone',   '' ),
			'email'   => get_option( 'dfseo_local_email',   '' ),
			'lat'     => get_option( 'dfseo_local_lat',     '' ),
			'lng'     => get_option( 'dfseo_local_lng',     '' ),
			'url'     => home_url(),
		];
	}
}
