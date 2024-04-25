<?php
/**
 * Functions collection.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

require_once WC_TABLE_RATE_SHIPPING_MAIN_ABSPATH . 'includes/class-helpers.php';

use WooCommerce\Shipping\Table_Rate\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The wc_table_rate_admin_shipping_rows function.
 *
 * @param WC_Shipping_Table_Rate $instance Current instance.
 */
function wc_table_rate_admin_shipping_rows( $instance ) {
	wp_enqueue_script( 'woocommerce_shipping_table_rate_rows' );

	// Get shipping classes.
	$shipping_classes = get_terms(
		array(
			'taxonomy'   => 'product_shipping_class',
			'hide_empty' => false,
		)
	);
	?>
	<table id="shipping_rates" class="shippingrows widefat" cellspacing="0" style="position:relative;">
		<thead>
			<tr>
				<th class="check-column"><input type="checkbox"></th>
				<?php if ( is_array( $shipping_classes ) && count( $shipping_classes ) ) : ?>
					<th>
						<?php esc_html_e( 'Shipping Class', 'woocommerce-table-rate-shipping' ); ?>
						<a class="tips" data-tip="<?php esc_attr_e( 'Shipping class this rate applies to.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
					</th>
				<?php endif; ?>
				<th>
					<?php esc_html_e( 'Condition', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Condition vs. destination', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th>
					<?php esc_html_e( 'Min&ndash;Max', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Bottom and top range for the selected condition. ', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th width="1%" class="checkbox">
					<?php esc_html_e( 'Break', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Break at this point. For per-order rates, no rates other than this will be offered. For calculated rates, this will stop any further rates being matched.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th width="1%" class="checkbox">
					<?php esc_html_e( 'Abort', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Enable this option to disable all rates/this shipping method if this row matches any item/line/class being quoted.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th class="cost">
					<?php esc_html_e( 'Row cost', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Cost for shipping the order.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th class="cost cost_per_item">
					<?php esc_html_e( 'Item cost', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Cost per item.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th class="cost cost_per_weight">
					<?php echo esc_html( get_option( 'woocommerce_weight_unit' ) . ' ' . __( 'cost', 'woocommerce-table-rate-shipping' ) ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Cost per weight unit.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
				<th class="cost cost_percent">
					<?php esc_html_e( '% cost', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Percentage of total to charge.', 'woocommerce-table-rate-shipping' ); ?>">[?]</a></th>
				<th class="shipping_label">
					<?php esc_html_e( 'Label', 'woocommerce-table-rate-shipping' ); ?>
					<a class="tips" data-tip="<?php esc_attr_e( 'Label for the shipping method which the user will be presented. ', 'woocommerce-table-rate-shipping' ); ?>">[?]</a>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan="2"><a href="#" class="add-rate button button-primary"><?php esc_html_e( 'Add Shipping Rate', 'woocommerce-table-rate-shipping' ); ?></a></th>
				<th colspan="9"><span class="description"><?php esc_html_e( 'Define your table rates here in order of priority.', 'woocommerce-table-rate-shipping' ); ?></span> <a href="#" class="dupe button"><?php esc_html_e( 'Duplicate selected rows', 'woocommerce-table-rate-shipping' ); ?></a> <a href="#" class="remove button"><?php esc_html_e( 'Delete selected rows', 'woocommerce-table-rate-shipping' ); ?></a></th>
			</tr>
		</tfoot>
		<?php
			$normalized_rates = function_exists( 'wc_esc_json' ) ? wc_esc_json( wp_json_encode( $instance->get_normalized_shipping_rates() ) ) : _wp_specialchars( wp_json_encode( $instance->get_normalized_shipping_rates() ), ENT_QUOTES, 'UTF-8', true );
		?>
		<tbody class="table_rates" data-rates="<?php echo esc_attr( $normalized_rates ); ?>"></tbody>
	</table>
	<script type="text/template" id="tmpl-table-rate-shipping-row-template">
		<tr class="table_rate">
			<td class="check-column">
				<input type="checkbox" name="select" />
				<input type="hidden" class="rate_id" name="rate_id[{{{ data.index }}}]" value="{{{ data.rate.rate_id }}}" />
			</td>
			<?php if ( is_array( $shipping_classes ) && count( $shipping_classes ) ) : ?>
				<td>
					<select class="select" name="shipping_class[{{{ data.index }}}]" style="min-width:100px;">
						<option value="" <# if ( "" === data.rate.rate_class ) { #>selected="selected"<# } #>><?php esc_html_e( 'Any class', 'woocommerce-table-rate-shipping' ); ?></option>
						<option value="0" <# if ( "0" === data.rate.rate_class ) { #>selected="selected"<# } #>><?php esc_html_e( 'No class', 'woocommerce-table-rate-shipping' ); ?></option>
						<?php foreach ( $shipping_classes as $class ) : ?>
							<option value="<?php echo esc_attr( $class->term_id ); ?>" <# if ( "<?php echo esc_attr( $class->term_id ); ?>" === data.rate.rate_class ) { #>selected="selected"<# } #>><?php echo esc_html( $class->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			<?php endif; ?>
			<td>
				<select class="select" name="shipping_condition[{{{ data.index }}}]" style="min-width:100px;">
					<option value="" <# if ( "" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'None', 'woocommerce-table-rate-shipping' ); ?></option>
					<option value="price" <# if ( "price" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Price', 'woocommerce-table-rate-shipping' ); ?></option>
					<option value="weight" <# if ( "weight" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Weight', 'woocommerce-table-rate-shipping' ); ?></option>
					<option value="items" <# if ( "items" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Item count', 'woocommerce-table-rate-shipping' ); ?></option>
					<?php if ( count( $shipping_classes ) ) : ?>
						<option value="items_in_class" <# if ( "items_in_class" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Item count (same class)', 'woocommerce-table-rate-shipping' ); ?></option>
					<?php endif; ?>
				</select>
			</td>
			<td class="minmax">
				<input type="text" class="text <# if ( data.rate.is_conflicting_min ) { #>error<# } #>" value="{{{ data.rate.rate_min }}}" name="shipping_min[{{{ data.index }}}]" placeholder="<?php esc_attr_e( 'n/a', 'woocommerce-table-rate-shipping' ); ?>" size="4" /><input type="text" class="text <# if ( data.rate.is_conflicting_max ) { #>error<# } #>" value="{{{ data.rate.rate_max }}}" name="shipping_max[{{{ data.index }}}]" placeholder="<?php esc_attr_e( 'n/a', 'woocommerce-table-rate-shipping' ); ?>" size="4" />
				<# if ( data.rate.is_conflicting_min || data.rate.is_conflicting_max  ) { #>
					<a class="tips" data-tip="{{{data.rate.is_conflicting_min}}} {{{data.rate.is_conflicting_max}}}">!</a>
				<# } #>
			</td>
			<td width="1%" class="checkbox"><input type="checkbox" <# if ( '1' === data.rate.rate_priority ) { #>checked="checked"<# } #> class="checkbox" name="shipping_priority[{{{ data.index }}}]" /></td>
			<td width="1%" class="checkbox"><input type="checkbox" <# if ( '1' === data.rate.rate_abort ) { #>checked="checked"<# } #> class="checkbox" name="shipping_abort[{{{ data.index }}}]" /></td>
			<td colspan="4" class="abort_reason">
				<input type="text" class="text" value="{{{ data.rate.rate_abort_reason }}}" placeholder="<?php esc_attr_e( 'Optional abort reason text', 'woocommerce-table-rate-shipping' ); ?>" name="shipping_abort_reason[{{{ data.index }}}]" />
			</td>
			<td class="cost">
				<input type="text" class="text" value="{{{ data.rate.rate_cost }}}" name="shipping_cost[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'woocommerce-table-rate-shipping' ); ?>" size="4" />
			</td>
			<td class="cost cost_per_item">
				<input type="text" class="text" value="{{{ data.rate.rate_cost_per_item }}}" name="shipping_per_item[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'woocommerce-table-rate-shipping' ); ?>" size="4" />
			</td>
			<td class="cost cost_per_weight">
				<input type="text" class="text" value="{{{ data.rate.rate_cost_per_weight_unit }}}" name="shipping_cost_per_weight[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'woocommerce-table-rate-shipping' ); ?>" size="4" />
			</td>
			<td class="cost cost_percent">
				<input type="text" class="text" value="{{{ data.rate.rate_cost_percent }}}" name="shipping_cost_percent[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'woocommerce-table-rate-shipping' ); ?>" size="4" />
			</td>
			<td class="shipping_label">
				<input type="text" class="text" value="{{{ data.rate.rate_label }}}" name="shipping_label[{{{ data.index }}}]" size="8" />
			</td>
		</tr>
	</script>
	<?php
	wc_prices_include_tax();
}

/**
 * The wc_table_rate_admin_shipping_class_priorities function.
 *
 * @param int $shipping_method_id Shipping Method Id.
 *
 * @return void
 */
function wc_table_rate_admin_shipping_class_priorities( $shipping_method_id ) {
	$classes = WC()->shipping->get_shipping_classes();
	if ( ! $classes ) :
		echo '<p class="description">' . esc_html__( 'No shipping classes exist - you can ignore this option :)', 'woocommerce-table-rate-shipping' ) . '</p>';
	else :
		$priority = get_option( 'woocommerce_table_rate_default_priority_' . $shipping_method_id, 10 );
		?>
		<table class="widefat shippingrows" style="position:relative;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Class', 'woocommerce-table-rate-shipping' ); ?></th>
					<th><?php esc_html_e( 'Priority', 'woocommerce-table-rate-shipping' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">
						<span class="description per_order">
							<?php
							echo wp_kses_post( __( 'When calculating shipping, the cart contents will be <strong>searched for all shipping classes</strong>. If all product shipping classes are <strong>identical</strong>, the corresponding class will be used.<br/><strong>If there are a mix of classes</strong> then the class with the <strong>lowest number priority</strong> (defined above) will be used.', 'woocommerce-table-rate-shipping' ) );
							?>
						</span>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Default', 'woocommerce-table-rate-shipping' ); ?></th>
					<td><input type="text" size="2" name="woocommerce_table_rate_default_priority" value="<?php echo esc_attr( $priority ); ?>" /></td>
				</tr>
				<?php
				$woocommerce_table_rate_priorities = get_option( 'woocommerce_table_rate_priorities_' . $shipping_method_id );
				foreach ( $classes as $class ) {
					$priority = ( isset( $woocommerce_table_rate_priorities[ $class->slug ] ) ) ? $woocommerce_table_rate_priorities[ $class->slug ] : 10;

					echo '<tr><th>' . esc_html( $class->name ) . '</th><td><input type="text" value="' . esc_attr( $priority ) . '" size="2" name="woocommerce_table_rate_priorities[' . esc_attr( $class->slug ) . ']" /></td></tr>';
				}
				?>
			</tbody>
		</table>
		<?php
	endif;
}

/**
 * WC_table_rate_admin_shipping_rows_process function.
 *
 * @param int $shipping_method_id Shipping Method Id.
 *
 * @return void
 */
function wc_table_rate_admin_shipping_rows_process( $shipping_method_id ) {
	global $wpdb;
	// phpcs:disable WordPress.Security.NonceVerification.Missing --- callable only on admin page

	// Clear cache.
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%')" );

	// Save class priorities.
	if ( empty( $_POST['woocommerce_table_rate_calculation_type'] ) ) {

		if ( isset( $_POST['woocommerce_table_rate_priorities'] ) ) {
			$priorities = array_map( 'intval', (array) $_POST['woocommerce_table_rate_priorities'] );
			update_option( 'woocommerce_table_rate_priorities_' . $shipping_method_id, $priorities );
		}

		if ( isset( $_POST['woocommerce_table_rate_default_priority'] ) ) {
			update_option( 'woocommerce_table_rate_default_priority_' . $shipping_method_id, intval( $_POST['woocommerce_table_rate_default_priority'] ) );
		}
	} else {
		delete_option( 'woocommerce_table_rate_priorities_' . $shipping_method_id );
		delete_option( 'woocommerce_table_rate_default_priority_' . $shipping_method_id );
	}

	$formatted_rate_values = Helpers::get_formatted_table_rate_row_values_from_postdata();

	if ( empty( $formatted_rate_values ) ) {
		return;
	}

	$db_value_format = array(
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%d',
		'%d',
		'%d',
		'%s',
	);

	foreach ( $formatted_rate_values as $idx => $rate_values ) {
		$db_values = array(
			'rate_class'                => $rate_values['rate_class'],
			'rate_condition'            => sanitize_title( $rate_values['rate_condition'] ),
			'rate_min'                  => $rate_values['rate_min'],
			'rate_max'                  => $rate_values['rate_max'],
			'rate_cost'                 => $rate_values['rate_cost'],
			'rate_cost_per_item'        => $rate_values['rate_cost_per_item'],
			'rate_cost_per_weight_unit' => $rate_values['rate_cost_per_weight_unit'],
			'rate_cost_percent'         => $rate_values['rate_cost_percent'],
			'rate_label'                => $rate_values['rate_label'],
			'rate_priority'             => $rate_values['rate_priority'],
			'rate_order'                => $idx,
			'shipping_method_id'        => $shipping_method_id,
			'rate_abort'                => $rate_values['rate_abort'],
			'rate_abort_reason'         => $rate_values['rate_abort_reason'],
		);

		if ( $rate_values['rate_id'] > 0 ) {

			// Update row.
			$wpdb->update(
				$wpdb->prefix . 'woocommerce_shipping_table_rates',
				$db_values,
				array(
					'rate_id' => $rate_values['rate_id'],
				),
				$db_value_format,
				array(
					'%d',
				)
			);

		} else {

			// Insert row.
			$wpdb->insert(
				$wpdb->prefix . 'woocommerce_shipping_table_rates',
				$db_values,
				$db_value_format
			);
		}
	}
}
