<?php
/**
 * Plugin Name: WooCommerce Table Rate Shipping
 * Plugin URI: https://woocommerce.com/products/table-rate-shipping/
 * Description: Table rate shipping lets you define rates depending on location vs shipping class, price, weight, or item count.
 * Version: 3.2.1
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: woocommerce-table-rate-shipping
 * Copyright: © 2024 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Tested up to: 6.5
 * WC tested up to: 8.9
 * WC requires at least: 8.7
 *
 * Woo: 18718:3034ed8aff427b0f635fe4c86bbf008a
 *
 * @package woocommerce-shipping-table-rate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TABLE_RATE_SHIPPING_VERSION' ) ) {
	define( 'TABLE_RATE_SHIPPING_VERSION', '3.2.1' ); // WRCS: DEFINED_VERSION.
}

if ( ! defined( 'TABLE_RATE_SHIPPING_DEBUG' ) ) {
	define( 'TABLE_RATE_SHIPPING_DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG && ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY ) );
}

if ( ! defined( 'WC_TABLE_RATE_SHIPPING_MAIN_FILE' ) ) {
	define( 'WC_TABLE_RATE_SHIPPING_MAIN_FILE', __FILE__ );
}


if ( ! defined( 'WC_TABLE_RATE_SHIPPING_MAIN_ABSPATH' ) ) {
	define( 'WC_TABLE_RATE_SHIPPING_MAIN_ABSPATH', dirname( WC_TABLE_RATE_SHIPPING_MAIN_FILE ) . '/' );
}

if ( ! defined( 'WC_TABLE_RATE_SHIPPING_PLUGIN_DIR' ) ) {
	define( 'WC_TABLE_RATE_SHIPPING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WC_TABLE_RATE_SHIPPING_PLUGIN_URL' ) ) {
	define( 'WC_TABLE_RATE_SHIPPING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WC_TABLE_RATE_SHIPPING_DIST_DIR' ) ) {
	define( 'WC_TABLE_RATE_SHIPPING_DIST_DIR', WC_TABLE_RATE_SHIPPING_PLUGIN_DIR . 'dist/' );
}

if ( ! defined( 'WC_TABLE_RATE_SHIPPING_DIST_URL' ) ) {
	define( 'WC_TABLE_RATE_SHIPPING_DIST_URL', WC_TABLE_RATE_SHIPPING_PLUGIN_URL . 'dist/' );
}

// Require the main Shipping Per Product class.
if ( ! class_exists( 'WC_Table_Rate_Shipping' ) ) {
	require_once dirname( WC_TABLE_RATE_SHIPPING_MAIN_FILE ) . '/includes/class-wc-table-rate-shipping.php';
}

new WC_Table_Rate_Shipping();

/**
 * Callback function for loading an instance of this method.
 *
 * @todo: it seems this function is not used anywhere
 *
 * @param mixed $instance Table Rate instance.
 * @return WC_Shipping_Table_Rate
 */
function woocommerce_get_shipping_method_table_rate( $instance = false ) {
	return new WC_Shipping_Table_Rate( $instance );
}
