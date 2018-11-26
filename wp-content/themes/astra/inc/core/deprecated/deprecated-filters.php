<?php
/**
 * Deprecated Filters of Astra Theme.
 *
 * @package     Astra
 * @author      Astra
 * @copyright   Copyright (c) 2017, Astra
 * @link        http://wpastra.com/
 * @since       Astra 1.0.23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Depreciating astra_color_palletes filter.
add_filter( 'astra_color_palettes', 'deprecated_astra_color_palette', 10, 1 );

/**
 * Astra Color Palettes
 *
 * @since 1.0.23
 * @param array $color_palette  customizer color palettes.
 * @return array  $color_palette updated customizer color palettes.
 */
function deprecated_astra_color_palette( $color_palette ) {

	$color_palette = apply_filters_deprecated( 'astra_color_palletes', array( $color_palette ), '1.0.22', 'astra_color_palettes', '' );

	return $color_palette;
}
