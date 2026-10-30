<?php
class WP_Trip_Template_Functions {

	/**
	 * Format reviewer name based on template_misc lastnameformat.
	 * Defaults to full name (current TripAdvisor behavior) when unset.
	 *
	 * @param object $review Review row.
	 * @param array  $template_misc_array Decoded template_misc.
	 * @return string
	 */
	public function wprevpro_get_reviewername( $review, $template_misc_array ) {
		$tempreviewername = stripslashes( strip_tags( $review->reviewer_name ) );
		$words            = explode( ' ', $tempreviewername );
		$firstname        = isset( $words[0] ) ? $words[0] : $tempreviewername;

		if ( ! isset( $template_misc_array['lastnameformat'] ) || $template_misc_array['lastnameformat'] === '' ) {
			$template_misc_array['lastnameformat'] = 'show';
		}

		if ( $template_misc_array['lastnameformat'] === 'hide' ) {
			$tempreviewername = $firstname;
		} elseif ( $template_misc_array['lastnameformat'] === 'initial' ) {
			$tempreviewername = $firstname;
			if ( isset( $words[1] ) && $words[1] !== '' ) {
				$tempreviewername .= ' ' . strtoupper( substr( $words[1], 0, 1 ) ) . '.';
			}
		}

		return $tempreviewername;
	}

	/**
	 * Build a local initials avatar as a data-URI SVG (no external service).
	 *
	 * @param string $name Reviewer name.
	 * @param int    $size Pixel size.
	 * @return string data:image/svg+xml;base64,... URL
	 */
	public function wprev_get_initials_avatar_url( $name, $size = 100 ) {
		$name = trim( wp_strip_all_tags( (string) $name ) );
		$size = absint( $size );
		if ( $size < 1 ) {
			$size = 100;
		}
		if ( $size > 500 ) {
			$size = 500;
		}

		$words = preg_split( '/\s+/', $name );
		if ( is_array( $words ) && count( $words ) >= 2 ) {
			$initials = strtoupper( substr( $words[0], 0, 1 ) . substr( $words[ count( $words ) - 1 ], 0, 1 ) );
		} elseif ( $name !== '' ) {
			$initials = strtoupper( substr( $name, 0, 1 ) );
		} else {
			$initials = 'U';
		}

		$hash       = md5( $name !== '' ? $name : 'U' );
		$background = '#' . substr( $hash, 0, 6 );
		$r          = hexdec( substr( $hash, 0, 2 ) );
		$g          = hexdec( substr( $hash, 2, 2 ) );
		$b          = hexdec( substr( $hash, 4, 2 ) );
		$brightness = ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;
		if ( $brightness > 200 ) {
			$background = sprintf( '#%02x%02x%02x', max( 0, $r - 50 ), max( 0, $g - 50 ), max( 0, $b - 50 ) );
		}

		$font_size = (int) round( $size * 0.4 );
		$svg       = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
		$svg      .= '<rect width="100%" height="100%" fill="' . $background . '"/>';
		$svg      .= '<text x="50%" y="50%" dy=".1em" fill="#ffffff" font-family="Arial,Helvetica,sans-serif" font-size="' . $font_size . '" font-weight="bold" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars( $initials, ENT_QUOTES, 'UTF-8' ) . '</text>';
		$svg      .= '</svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Escape an image src that may be https or a data URI.
	 *
	 * @param string $url Image URL.
	 * @return string
	 */
	public function wprev_esc_avatar_src( $url ) {
		if ( strpos( (string) $url, 'data:' ) === 0 ) {
			return esc_attr( $url );
		}
		return esc_url( $url );
	}

	/**
	 * Get media HTML for a review when showmedia is yes.
	 *
	 * @param object $review Review row.
	 * @param array  $template_misc_array Decoded template_misc.
	 * @return string
	 */
	public function wprevpro_get_media( $review, $template_misc_array ) {
		$media = '';
		if ( ! isset( $template_misc_array['showmedia'] ) ) {
			$template_misc_array['showmedia'] = 'no';
		}
		if ( $template_misc_array['showmedia'] === 'yes' ) {
			$mediaurls       = stripslashes( $review->mediaurlsarrayjson );
			$mediathumburls  = stripslashes( $review->mediathumburlsarrayjson );
			$mediathumburlsarray = json_decode( $mediathumburls, true );

			if ( isset( $mediaurls ) && $mediaurls !== '' ) {
				$mediaurlsarray = json_decode( $mediaurls, true );
				if ( is_array( $mediaurlsarray ) ) {
					$mediaurlsarray = array_filter( $mediaurlsarray );
					if ( count( $mediaurlsarray ) > 0 ) {
						$media          = '<div class="wprev_media_div ' . count( $mediaurlsarray ) . '">';
						$mediaurlsarray = array_values( $mediaurlsarray );
						$n              = 0;
						foreach ( $mediaurlsarray as &$urlvalue ) {
							if ( $urlvalue !== '' ) {
								$urlvalue = esc_url( $urlvalue );
								if ( isset( $mediathumburlsarray[ $n ] ) && $mediathumburlsarray[ $n ] !== '' ) {
									$thumburl = $mediathumburlsarray[ $n ];
								} else {
									$thumburl = $urlvalue;
								}
								$thumburl = esc_url( $thumburl );
								if ( stripos( $urlvalue, 'youtu' ) === false ) {
									$tempclass = 'notyoutu';
								} else {
									$tempclass = 'youtu';
								}
								$media = $media . '<a class="wprev_media_img_a ' . $tempclass . '" href="' . $urlvalue . '" data-lity-desc="Review media"><img src="' . $thumburl . '" class="wprev_media_img" alt="media thumbnail ' . $n . '"></a>';
							}
							$n++;
						}
						$media = $media . '</div>';
					}
				}
			}
		}
		return $media;
	}

	/**
	 * Source average and total stored for a location. No local-review fallback.
	 *
	 * @param string $pageid TripAdvisor page id (filtersource), or '' for a single-source site.
	 * @return array{avg:string,total:string}
	 */
	public function wprev_badge_avg_total( $pageid ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wptripadvisor_total_averages';
		$row   = null;

		if ( $pageid !== '' ) {
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT avg, total FROM {$table} WHERE btp_id = %s LIMIT 1",
					$pageid
				)
			);
		} else {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT avg, total FROM {$table} WHERE btp_type = %s",
					'page'
				)
			);
			if ( is_array( $rows ) && count( $rows ) === 1 ) {
				$row = $rows[0];
			}
		}

		$avg   = '';
		$total = '';
		if ( $row ) {
			$source_avg   = str_replace( ',', '.', trim( (string) $row->avg ) );
			$source_total = trim( (string) $row->total );
			if ( is_numeric( $source_avg ) && (float) $source_avg > 0 ) {
				$avg = $source_avg;
			}
			if ( is_numeric( $source_total ) && (int) $source_total > 0 ) {
				$total = (string) (int) $source_total;
			}
		}

		return array(
			'avg'   => $avg,
			'total' => $total,
		);
	}

	/**
	 * One-decimal label for a badge average. Empty when there is no rating.
	 *
	 * @param string|float $avg Raw average.
	 * @return string
	 */
	public function wprev_format_badge_avg( $avg ) {
		$avg = (float) str_replace( ',', '.', trim( (string) $avg ) );
		if ( $avg <= 0 ) {
			return '';
		}
		if ( $avg > 5 ) {
			$avg = 5;
		}
		return number_format( $avg, 1, '.', '' );
	}

	/**
	 * TripAdvisor bubble image filename for an average, rounded to the nearest half star.
	 *
	 * @param string|float $avg Raw average.
	 * @return string Filename or '' when there is no rating.
	 */
	public function wprev_badge_star_file( $avg ) {
		$avg = (float) str_replace( ',', '.', trim( (string) $avg ) );
		if ( $avg <= 0 ) {
			return '';
		}
		if ( $avg > 5 ) {
			$avg = 5;
		}
		$rounded = round( $avg * 2 ) / 2;
		if ( abs( $rounded - round( $rounded ) ) < 0.001 ) {
			return 'tripadvisor_stars_' . (int) round( $rounded ) . '.png';
		}
		return 'tripadvisor_stars_' . number_format( $rounded, 1, '.', '' ) . '.png';
	}

	/**
	 * Badge row: numeric average plus the matching TripAdvisor bubble image.
	 *
	 * @param string|float $avg       Raw average.
	 * @param string       $imgs_base Plugin imgs directory URL, with trailing slash.
	 * @return string HTML or '' when there is no rating.
	 */
	public function wprev_badge_star_html( $avg, $imgs_base ) {
		$display = $this->wprev_format_badge_avg( $avg );
		$file    = $this->wprev_badge_star_file( $avg );
		if ( $display === '' || $file === '' ) {
			return '';
		}
		$src = $imgs_base . $file;
		return '<div class="wprevstardiv"><span class="wprev-tripadvisor-rating">' . esc_html( $display ) . '</span><img class="wprev-tripadvisor-badge-stars" src="' . esc_url( $src ) . '" alt="' . esc_attr( sprintf( '%s star rating', $display ) ) . '" width="84" height="18"></div>';
	}

}
