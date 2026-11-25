<?php

/**
 * Regression tests for map info_window entity-encoding XSS and marker_icon sanitization.
 */
class MapInfoWindowXssTest extends WP_UnitTestCase {
	public function set_up(): void {
		parent::set_up();

		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active.' );
		}
	}

	/**
	 * Parse the JSON payload from a rendered map container's data attribute,
	 * simulating the browser HTML parser + jQuery .data() JSON decoding.
	 */
	private function parse_map_options( string $html, string $attr ): array {
		$dom = new DOMDocument;
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$div = $dom->getElementsByTagName( 'div' )->item( 0 );
		$this->assertNotNull( $div, 'Expected rendered map <div> element.' );

		$raw = $div->getAttribute( $attr );
		$this->assertNotSame( '', $raw, "Expected non-empty {$attr} attribute." );

		$decoded = json_decode( $raw, true );
		$this->assertIsArray( $decoded, "Expected valid JSON in {$attr} attribute." );

		return $decoded;
	}

	/**
	 * Simulates Leaflet's marker.bindPopup( content ) or Google Maps InfoWindow( { content } ),
	 * which assigns content directly to innerHTML of a popup container.
	 * Checks if ANY dangerous elements or event handler attributes are created in the DOM.
	 */
	private function assertNoDangerousDomExecution( string $popup_content ): void {
		$dom = new DOMDocument;
		// Suppress warnings for fragments
		@$dom->loadHTML( '<?xml encoding="utf-8" ?><div>' . $popup_content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		$xpath = new DOMXPath( $dom );

		// Check for forbidden tags
		$forbidden_tags = [ 'script', 'iframe', 'object', 'embed' ];
		foreach ( $forbidden_tags as $tag ) {
			$elements = $xpath->query( "//{$tag}" );
			$this->assertSame( 0, $elements->length, "Dangerous element <{$tag}> found in DOM from popup content!" );
		}

		// Check all elements for on* event handlers (onerror, onload, onclick, ontoggle, etc.)
		$all_elements = $xpath->query( '//*' );
		foreach ( $all_elements as $elem ) {
			if ( ! ( $elem instanceof DOMElement ) ) {
				continue;
			}
			foreach ( $elem->attributes as $attr ) {
				$name = strtolower( $attr->name );
				$this->assertFalse(
					str_starts_with( $name, 'on' ),
					"Dangerous event handler attribute '{$name}' found on element <{$elem->nodeName}>!"
				);
			}
		}
	}

	public function testOsmEntityEncodedHtmlInInfoWindowIsNotDecoded(): void {
		$payloads = [
			'&lt;img src=x onerror=alert(1)&gt;',
			'&LT;IMG SRC=X ONERROR=ALERT(1)&GT;',
			'&#60;svg onload=alert(1)&#62;',
			'&#060;svg onload=alert(1)&#062;',
			'&#0000060;svg onload=alert(1)&#0000062;',
			'&#x3c;script&#x3e;alert(1)&#x3c;/script&#x3e;',
			'&#x3C;SCRIPT&#x3E;alert(1)&#x3C;/SCRIPT&#x3E;',
			'&lt;iframe src=javascript:alert(1)&gt;',
			'&lt;details open ontoggle=alert(1)&gt;',
			'&lt;body onload=alert(1)&gt;',
			'&lt;a href="javascript:alert(1)"&gt;click&lt;/a&gt;',
		];

		foreach ( $payloads as $payload ) {
			$html    = RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
				'info_window' => $payload,
			] );
			$options = $this->parse_map_options( $html, 'data-osm_options' );

			$this->assertStringNotContainsString( '<', $options['info_window'] );
			$this->assertStringNotContainsString( '>', $options['info_window'] );
			$this->assertNoDangerousDomExecution( $options['info_window'] );
		}
	}

	public function testMapEntityEncodedHtmlInInfoWindowIsNotDecoded(): void {
		$payloads = [
			'&lt;img src=x onerror=alert(1)&gt;',
			'&LT;IMG SRC=X ONERROR=ALERT(1)&GT;',
			'&#60;svg onload=alert(1)&#62;',
			'&#060;svg onload=alert(1)&#062;',
			'&#0000060;svg onload=alert(1)&#0000062;',
			'&#x3c;script&#x3e;alert(1)&#x3c;/script&#x3e;',
			'&#x3C;SCRIPT&#x3E;alert(1)&#x3C;/SCRIPT&#x3E;',
			'&lt;iframe src=javascript:alert(1)&gt;',
			'&lt;details open ontoggle=alert(1)&gt;',
			'&lt;body onload=alert(1)&gt;',
			'&lt;a href="javascript:alert(1)"&gt;click&lt;/a&gt;',
		];

		foreach ( $payloads as $payload ) {
			$html    = RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'info_window' => $payload,
			] );
			$options = $this->parse_map_options( $html, 'data-map_options' );

			$this->assertStringNotContainsString( '<', $options['info_window'] );
			$this->assertStringNotContainsString( '>', $options['info_window'] );
			$this->assertNoDangerousDomExecution( $options['info_window'] );
		}
	}

	public function testAttributeBreakoutAttempts(): void {
		$breakouts = [
			'"><script>alert(1)</script><div data-x="',
			'"><img src=x onerror=alert(1)><div data-x="',
			'\'><script>alert(1)</script><div data-x=\'',
			'&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;',
			'\\"><script>alert(1)</script>',
		];

		foreach ( $breakouts as $payload ) {
			$osm_html = RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
				'info_window' => $payload,
			] );
			$osm_opts = $this->parse_map_options( $osm_html, 'data-osm_options' );
			$this->assertNoDangerousDomExecution( $osm_opts['info_window'] );

			$map_html = RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'info_window' => $payload,
			] );
			$map_opts = $this->parse_map_options( $map_html, 'data-map_options' );
			$this->assertNoDangerousDomExecution( $map_opts['info_window'] );
		}
	}

	public function testDoubleEncodedEntitiesStayEncoded(): void {
		$payload = '&amp;lt;img src=x onerror=alert(1)&amp;gt;';

		$osm_html = RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
			'info_window' => $payload,
		] );
		$osm_opts = $this->parse_map_options( $osm_html, 'data-osm_options' );
		$this->assertStringNotContainsString( '<', $osm_opts['info_window'] );
		$this->assertSame( $payload, $osm_opts['info_window'] );
		$this->assertNoDangerousDomExecution( $osm_opts['info_window'] );

		$map_html = RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
			'info_window' => $payload,
		] );
		$map_opts = $this->parse_map_options( $map_html, 'data-map_options' );
		$this->assertStringNotContainsString( '<', $map_opts['info_window'] );
		$this->assertSame( $payload, $map_opts['info_window'] );
		$this->assertNoDangerousDomExecution( $map_opts['info_window'] );
	}

	public function testLegitimateHtmlInInfoWindowIsPreserved(): void {
		$safe_html = '<strong>Office</strong> &amp; <a href="https://example.com?a=1&amp;b=2" target="_blank" title="Our Office">Directions</a>';

		$osm_html = RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
			'info_window' => $safe_html,
		] );
		$osm_opts = $this->parse_map_options( $osm_html, 'data-osm_options' );
		$this->assertSame( $safe_html, $osm_opts['info_window'] );

		$map_html = RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
			'info_window' => $safe_html,
		] );
		$map_opts = $this->parse_map_options( $map_html, 'data-map_options' );
		$this->assertSame( $safe_html, $map_opts['info_window'] );
	}

	public function testRawDisallowedHtmlIsStripped(): void {
		$raw = '<script>alert(1)</script><img src="https://example.com/a.png" onerror="alert(1)"><b>OK</b>';

		$osm_html = RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
			'info_window' => $raw,
		] );
		$osm_opts = $this->parse_map_options( $osm_html, 'data-osm_options' );
		$this->assertStringNotContainsString( '<script', $osm_opts['info_window'] );
		$this->assertStringNotContainsString( 'onerror', $osm_opts['info_window'] );
		$this->assertStringContainsString( '<b>OK</b>', $osm_opts['info_window'] );
		$this->assertNoDangerousDomExecution( $osm_opts['info_window'] );

		$map_html = RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
			'info_window' => $raw,
		] );
		$map_opts = $this->parse_map_options( $map_html, 'data-map_options' );
		$this->assertStringNotContainsString( '<script', $map_opts['info_window'] );
		$this->assertStringNotContainsString( 'onerror', $map_opts['info_window'] );
		$this->assertStringContainsString( '<b>OK</b>', $map_opts['info_window'] );
		$this->assertNoDangerousDomExecution( $map_opts['info_window'] );
	}

	public function testMarkerIconSanitization(): void {
		$bad_icons = [
			'javascript:alert(1)',
			'JAVASCRIPT:alert(1)',
			'javascript:/*--></title></style></textarea>*/<svg/onload=alert(1)>',
			'data:text/html,<script>alert(1)</script>',
			'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>',
			'vbscript:msgbox(1)',
			'file:///etc/passwd',
		];

		foreach ( $bad_icons as $bad_icon ) {
			$osm_bad = $this->parse_map_options(
				RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
					'marker_icon' => $bad_icon,
				] ),
				'data-osm_options'
			);
			$this->assertSame( '', $osm_bad['marker_icon'], "Expected OSM to reject marker_icon: {$bad_icon}" );

			$map_bad = $this->parse_map_options(
				RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
					'marker_icon' => $bad_icon,
				] ),
				'data-map_options'
			);
			$this->assertSame( '', $map_bad['marker_icon'], "Expected Map to reject marker_icon: {$bad_icon}" );

			$map_bad_obj = $this->parse_map_options(
				RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
					'marker_icon' => [ 'url' => $bad_icon ],
				] ),
				'data-map_options'
			);
			$this->assertSame( '', $map_bad_obj['marker_icon'], "Expected Map obj to reject invalid marker_icon: {$bad_icon}" );
		}

		$osm_good = $this->parse_map_options(
			RWMB_OSM_Field::render_map( '21.0285,105.8542,14', [
				'marker_icon' => 'https://example.com/pin.png',
			] ),
			'data-osm_options'
		);
		$this->assertSame( 'https://example.com/pin.png', $osm_good['marker_icon'] );

		$map_rel = $this->parse_map_options(
			RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'marker_icon' => '/wp-content/uploads/pin.png',
			] ),
			'data-map_options'
		);
		$this->assertSame( '/wp-content/uploads/pin.png', $map_rel['marker_icon'] );

		$map_icon_obj = $this->parse_map_options(
			RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'marker_icon' => [
					'url' => 'https://example.com/pin.png',
				],
			] ),
			'data-map_options'
		);
		$this->assertSame( 'https://example.com/pin.png', $map_icon_obj['marker_icon']['url'] );

		$symbol          = [
			'path'      => 0,
			'scale'     => 8,
			'fillColor' => '#f00',
		];
		$map_icon_symbol = $this->parse_map_options(
			RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'marker_icon' => $symbol,
			] ),
			'data-map_options'
		);
		$this->assertSame( $symbol, $map_icon_symbol['marker_icon'] );

		$map_icon_non_string_url = $this->parse_map_options(
			RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'marker_icon' => [
					'url' => [ 'invalid' ],
				],
			] ),
			'data-map_options'
		);
		$this->assertSame( '', $map_icon_non_string_url['marker_icon'] );

		$map_icon_unknown_array = $this->parse_map_options(
			RWMB_Map_Field::render_map( '21.0285,105.8542,14', [
				'marker_icon' => [
					'scaledSize' => [ 32, 32 ],
				],
			] ),
			'data-map_options'
		);
		$this->assertSame( '', $map_icon_unknown_array['marker_icon'] );
	}

	public function testShortcodePipelineDoesNotDecodeEntityEncodedInfoWindow(): void {
		$post_id = wp_insert_post( [
			'post_title'   => 'OSM Shortcode XSS Test',
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_content' => '',
		] );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			$this->markTestSkipped( 'Failed to create test post.' );
		}

		update_post_meta( $post_id, 'test_osm_xss', '21.0285,105.8542,14' );
		rwmb_get_registry( 'field' )->add( RWMB_OSM_Field::normalize( [
			'id'   => 'test_osm_xss',
			'type' => 'osm',
		] ), 'post', 'post' );

		$shortcode = new RWMB_Shortcode;
		$payloads  = [
			'&lt;img src=x onerror=alert(1)&gt;',
			'&LT;SCRIPT&GT;alert(1)&LT;/SCRIPT&GT;',
			'&#60;svg onload=alert(1)&#62;',
			'"><script>alert(1)</script>',
		];

		foreach ( $payloads as $payload ) {
			// Test with render_shortcodes = false
			$html1 = $shortcode->register_shortcode( [
				'id'                => 'test_osm_xss',
				'object_id'         => $post_id,
				'info_window'       => $payload,
				'render_shortcodes' => 'false',
			] );

			$options1 = $this->parse_map_options( $html1, 'data-osm_options' );
			$this->assertNoDangerousDomExecution( $options1['info_window'] );

			// Test with render_shortcodes = true
			$html2 = $shortcode->register_shortcode( [
				'id'                => 'test_osm_xss',
				'object_id'         => $post_id,
				'info_window'       => $payload,
				'render_shortcodes' => 'true',
			] );

			$options2 = $this->parse_map_options( $html2, 'data-osm_options' );
			$this->assertNoDangerousDomExecution( $options2['info_window'] );
		}

		wp_delete_post( $post_id, true );
	}
}
