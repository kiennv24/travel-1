<?php

/**
 * Shared sanitization and escaping helpers for template CSS output.
 *
 * @package    WP_TripAdvisor_Review
 * @subpackage WP_TripAdvisor_Review/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_TripAdvisor_Review_Sanitize {

	/**
	 * Sanitize a value destined for use inside a CSS declaration (e.g. color).
	 *
	 * @param string $value Raw color/CSS value.
	 * @return string Safe CSS value, or '' if it cannot be made safe.
	 */
	public static function sanitize_css_color( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/[<>"\'`;{}\\\\()]/', $value ) && ! self::is_css_color_function( $value ) ) {
			return '';
		}

		if ( preg_match( '/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
			return $value;
		}

		if ( self::is_css_color_function( $value ) ) {
			return $value;
		}

		if ( preg_match( '/^[a-zA-Z]+$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Whether a value is a safe rgb/rgba/hsl/hsla functional color notation.
	 *
	 * @param string $value Raw value.
	 * @return bool
	 */
	private static function is_css_color_function( $value ) {
		return (bool) preg_match(
			'/^(rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/]+\)$/i',
			trim( (string) $value )
		);
	}

	/**
	 * Sanitize a numeric CSS value (border radius, image size, etc.).
	 *
	 * @param mixed $value Raw value.
	 * @return int Non-negative integer.
	 */
	public static function sanitize_css_number( $value ) {
		return absint( $value );
	}

	/**
	 * Build the inline style rules for a review template from template_misc.
	 *
	 * @param int    $template_id   Template row id.
	 * @param string $style         Template style number.
	 * @param array  $misc          Decoded template_misc array.
	 * @param string $widget_suffix Either '' or '_widget'.
	 * @param bool   $scope_by_id   Whether to scope under #wprev-slider-{id}.
	 * @return string Sanitized CSS without surrounding style tags.
	 */
	public static function build_template_misc_style( $template_id, $style, $misc, $widget_suffix = '', $scope_by_id = true ) {
		if ( ! is_array( $misc ) ) {
			return '';
		}

		$tid    = absint( $template_id );
		$style  = preg_replace( '/[^0-9]/', '', (string) $style );
		$suffix = ( '_widget' === $widget_suffix ) ? '_widget' : '';
		$scope  = $scope_by_id ? ( '#wprev-slider-' . $tid . ' ' ) : '';

		$bgcolor1 = isset( $misc['bgcolor1'] ) ? self::sanitize_css_color( $misc['bgcolor1'] ) : '';
		$bgcolor2 = isset( $misc['bgcolor2'] ) ? self::sanitize_css_color( $misc['bgcolor2'] ) : '';
		$tcolor1  = isset( $misc['tcolor1'] ) ? self::sanitize_css_color( $misc['tcolor1'] ) : '';
		$tcolor2  = isset( $misc['tcolor2'] ) ? self::sanitize_css_color( $misc['tcolor2'] ) : '';
		$tcolor3  = isset( $misc['tcolor3'] ) ? self::sanitize_css_color( $misc['tcolor3'] ) : '';
		$bradius  = isset( $misc['bradius'] ) ? self::sanitize_css_number( $misc['bradius'] ) : 0;

		$css = '';

		if ( isset( $misc['showstars'] ) && 'no' === $misc['showstars'] ) {
			$css .= $scope . '.wprevpro_star_imgs_T' . $style . $suffix . ' {display: none;}';
		}

		if ( isset( $misc['showdate'] ) && 'no' === $misc['showdate'] ) {
			$css .= $scope . '.wprev_showdate_T' . $style . $suffix . ' {display: none;}';
		}

		$css .= $scope . '.wprev_preview_bradius_T' . $style . $suffix . ' {border-radius: ' . $bradius . 'px;}';

		if ( '' !== $bgcolor1 ) {
			$css .= $scope . '.wprev_preview_bg1_T' . $style . $suffix . ' {background:' . $bgcolor1 . ';}';
		}
		if ( '' !== $bgcolor2 ) {
			$css .= $scope . '.wprev_preview_bg2_T' . $style . $suffix . ' {background:' . $bgcolor2 . ';}';
		}
		if ( '' !== $tcolor1 ) {
			$css .= $scope . '.wprev_preview_tcolor1_T' . $style . $suffix . ' {color:' . $tcolor1 . ';}';
		}
		if ( '' !== $tcolor2 ) {
			$css .= $scope . '.wprev_preview_tcolor2_T' . $style . $suffix . ' {color:' . $tcolor2 . ';}';
		}

		$tfont1 = isset( $misc['tfont1'] ) ? self::sanitize_css_number( $misc['tfont1'] ) : 0;
		$tfont2 = isset( $misc['tfont2'] ) ? self::sanitize_css_number( $misc['tfont2'] ) : 0;
		if ( $tfont1 > 0 ) {
			$css .= $scope . '.wprev_preview_tcolor1_T' . $style . $suffix . ' {font-size:' . $tfont1 . 'px !important;line-height:normal !important;}';
		}
		if ( $tfont2 > 0 ) {
			$css .= $scope . '.wprev_preview_tcolor2_T' . $style . $suffix . ' {font-size:' . $tfont2 . 'px !important;line-height:normal !important;}';
		}

		if ( '1' === (string) $style && '' !== $bgcolor1 ) {
			$css .= $scope . '.wprev_preview_bg1_T' . $style . $suffix . '::after{ border-top: 30px solid ' . $bgcolor1 . '; }';
		}
		if ( '6' === (string) $style && '' !== $bgcolor2 ) {
			$css .= $scope . '.wprev_preview_bg1_T' . $style . $suffix . ' {border:1px solid ' . $bgcolor2 . ';}';
		}

		if ( $scope_by_id && isset( $misc['read_more_color'] ) ) {
			$read_more_color = self::sanitize_css_color( $misc['read_more_color'] );
			if ( '' !== $read_more_color ) {
				$css .= $scope . '.wprs_rd_more{color:' . $read_more_color . ';}';
			}
		}

		return $css;
	}
}
