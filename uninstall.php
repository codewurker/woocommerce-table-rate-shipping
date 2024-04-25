<?php
/**
 * Uninstall file.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {

	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}woocommerce_shipping_table_rates" );
}
