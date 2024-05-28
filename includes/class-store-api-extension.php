<?php
/**
 * Store_API_Extension class.
 *
 * A class to extend the store public API with Table Rate Shipping Abort Message functionality.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

/**
 * Store API Extension.
 */
class Store_API_Extension {
	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendSchema
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'woocommerce_table_rate_shipping';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::$extend = StoreApi::container()->get( ExtendSchema::class );
		self::extend_store();
	}

	/**
	 * Registers the data into each endpoint.
	 */
	public static function extend_store() {

		self::$extend->register_endpoint_data(
			array(
				'endpoint'        => CartSchema::IDENTIFIER,
				'namespace'       => self::IDENTIFIER,
				'data_callback'   => array( static::class, 'data' ),
				'schema_callback' => array( static::class, 'schema' ),
				'schema_type'     => ARRAY_A,
			)
		);
	}

	/**
	 * Store API extension data callback.
	 *
	 * @return array
	 */
	public static function data() {
		$abort = WC()->session->get( WC_Table_Rate_Shipping::$abort_key );
		$abort = is_array( $abort ) ? $abort : array();

		$packages       = WC()->cart->get_shipping_packages();
		$package_hashes = array();
		foreach ( $packages as $package ) {
			$package_hashes[] = WC_Table_Rate_Shipping::create_package_hash( $package );
		}

		return array(
			'abort_messages' => $abort,
			'package_hashes' => $package_hashes,
		);
	}

	/**
	 * Store API extension schema callback.
	 *
	 * @return array Registered schema.
	 */
	public static function schema() {
		return array(
			'abort_messages' => array(
				'description' => __( 'Abort messages from Table Rate Shipping.', 'woocommerce-table-rate-shipping' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'package_hashes' => array(
				'description' => __( 'Current package hashes.', 'woocommerce-table-rate-shipping' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	}
}
