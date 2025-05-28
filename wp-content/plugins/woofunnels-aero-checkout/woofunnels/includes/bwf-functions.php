<?php

if ( ! function_exists( 'bwf_get_remote_rest_args' ) ) {
	function bwf_get_remote_rest_args( $data = '', $method = 'POST' ) {
		return apply_filters( 'bwf_get_remote_rest_args', [
			'method'    => $method,
			'body'      => $data,
			'timeout'   => 0.01,
			'sslverify' => false,
		] );
	}
}
if ( ! function_exists( 'bwf_clean' ) ) {
	function bwf_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'bwf_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}
if ( ! function_exists( 'bwf_get_states' ) ) {
	function bwf_get_states( $country = '', $state = '' ) {
		$country_states = apply_filters( 'bwf_get_states', include WooFunnel_Loader::$ultimate_path . 'helpers/states.php' );

		if ( empty( $state ) ) {
			return '';
		}
		if ( empty( $country ) ) {
			return $state;
		}
		if ( ! isset( $country_states[ $country ] ) ) {
			return $state;
		}
		if ( ! isset( $country_states[ $country ][ $state ] ) ) {
			return $state;
		}

		return $country_states[ $country ][ $state ];
	}
}


/**
 * get the list of all the registered fonts
 * we have 3 modes here, 'standard', 'name_only','name_key' and 'all'
 *
 * @param string $mode
 *
 * @return array
 */
if ( ! function_exists( 'bwf_get_fonts_list' ) ) {

	function bwf_get_fonts_list( $mode = 'standard' ) {
		$fonts        = [];
		$fontpath     = WooFunnel_Loader::$ultimate_path . '/helpers/fonts.json';
		$google_fonts = json_decode( file_get_contents( $fontpath ), true );     //phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		$web_fonts    = ( $mode !== 'all' ) ? array_keys( $google_fonts ) : $google_fonts;

		if ( $mode === 'all' || $mode === 'name_only' ) {
			return $web_fonts;
		}

		/**
		 * if the name_key mode
		 */
		if ( $mode === 'name_key' ) {

			foreach ( $web_fonts as $web_font_family ) {

				if ( $web_font_family !== 'Open Sans' ) {

					$fonts[ $web_font_family ] = $web_font_family;
				}
			}

			return $fonts;
		}


		/**
		 * if standard mode
		 */
		$fonts[] = array(
			'id'   => 'default',
			'name' => __( 'Default', 'funnel-builder' )
		);
		foreach ( $web_fonts as $web_font_family ) {

			if ( $web_font_family !== 'Open Sans' ) {

				$fonts[] = array(
					'id'   => $web_font_family,
					'name' => $web_font_family,
				);
			}
		}

		return $fonts;

	}
}