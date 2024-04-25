<?php
/**
 * WC_Table_Rate_Shipping class file.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

namespace WooCommerce\Shipping\Table_Rate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Class.
 */
class Helpers {

	/**
	 * Get the shipping method database table name.
	 *
	 * @return string
	 */
	public static function get_db_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'woocommerce_shipping_table_rates';
	}

	/**
	 * Get raw shipping rates from the DB.
	 *
	 * Optional filter helper for integration with other plugins.
	 *
	 * @param int    $instance_id Shipping method instance ID.
	 * @param string $output      Output format.
	 *
	 * @return mixed
	 */
	public static function get_shipping_rates( int $instance_id, string $output = OBJECT ) {
		global $wpdb;

		$table_name = self::get_db_table_name();

		$rates = $wpdb->get_results(
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table_name WHERE shipping_method_id = %s ORDER BY rate_order;",
				$instance_id
			),
			$output
		);

		return apply_filters( 'woocommerce_table_rate_get_shipping_rates', $rates );
	}

	/**
	 * Loop through the shipping rates and add flags to indicate if any rate's conditions conflict with another rate's conditions.
	 *
	 * @param array $shipping_rates array of shipping rates.
	 *
	 * @return array.
	 */
	public static function flag_conflicting_shipping_rates( array $shipping_rates ): array {
		// Loop through each rate.
		$number_of_rates = count( $shipping_rates );
		for ( $i = 0; $i < $number_of_rates; $i++ ) {
			$rate1 = $shipping_rates[ $i ];

			// Check if min rate is bigger than max rate.
			if ( '' !== $rate1['rate_max'] && $rate1['rate_min'] > $rate1['rate_max'] ) {
				$shipping_rates[ $i ]['is_conflicting_max'] = __( 'Max condition can\'t be less than Min.' );
				continue;
			}

			// Compare the current rate with other rates.
			for ( $j = $i + 1; $j < $number_of_rates; $j++ ) {
				$rate2 = $shipping_rates[ $j ];

				// Check if the rates not the same class and condition.
				if (
					$rate1['rate_class'] !== $rate2['rate_class'] ||
					$rate1['rate_condition'] !== $rate2['rate_condition']
				) {
					continue;
				}

				// Rate min if it's empty should represent 0.
				// Using `floatval()` to change empty string to be 0.
				$rate1['rate_min'] = floatval( $rate1['rate_min'] );
				$rate2['rate_min'] = floatval( $rate2['rate_min'] );

				// If the max is empty, it represent an infinite.
				if ( '' === $rate1['rate_max'] ) {
					$rate1['rate_max'] = PHP_FLOAT_MAX;
				}

				// If the max is empty, it represent an infinite.
				if ( '' === $rate2['rate_max'] ) {
					$rate2['rate_max'] = PHP_FLOAT_MAX;
				}

				if (
					( $rate1['rate_min'] >= $rate2['rate_min'] && $rate1['rate_min'] <= $rate2['rate_max'] ) ||
					( $rate1['rate_max'] >= $rate2['rate_min'] && $rate1['rate_max'] <= $rate2['rate_max'] ) ||
					( $rate2['rate_min'] >= $rate1['rate_min'] && $rate2['rate_min'] <= $rate1['rate_max'] ) ||
					( $rate2['rate_max'] >= $rate1['rate_min'] && $rate2['rate_max'] <= $rate1['rate_max'] )
				) {
					// translators: %d is row ID.
					$shipping_rates[ $i ]['is_conflicting_max'] = sprintf( esc_attr__( 'Max value is overlapping with min value from row %d.' ), esc_attr( $j + 1 ) );
					// translators: %d is row ID.
					$shipping_rates[ $j ]['is_conflicting_min'] = sprintf( esc_attr__( 'Min value is overlapping with max value from row %d.' ), esc_attr( $i + 1 ) );
				}
			}
		}

		return $shipping_rates;
	}

	/**
	 * Get conflicting shipping rates.
	 *
	 * @param array $flagged_shipping_rates array of flagged shipping rates.
	 *
	 * @return array.
	 */
	public static function get_conflicting_shipping_rates( array $flagged_shipping_rates ): array {

		if ( empty( $flagged_shipping_rates ) ) {
			return array();
		}

		$conflicting_shipping_rates = array();

		foreach ( $flagged_shipping_rates as $rate ) {
			if ( ( isset( $rate['is_conflicting_min'] ) && $rate['is_conflicting_min'] ) || ( isset( $rate['is_conflicting_max'] ) && $rate['is_conflicting_max'] ) ) {
				$conflicting_shipping_rates[] = $rate;
			}
		}

		return $conflicting_shipping_rates;
	}

	/**
	 * Get conflicting shipping rates by instance ID.
	 *
	 * @param int $instance_id Shipping method instance ID.
	 *
	 * @return array.
	 */
	public static function get_conflicting_shipping_rates_by_instance_id( int $instance_id ): array {
		$shipping_rates         = self::get_shipping_rates( $instance_id, ARRAY_A );
		$flagged_shipping_rates = self::flag_conflicting_shipping_rates( $shipping_rates );

		return self::get_conflicting_shipping_rates( $flagged_shipping_rates );
	}

	/**
	 * Get an array of formatted rate values from the $_POST data.
	 * Returns false if $_POST is empty.
	 *
	 * @return array|false
	 */
	public static function get_formatted_table_rate_row_values_from_postdata() {

		// We are not checking nonce here because we aren't saving anything.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST ) ) {
			return false;
		}

		$formatted_rate_values = array();

		$precision = function_exists( 'wc_get_rounding_precision' ) ? wc_get_rounding_precision() : 4;

		// Save rates.
		$rate_ids                 = isset( $_POST['rate_id'] ) ? array_map( 'intval', wp_unslash( $_POST['rate_id'] ) ) : array();
		$shipping_class           = isset( $_POST['shipping_class'] ) ? wc_clean( wp_unslash( $_POST['shipping_class'] ) ) : array();
		$shipping_condition       = isset( $_POST['shipping_condition'] ) ? wc_clean( wp_unslash( $_POST['shipping_condition'] ) ) : array();
		$shipping_min             = isset( $_POST['shipping_min'] ) ? wc_clean( wp_unslash( $_POST['shipping_min'] ) ) : array();
		$shipping_max             = isset( $_POST['shipping_max'] ) ? wc_clean( wp_unslash( $_POST['shipping_max'] ) ) : array();
		$shipping_cost            = isset( $_POST['shipping_cost'] ) ? wc_clean( wp_unslash( $_POST['shipping_cost'] ) ) : array();
		$shipping_per_item        = isset( $_POST['shipping_per_item'] ) ? wc_clean( wp_unslash( $_POST['shipping_per_item'] ) ) : array();
		$shipping_cost_per_weight = isset( $_POST['shipping_cost_per_weight'] ) ? wc_clean( wp_unslash( $_POST['shipping_cost_per_weight'] ) ) : array();
		$cost_percent             = isset( $_POST['shipping_cost_percent'] ) ? wc_clean( wp_unslash( $_POST['shipping_cost_percent'] ) ) : array();
		$shipping_label           = isset( $_POST['shipping_label'] ) ? wc_clean( wp_unslash( $_POST['shipping_label'] ) ) : array();
		$shipping_priority        = isset( $_POST['shipping_priority'] ) ? wc_clean( wp_unslash( $_POST['shipping_priority'] ) ) : array();
		$shipping_abort           = isset( $_POST['shipping_abort'] ) ? wc_clean( wp_unslash( $_POST['shipping_abort'] ) ) : array();
		$shipping_abort_reason    = isset( $_POST['shipping_abort_reason'] ) ? wc_clean( wp_unslash( $_POST['shipping_abort_reason'] ) ) : array();
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Get max key.
		$max_key = ( $rate_ids ) ? max( array_keys( $rate_ids ) ) : 0;

		for ( $i = 0; $i <= $max_key; $i++ ) {

			if ( ! isset( $rate_ids[ $i ] ) ) {
				continue;
			}

			$rate_id                   = $rate_ids[ $i ];
			$rate_class                = isset( $shipping_class[ $i ] ) ? $shipping_class[ $i ] : '';
			$rate_condition            = $shipping_condition[ $i ];
			$rate_min                  = isset( $shipping_min[ $i ] ) ? $shipping_min[ $i ] : '';
			$rate_max                  = isset( $shipping_max[ $i ] ) ? $shipping_max[ $i ] : '';
			$rate_cost                 = isset( $shipping_cost[ $i ] ) ? wc_format_decimal( $shipping_cost[ $i ], $precision, true ) : '';
			$rate_cost_per_item        = isset( $shipping_per_item[ $i ] ) ? wc_format_decimal( $shipping_per_item[ $i ], $precision, true ) : '';
			$rate_cost_per_weight_unit = isset( $shipping_cost_per_weight[ $i ] ) ? wc_format_decimal( $shipping_cost_per_weight[ $i ], $precision, true ) : '';
			$rate_cost_percent         = isset( $cost_percent[ $i ] ) ? wc_format_decimal( str_replace( '%', '', $cost_percent[ $i ] ), $precision, true ) : '';
			$rate_label                = isset( $shipping_label[ $i ] ) ? $shipping_label[ $i ] : '';
			$rate_priority             = isset( $shipping_priority[ $i ] ) ? 1 : 0;
			$rate_abort                = isset( $shipping_abort[ $i ] ) ? 1 : 0;
			$rate_abort_reason         = isset( $shipping_abort_reason[ $i ] ) ? $shipping_abort_reason[ $i ] : '';

			// Format min and max.
			switch ( $rate_condition ) {
				case 'weight':
				case 'price':
					if ( $rate_min ) {
						$rate_min = wc_format_decimal( $rate_min, $precision, true );
					}
					if ( $rate_max ) {
						$rate_max = wc_format_decimal( $rate_max, $precision, true );
					}
					break;
				case 'items':
				case 'items_in_class':
					if ( $rate_min ) {
						$rate_min = round( $rate_min );
					}
					if ( $rate_max ) {
						$rate_max = round( $rate_max );
					}
					break;
				default:
					$rate_min = '';
					$rate_max = '';
					break;
			}

			$formatted_rate_values[ $i ] = array(
				'rate_id'                   => $rate_id,
				'rate_class'                => $rate_class,
				'rate_condition'            => $rate_condition,
				'rate_min'                  => $rate_min,
				'rate_max'                  => $rate_max,
				'rate_cost'                 => $rate_cost,
				'rate_cost_per_item'        => $rate_cost_per_item,
				'rate_cost_per_weight_unit' => $rate_cost_per_weight_unit,
				'rate_cost_percent'         => $rate_cost_percent,
				'rate_label'                => $rate_label,
				'rate_priority'             => $rate_priority,
				'rate_abort'                => $rate_abort,
				'rate_abort_reason'         => $rate_abort_reason,
			);
		}

		return $formatted_rate_values;
	}
}
