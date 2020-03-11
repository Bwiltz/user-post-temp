<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing')) {
    require_once('rp-wcdpd-method-product-pricing.class.php');
}

/**
 * Product Pricing Method: Volume
 *
 * @class RP_WCDPD_Method_Product_Pricing_Volume
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Volume')) {

abstract class RP_WCDPD_Method_Product_Pricing_Volume extends RP_WCDPD_Method_Product_Pricing
{
    protected $group_key        = 'volume';
    protected $group_position   = 20;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->hook_group();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return __('Volume', 'rp_wcdpd');
    }

    /**
     * Get cart item adjustments by rule
     *
     * @access public
     * @param array $rule
     * @param array $cart_items
     * @return array
     */
    public function get_adjustments($rule, $cart_items = null)
    {
        $adjustments = array();

        // No quantity ranges defined
        if (empty($rule['quantity_ranges'])) {
            return $adjustments;
        }

        // Get cart item quantities allocated to quantity ranges
        $allocated_quantities = $this->get_quantities_allocated_to_quantity_ranges($cart_items, $rule);

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Check if rule applies to current cart item
            // Note: conditions are not checked here as they were checked when fetching applicable quantity ranges, if cart item is not there - conditions do not match
            if (isset($allocated_quantities[$cart_item_key])) {

                // Make sure that rule does not get applied multiple times
                if (!RP_WCDPD_Controller_Methods_Product_Pricing::is_already_processed($rule['uid'], $cart_item_key)) {

                    // Get product base price
                    $base_price = RP_WCDPD_Pricing::get_product_base_price($cart_item['data']);

                    // Add adjustment to main array
                    $adjustments[$cart_item_key] = array(
                        'rule'              => $rule,
                        'quantity_ranges'   => $allocated_quantities[$cart_item_key],
                        'reference_amount'  => $this->get_reference_amount(
                            array(
                                'quantity_ranges' => $allocated_quantities[$cart_item_key],
                                'rule'              => $rule,
                            ), $base_price, $cart_item['quantity'], $cart_item['data'], $cart_item
                        ),
                    );
                }
            }
        }

        return $adjustments;
    }

    /**
     * Get cart item quantities allocated to quantity ranges
     *
     * @access public
     * @param array $cart_items
     * @param array $rule
     * @return array
     */
    public function get_quantities_allocated_to_quantity_ranges($cart_items, $rule)
    {
        $ranges = array();

        // Group quantities
        $quantity_groups = $this->group_quantities($cart_items, $rule);

        // Iterate over quantity groups
        foreach ($quantity_groups as $quantity_group_key => $quantity_group) {

            // Get matching quantity range keys with allocated cart item quantities
            $quantity_range_keys_with_quantities = $this->get_quantity_ranges_with_allocated_quantities($rule, $quantity_group);

            // Iterate over quantity range keys with quantities
            foreach ($quantity_range_keys_with_quantities as $quantity_range_key => $cart_items_with_quantities) {

                // Iterate over cart items with quantities
                foreach ($cart_items_with_quantities as $cart_item_key => $quantity) {
                    $ranges[$cart_item_key][$quantity_range_key] = $quantity;
                }
            }
        }

        return $ranges;
    }

    /**
     * Apply adjustment to prices
     *
     * @access public
     * @param array $prices
     * @param array $adjustment
     * @param string $cart_item_key
     * @return array
     */
    public function apply_adjustment_to_prices($prices, $adjustment, $cart_item_key = null)
    {
        // Reference rule
        $rule = $adjustment['rule'];

        // Make a copy of prices array so we can unset items from it
        $prices_copy = $prices;

        // Do not process identical prices multiple times
        $processed = array();

        // Iterate over quantity ranges
        foreach ($adjustment['quantity_ranges'] as $quantity_range_key => $quantity) {

            // Make a copy of quantity so we can decrease it
            $quantity_copy = $quantity;

            // Reference quantity range
            $quantity_range = $rule['quantity_ranges'][$quantity_range_key];

            // Iterate over prices
            foreach ($prices_copy as $index => $price) {

                // Get correct processed price key
                $processed_key = $price['adjusted'] . '_' . $quantity_range['pricing_method'] . '_' . $quantity_range['pricing_value'];

                // Price not yet processed
                if (!isset($processed[$processed_key])) {

                    // Special handling - pricing is set per range
                    if ($quantity_range['pricing_method'] === 'fixed__price_per_range') {

                        // Get pricing value
                        $pricing_value = RightPress_Helper::get_amount_in_currency($quantity_range['pricing_value'], array('aelia'));

                        // Convert price per range to price per quantity unit
                        $adjusted = $pricing_value / $quantity;
                    }
                    // Regular handling - pricing is set per quantity unit
                    else {

                        // Get adjusted amount
                        $adjusted = RP_WCDPD_Pricing::adjust_amount($price['adjusted'], $quantity_range['pricing_method'], $quantity_range['pricing_value']);
                    }

                    // Round adjusted amount to get predictable results
                    $adjusted = RP_WCDPD_Pricing::round($adjusted);

                    // Allow developers to override
                    $adjusted = apply_filters('rp_wcdpd_product_pricing_adjusted_unit_price', $adjusted, $price['adjusted'], $adjustment, array('quantity_range' => $quantity_range));

                    // Add to processed prices array
                    $processed[$processed_key] = (float) $adjusted;
                }

                // Set adjusted price
                $this->set_adjusted_price($prices, $processed[$processed_key], $index, $adjustment, $cart_item_key);

                // Do not discount the same unit in the next quantity range
                unset($prices_copy[$index]);

                // Decrease quantity
                $quantity_copy--;

                // Nothing left in this quantity range
                if (!$quantity_copy) {
                    break;
                }
            }
        }

        return $prices;
    }



}
}
