<?php
/**
 * Analytics AJAX handlers for WP TripAdvisor Review Slider.
 *
 * @package WP_TripAdvisor_Review
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Analytics AJAX handlers.
 */
class WP_TripAdvisor_Review_Analytics {

	/**
	 * Capability check for analytics.
	 *
	 * @return bool
	 */
	private function can_view_analytics() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Overall ratings chart + word cloud data.
	 */
	public function wppro_get_overall_chart_data() {
		if ( ! $this->can_view_analytics() ) {
			wp_die( 'Insufficient permissions' );
		}
		// Analytics JS posts this as _ajax_nonce for the overall chart request.
		check_ajax_referer( 'randomnoncestring', '_ajax_nonce' );

		$startdate  = sanitize_text_field( wp_unslash( $_POST['startd'] ) );
		$enddate    = sanitize_text_field( wp_unslash( $_POST['endd'] ) );
		$rtypearray = json_decode( sanitize_text_field( wp_unslash( $_POST['stypes'] ) ), true );
		$rpagearray = json_decode( sanitize_text_field( wp_unslash( $_POST['slocations'] ) ), true );
		$filtertext = sanitize_text_field( wp_unslash( $_POST['filtertext'] ) );

		$utstartdate = strtotime( $startdate );
		$utenddate   = strtotime( $enddate );

		$rpagefilter = '';
		$rtypefilter = '';

		if ( is_array( $rpagearray ) ) {
			$rpagearray = array_values( array_filter( $rpagearray ) );
			if ( count( $rpagearray ) > 0 ) {
				for ( $x = 0; $x < count( $rpagearray ); $x++ ) {
					if ( 0 === $x ) {
						$rpagefilter = "AND (pageid = '" . esc_sql( $rpagearray[ $x ] ) . "'";
					} else {
						$rpagefilter .= " OR pageid = '" . esc_sql( $rpagearray[ $x ] ) . "'";
					}
				}
				$rpagefilter .= ')';
			}
		}

		if ( is_array( $rtypearray ) ) {
			$rtypearray = array_values( array_filter( $rtypearray ) );
			if ( count( $rtypearray ) > 0 ) {
				for ( $x = 0; $x < count( $rtypearray ); $x++ ) {
					$type_val = strtolower( $rtypearray[ $x ] );
					if ( 0 === $x ) {
						$rtypefilter = "AND (LOWER(type) = '" . esc_sql( $type_val ) . "'";
					} else {
						$rtypefilter .= " OR LOWER(type) = '" . esc_sql( $type_val ) . "'";
					}
				}
				$rtypefilter .= ')';
			}
		}

		// TripAdvisor free plugin: default to TripAdvisor reviews when no type filter is set.
		if ( '' === $rtypefilter ) {
			$rtypefilter = "AND LOWER(type) = 'tripadvisor'";
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'wptripadvisor_reviews';

		if ( $filtertext !== '' ) {
			$like        = '%' . $wpdb->esc_like( $filtertext ) . '%';
			$querystring = $wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE (reviewer_name LIKE %s OR review_text LIKE %s) AND rating > 0 AND (created_time_stamp >= %d AND created_time_stamp <= %d) {$rtypefilter} {$rpagefilter} ORDER BY created_time_stamp ASC",
				$like,
				$like,
				$utstartdate,
				$utenddate
			);
		} else {
			$querystring = $wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE rating > 0 AND (created_time_stamp >= %d AND created_time_stamp <= %d) {$rtypefilter} {$rpagefilter} ORDER BY created_time_stamp ASC",
				$utstartdate,
				$utenddate
			);
		}

		$totalreviews = $wpdb->get_results( $querystring, ARRAY_A );

		$resultarray               = array();
		$resultarray['ratingvals'] = array();
		$positivewords             = '';
		$negativewords             = '';
		$ratingsarray              = array();
		$typeratingsarray          = array();

		for ( $x = 0; $x < count( $totalreviews ); $x++ ) {
			$temptext = $totalreviews[ $x ]['review_text'];
			if ( extension_loaded( 'mbstring' ) ) {
				$review_length_char = mb_strlen( $temptext );
				$temptext           = mb_substr( $temptext, 0, 70 ) . ( $review_length_char > 70 ? '...' : '' );
			} else {
				$review_length_char = strlen( $temptext );
				$temptext           = substr( $temptext, 0, 70 ) . ( $review_length_char > 70 ? '...' : '' );
			}
			$temptext  = wp_strip_all_tags( $temptext );
			$temptime  = $totalreviews[ $x ]['created_time_stamp'];
			$tempname  = $totalreviews[ $x ]['reviewer_name'] . ' - ' . date( 'M j, Y', $temptime );
			$temppage  = ( $totalreviews[ $x ]['pagename'] !== '' )
				? $totalreviews[ $x ]['type'] . ' - ' . $totalreviews[ $x ]['pagename']
				: $totalreviews[ $x ]['type'] . ' - ' . $totalreviews[ $x ]['pageid'];
			$temptitle = isset( $totalreviews[ $x ]['review_title'] ) ? $totalreviews[ $x ]['review_title'] : '';

			if ( $temptitle !== '' ) {
				$temptextarray = ( $temptext !== '' ) ? array( $temppage, $tempname, $temptitle, $temptext ) : array( $temppage, $tempname, $temptitle );
			} else {
				$temptextarray = ( $temptext !== '' ) ? array( $temppage, $tempname, $temptext ) : array( $temppage, $tempname );
			}

			$resultarray['labelvals'][] = $temptextarray;

			$rec_type = isset( $totalreviews[ $x ]['recommendation_type'] ) ? $totalreviews[ $x ]['recommendation_type'] : '';
			if ( $rec_type === 'positive' ) {
				$totalreviews[ $x ]['rating'] = 5;
			} elseif ( $rec_type === 'negative' ) {
				$totalreviews[ $x ]['rating'] = 2;
			}

			$resultarray['ratingvals'][] = (int) $totalreviews[ $x ]['rating'];
			$resultarray['reviewdata'][] = $totalreviews[ $x ];

			$tempnum = 0;
			if ( $totalreviews[ $x ]['rating'] > 0 ) {
				$tempnum = (int) $totalreviews[ $x ]['rating'];
			} elseif ( $rec_type === 'positive' ) {
				$tempnum = 5;
			} elseif ( $rec_type === 'negative' ) {
				$tempnum = 2;
			}

			$temptype                        = $totalreviews[ $x ]['type'];
			$typeratingsarray[ $temptype ][] = $tempnum;
			$ratingsarray[]                  = $tempnum;

			if ( $tempnum > 3 ) {
				$positivewords .= ' ' . $totalreviews[ $x ]['review_text'];
			} elseif ( $tempnum <= 3 ) {
				$negativewords .= ' ' . $totalreviews[ $x ]['review_text'];
			}
		}

		$resultarray['ratingtypenumvals'] = ( ! empty( $typeratingsarray ) && is_array( $typeratingsarray ) )
			? array_filter( $typeratingsarray )
			: '';

		$temprating = $this->wprp_get_temprating( $ratingsarray );
		if ( isset( $temprating ) ) {
			$tempratingarray = array(
				'numr1' => array_sum( $temprating[1] ),
				'numr2' => array_sum( $temprating[2] ),
				'numr3' => array_sum( $temprating[3] ),
				'numr4' => array_sum( $temprating[4] ),
				'numr5' => array_sum( $temprating[5] ),
			);
		} else {
			$tempratingarray = array(
				'numr1' => 0,
				'numr2' => 0,
				'numr3' => 0,
				'numr4' => 0,
				'numr5' => 0,
			);
		}
		$resultarray['ratingnumvals'] = $tempratingarray;

		$resultarray['avgrating'] = '';
		if ( ! empty( $ratingsarray ) && is_array( $ratingsarray ) ) {
			$ratingsarray             = array_filter( $ratingsarray );
			$resultarray['avgrating'] = round( array_sum( $ratingsarray ) / count( $ratingsarray ), 1 );
		}

		$stopwordsarray               = array( 'here', 'very', 'great', 'good', 'their', 'there', 'would', 'which', 'what', 'were', 'when', 'that', 'with', 'have', 'this', 'will', 'your', 'from', 'they', 'know', 'want', 'been', 'because', 'once' );
		$resultarray['poswordarray']  = $this->mostFrequentWords( $positivewords, $stopwordsarray );
		$resultarray['negwordarray']  = $this->mostFrequentWords( $negativewords, $stopwordsarray );

		echo wp_json_encode( $resultarray );
		die();
	}

	/**
	 * Count ratings into buckets.
	 *
	 * @param array $ratingsarray Ratings.
	 * @return array
	 */
	private function wprp_get_temprating( $ratingsarray ) {
		$temprating = array();
		for ( $x = 0; $x <= 5; $x++ ) {
			$temprating[ $x ][] = 0;
		}
		if ( ! is_array( $ratingsarray ) ) {
			return $temprating;
		}
		foreach ( $ratingsarray as $tempnum ) {
			$tempnum = (int) round( $tempnum );
			if ( $tempnum >= 1 && $tempnum <= 5 ) {
				$temprating[ $tempnum ][] = 1;
			}
		}
		return $temprating;
	}

	/**
	 * Most frequent words helper.
	 *
	 * @param string $string    Text.
	 * @param array  $stopWords Stop words.
	 * @param int    $limit     Limit.
	 * @return array
	 */
	public function mostFrequentWords( $string, $stopWords = array(), $limit = 15 ) {
		$string = preg_replace( '/\b[A-Za-z0-9]{1,3}\b\s?/i', '', $string );
		$words  = array_count_values( array_diff( str_word_count( strtolower( (string) $string ), 1 ), $stopWords ) );
		arsort( $words );
		return array_slice( $words, 0, $limit );
	}

	/**
	 * Shared WHERE builder for newer analytics endpoints.
	 *
	 * @return array{clause:string,values:array}
	 */
	private function build_analytics_where() {
		global $wpdb;
		$seltypes     = isset( $_POST['seltypes'] ) ? sanitize_text_field( wp_unslash( $_POST['seltypes'] ) ) : '';
		$sellocations = isset( $_POST['sellocations'] ) ? sanitize_text_field( wp_unslash( $_POST['sellocations'] ) ) : '';
		$filtertext   = isset( $_POST['filtertext'] ) ? sanitize_text_field( wp_unslash( $_POST['filtertext'] ) ) : '';
		$startdate    = isset( $_POST['startdate'] ) ? sanitize_text_field( wp_unslash( $_POST['startdate'] ) ) : '';
		$enddate      = isset( $_POST['enddate'] ) ? sanitize_text_field( wp_unslash( $_POST['enddate'] ) ) : '';

		$where_conditions = array();
		$where_values     = array();

		$types_array = ! empty( $seltypes ) ? json_decode( $seltypes, true ) : array();
		if ( is_array( $types_array ) && ! empty( $types_array ) ) {
			$types_array        = array_map( 'strtolower', $types_array );
			$placeholders       = implode( ',', array_fill( 0, count( $types_array ), '%s' ) );
			$where_conditions[] = "LOWER(type) IN ($placeholders)";
			$where_values       = array_merge( $where_values, $types_array );
		} else {
			$where_conditions[] = 'LOWER(type) = %s';
			$where_values[]     = 'tripadvisor';
		}

		if ( ! empty( $sellocations ) ) {
			$locations_array = json_decode( $sellocations, true );
			if ( is_array( $locations_array ) && ! empty( $locations_array ) ) {
				$placeholders       = implode( ',', array_fill( 0, count( $locations_array ), '%s' ) );
				$where_conditions[] = "pageid IN ($placeholders)";
				$where_values       = array_merge( $where_values, $locations_array );
			}
		}

		if ( ! empty( $filtertext ) ) {
			$where_conditions[] = '(review_text LIKE %s OR review_title LIKE %s OR reviewer_name LIKE %s)';
			$search_term        = '%' . $wpdb->esc_like( $filtertext ) . '%';
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
			$where_values[]     = $search_term;
		}

		return array(
			'conditions' => $where_conditions,
			'values'     => $where_values,
			'startdate'  => $startdate,
			'enddate'    => $enddate,
		);
	}

	/**
	 * Review volume timeline.
	 */
	public function wprevpro_ajax_analytics_volume() {
		if ( ! $this->can_view_analytics() ) {
			wp_die( 'Insufficient permissions' );
		}

		global $wpdb;
		$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$built              = $this->build_analytics_where();
		$where_conditions   = $built['conditions'];
		$where_values       = $built['values'];
		$startdate          = $built['startdate'];
		$enddate            = $built['enddate'];

		if ( ! empty( $startdate ) && ! empty( $enddate ) ) {
			$where_conditions[] = 'DATE(created_time) BETWEEN %s AND %s';
			$where_values[]     = $startdate;
			$where_values[]     = $enddate;
		}

		$where_clause = ! empty( $where_conditions ) ? 'WHERE ' . implode( ' AND ', $where_conditions ) : '';

		$range_start_dt = ! empty( $startdate ) ? new DateTime( $startdate ) : ( new DateTime() )->modify( '-29 days' );
		$range_end_dt   = ! empty( $enddate ) ? new DateTime( $enddate ) : new DateTime();

		$query = "SELECT DATE(created_time) as review_date, COUNT(*) as review_count
			FROM {$reviews_table_name}
			{$where_clause}
			GROUP BY DATE(created_time)
			ORDER BY review_date ASC";

		$results = ! empty( $where_values )
			? $wpdb->get_results( $wpdb->prepare( $query, $where_values ) )
			: $wpdb->get_results( $query );

		$date_counts = array();
		foreach ( $results as $row ) {
			$date_counts[ $row->review_date ] = (int) $row->review_count;
		}

		$labels    = array();
		$data      = array();
		$date_keys = array();
		$period    = new DatePeriod( $range_start_dt, new DateInterval( 'P1D' ), ( clone $range_end_dt )->modify( '+1 day' ) );
		foreach ( $period as $current_date ) {
			$date_str    = $current_date->format( 'Y-m-d' );
			$labels[]    = $current_date->format( 'M j' );
			$data[]      = isset( $date_counts[ $date_str ] ) ? $date_counts[ $date_str ] : 0;
			$date_keys[] = $date_str;
		}

		wp_send_json_success(
			array(
				'labels'    => $labels,
				'data'      => $data,
				'date_keys' => $date_keys,
			)
		);
	}

	/**
	 * Platform rating comparison.
	 */
	public function wprevpro_ajax_analytics_platform() {
		if ( ! $this->can_view_analytics() ) {
			wp_die( 'Insufficient permissions' );
		}

		global $wpdb;
		$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$built              = $this->build_analytics_where();
		$where_conditions   = $built['conditions'];
		$where_values       = $built['values'];
		$startdate          = $built['startdate'];
		$enddate            = $built['enddate'];

		if ( ! empty( $startdate ) && ! empty( $enddate ) ) {
			$where_conditions[] = 'DATE(created_time) BETWEEN %s AND %s';
			$where_values[]     = $startdate;
			$where_values[]     = $enddate;
		}

		$where_clause = ! empty( $where_conditions ) ? 'WHERE ' . implode( ' AND ', $where_conditions ) : '';
		$query        = "SELECT type as platform, AVG(rating) as avg_rating, COUNT(*) as review_count
			FROM {$reviews_table_name}
			{$where_clause}
			GROUP BY type
			ORDER BY avg_rating DESC";

		$results = ! empty( $where_values )
			? $wpdb->get_results( $wpdb->prepare( $query, $where_values ) )
			: $wpdb->get_results( $query );

		$labels = array();
		$data   = array();
		$colors = array( '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40' );
		foreach ( $results as $row ) {
			$labels[] = ucfirst( $row->platform );
			$data[]   = round( (float) $row->avg_rating, 2 );
		}

		wp_send_json_success(
			array(
				'labels' => $labels,
				'data'   => $data,
				'colors' => array_slice( $colors, 0, count( $labels ) ),
			)
		);
	}

	/**
	 * Platform volume distribution.
	 */
	public function wprevpro_ajax_analytics_platform_volume() {
		if ( ! $this->can_view_analytics() ) {
			wp_die( 'Insufficient permissions' );
		}

		global $wpdb;
		$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$built              = $this->build_analytics_where();
		$where_conditions   = $built['conditions'];
		$where_values       = $built['values'];
		$startdate          = $built['startdate'];
		$enddate            = $built['enddate'];

		if ( ! empty( $startdate ) && ! empty( $enddate ) ) {
			$where_conditions[] = 'DATE(created_time) BETWEEN %s AND %s';
			$where_values[]     = $startdate;
			$where_values[]     = $enddate;
		}

		$where_clause = ! empty( $where_conditions ) ? 'WHERE ' . implode( ' AND ', $where_conditions ) : '';
		$query        = "SELECT type as platform, COUNT(*) as review_count
			FROM {$reviews_table_name}
			{$where_clause}
			GROUP BY type
			ORDER BY review_count DESC";

		$results = ! empty( $where_values )
			? $wpdb->get_results( $wpdb->prepare( $query, $where_values ) )
			: $wpdb->get_results( $query );

		$labels = array();
		$data   = array();
		$colors = array( '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40' );
		foreach ( $results as $row ) {
			$labels[] = ucfirst( $row->platform );
			$data[]   = (int) $row->review_count;
		}

		wp_send_json_success(
			array(
				'labels' => $labels,
				'data'   => $data,
				'colors' => array_slice( $colors, 0, count( $labels ) ),
			)
		);
	}

	/**
	 * Rating trends over time.
	 */
	public function wprevpro_ajax_analytics_rating_trends() {
		if ( ! $this->can_view_analytics() ) {
			wp_die( 'Insufficient permissions' );
		}

		global $wpdb;
		$reviews_table_name = $wpdb->prefix . 'wptripadvisor_reviews';
		$built              = $this->build_analytics_where();
		$where_conditions   = $built['conditions'];
		$where_values       = $built['values'];
		$startdate          = $built['startdate'];
		$enddate            = $built['enddate'];

		if ( ! empty( $startdate ) && ! empty( $enddate ) ) {
			$where_conditions[] = 'created_time_stamp BETWEEN %d AND %d';
			$where_values[]     = strtotime( $startdate );
			$where_values[]     = strtotime( $enddate . ' 23:59:59' );
		}

		$where_clause = ! empty( $where_conditions ) ? 'WHERE ' . implode( ' AND ', $where_conditions ) : '';
		$query        = "SELECT
			DATE_FORMAT(DATE_SUB(created_time, INTERVAL WEEKDAY(created_time) DAY), '%Y-%m-%d') as week_start,
			AVG(rating) as avg_rating,
			COUNT(*) as review_count
			FROM {$reviews_table_name}
			{$where_clause}
			GROUP BY DATE_FORMAT(DATE_SUB(created_time, INTERVAL WEEKDAY(created_time) DAY), '%Y-%m-%d')
			ORDER BY week_start DESC
			LIMIT 12";

		$results = ! empty( $where_values )
			? $wpdb->get_results( $wpdb->prepare( $query, $where_values ) )
			: $wpdb->get_results( $query );

		if ( empty( $results ) ) {
			$fallback_query = "SELECT
				DATE(DATE_SUB(FROM_UNIXTIME(created_time_stamp), INTERVAL WEEKDAY(FROM_UNIXTIME(created_time_stamp)) DAY)) as week_start,
				AVG(rating) as avg_rating,
				COUNT(*) as review_count
				FROM {$reviews_table_name}
				{$where_clause}
				GROUP BY DATE(DATE_SUB(FROM_UNIXTIME(created_time_stamp), INTERVAL WEEKDAY(FROM_UNIXTIME(created_time_stamp)) DAY))
				ORDER BY week_start DESC
				LIMIT 12";
			$results = ! empty( $where_values )
				? $wpdb->get_results( $wpdb->prepare( $fallback_query, $where_values ) )
				: $wpdb->get_results( $fallback_query );
		}

		$range_weeks = 12;
		if ( ! empty( $startdate ) && ! empty( $enddate ) ) {
			$start_dt    = new DateTime( $startdate );
			$end_dt      = new DateTime( $enddate );
			$diff_days   = $end_dt->diff( $start_dt )->days;
			$range_weeks = max( 1, min( 52, (int) ceil( ( $diff_days + 1 ) / 7 ) ) );
		}

		$week_map = array();
		foreach ( $results as $row ) {
			$week_map[ $row->week_start ] = round( (float) $row->avg_rating, 2 );
		}

		$labels    = array();
		$data      = array();
		$week_keys = array();
		for ( $i = $range_weeks - 1; $i >= 0; $i-- ) {
			$week_start  = date( 'Y-m-d', strtotime( "-{$i} weeks Monday" ) );
			$week_keys[] = $week_start;
			$labels[]    = date( 'M j', strtotime( $week_start ) );
			$data[]      = isset( $week_map[ $week_start ] ) ? $week_map[ $week_start ] : null;
		}

		wp_send_json_success(
			array(
				'labels'    => $labels,
				'data'      => $data,
				'week_keys' => $week_keys,
			)
		);
	}
}
