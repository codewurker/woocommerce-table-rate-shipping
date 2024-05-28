<?php
/**
 * Blocks Integration class.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

class Blocks_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'woocommerce-table-rate-shipping';
	}

	/**
	 * When called invokes any initialization/setup for the integratidon.
	 */
	public function initialize() {
		$this->register_scripts();
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles(): array {
		return array( 'woocommerce-trs-abort-notices' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles(): array {
		return array();
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data(): array {
		return array();
	}

	/**
	 * Registers the scripts and styles for the integration.
	 */
	public function register_scripts() {

		foreach ( $this->get_script_handles() as $handle ) {
			$this->register_script( $handle );
		}
	}

	/**
	 * Register a script for the integration.
	 *
	 * @param string $handle Script handle.
	 */
	protected function register_script( string $handle ) {
		$script_path = $handle . '.js';
		$script_url  = WC_TABLE_RATE_SHIPPING_DIST_URL . $script_path;

		$script_asset_path = WC_TABLE_RATE_SHIPPING_DIST_DIR . $handle . '.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path // nosemgrep: audit.php.lang.security.file.inclusion-arg --- This is a safe file inclusion.
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( WC_TABLE_RATE_SHIPPING_DIST_DIR . $script_path ),
			);

		wp_register_script(
			$handle,
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			$handle,
			'woocommerce-table-rate-shipping',
			WC_TABLE_RATE_SHIPPING_MAIN_ABSPATH . '/languages'
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 *
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( string $file ): string {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return TABLE_RATE_SHIPPING_VERSION;
	}
}
