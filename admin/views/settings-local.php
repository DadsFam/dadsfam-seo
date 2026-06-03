<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( ! dfseo_is_premium() ) { DFSEO_Core::premium_overlay( 'Local SEO' ); return; } ?>
<div class="dfseo-settings-section">
	<h2>📍 <?php esc_html_e( 'Local Business Information', 'dadsfam-seo' ); ?></h2>
	<p class="dfseo-hint">💡 <?php esc_html_e( 'If you have a physical location, filling this in helps Google show your business in local "near me" searches and on Google Maps. This data is added to your site as LocalBusiness structured data — Google reads it automatically.', 'dadsfam-seo' ); ?></p>

	<table class="dfseo-settings-table">
		<tr>
			<th><label for="dfseo_local_schema_type"><?php esc_html_e( 'Business Type', 'dadsfam-seo' ); ?></label></th>
			<td>
				<select name="dfseo_local_schema_type" id="dfseo_local_schema_type" class="dfseo-select">
					<?php foreach ( [ 'LocalBusiness' => '🏢 '.__('Local Business (general)','dadsfam-seo'), 'Restaurant' => '🍽️ '.__('Restaurant','dadsfam-seo'), 'Store' => '🛒 '.__('Store / Shop','dadsfam-seo'), 'MedicalBusiness' => '🏥 '.__('Medical / Health','dadsfam-seo'), 'HealthAndBeautyBusiness' => '💅 '.__('Health & Beauty','dadsfam-seo'), 'LegalService' => '⚖️ '.__('Legal Service','dadsfam-seo'), 'AccountingService' => '🧾 '.__('Accounting / Finance','dadsfam-seo'), 'AutoDealer' => '🚗 '.__('Auto Dealer','dadsfam-seo'), 'Hotel' => '🏨 '.__('Hotel / Accommodation','dadsfam-seo'), 'FoodEstablishment' => '🍕 '.__('Food & Beverage','dadsfam-seo'), 'HomeAndConstructionBusiness' => '🔨 '.__('Home & Construction','dadsfam-seo'), 'ProfessionalService' => '💼 '.__('Professional Service','dadsfam-seo') ] as $t => $l ) : ?>
						<option value="<?php echo esc_attr( $t ); ?>" <?php selected( get_option( 'dfseo_local_schema_type', 'LocalBusiness' ), $t ); ?>><?php echo esc_html( $l ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="dfseo-hint">💡 <?php esc_html_e( 'Pick the most specific type that matches your business. The more specific, the better Google can categorise you in local search.', 'dadsfam-seo' ); ?></p>
			</td>
		</tr>
		<?php
		$local_fields = [
			'dfseo_local_name'    => [ __( 'Business Name', 'dadsfam-seo' ),   'text',  'DadsFam', __( 'Your official business name exactly as registered. Must match your Google Business Profile.', 'dadsfam-seo' ) ],
			'dfseo_local_address' => [ __( 'Street Address', 'dadsfam-seo' ),  'text',  '123 Main St', __( 'Your street address. Include building number and street name.', 'dadsfam-seo' ) ],
			'dfseo_local_city'    => [ __( 'City', 'dadsfam-seo' ),            'text',  'Cape Town', '' ],
			'dfseo_local_state'   => [ __( 'Province / State', 'dadsfam-seo' ),'text',  'Western Cape', '' ],
			'dfseo_local_zip'     => [ __( 'Postal Code', 'dadsfam-seo' ),     'text',  '8001', '' ],
			'dfseo_local_country' => [ __( 'Country Code', 'dadsfam-seo' ),    'text',  'ZA', __( 'Two-letter ISO country code. e.g. ZA for South Africa, US for United States, GB for United Kingdom.', 'dadsfam-seo' ) ],
			'dfseo_local_phone'   => [ __( 'Phone Number', 'dadsfam-seo' ),    'tel',   '+27 xx xxx xxxx', __( 'Include the country code. e.g. +27 21 555 0000', 'dadsfam-seo' ) ],
			'dfseo_local_email'   => [ __( 'Email Address', 'dadsfam-seo' ),   'email', 'hello@yoursite.co.za', '' ],
			'dfseo_local_lat'     => [ __( 'Latitude', 'dadsfam-seo' ),        'text',  '-33.9249', __( 'Find your exact coordinates on Google Maps: right-click your location → "What\'s here?" The first number is latitude.', 'dadsfam-seo' ) ],
			'dfseo_local_lng'     => [ __( 'Longitude', 'dadsfam-seo' ),       'text',  '18.4241',  __( 'The second number from Google Maps is longitude.', 'dadsfam-seo' ) ],
		];
		foreach ( $local_fields as $key => [$label, $type, $placeholder, $tip] ) : ?>
		<tr>
			<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
					value="<?php echo esc_attr( get_option( $key ) ); ?>" class="dfseo-input" placeholder="<?php echo esc_attr( $placeholder ); ?>">
				<?php if ( $tip ) : ?><p class="dfseo-hint">💡 <?php echo esc_html( $tip ); ?></p><?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
