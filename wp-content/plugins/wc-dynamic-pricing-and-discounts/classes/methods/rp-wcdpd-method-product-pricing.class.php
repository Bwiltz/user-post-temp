<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method')) {
    require_once('rp-wcdpd-method.class.php');
}

/**
 * Product Pricing Method
 *
 * @class RP_WCDPD_Method_Product_Pricing
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing')) {

abstract class RP_WCDPD_Method_Product_Pricing extends RP_WCDPD_Method
{
    protected $context = 'product_pricing';

    /**
     * Group quantities of matching cart items
     *
     * @access public
     * @param array $cart_items
     * @param array $rule
     * @return array
     */
    public function group_quantities($cart_items, $rule)
    {
        $quantities = array();

        // Get Quantities Based On method
        $based_on = $rule['quantities_based_on'];

        // Filter out cart items that are not affected by this rule so we don't count them
        $cart_items = RP_WCDPD_Product_Pricing::filter_items_by_rules($cart_items, array($rule));

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Get quantity
            $quantity = RP_WCDPD_Helper::cart_item_is_bundle($cart_item) ? 0 : $cart_item['quantity'];

            // Get absolute product id (i.e. parent product id for variations)
            $product_id = RP_WCDPD_WC_Product::product_get_absolute_id($cart_item['data']);

            // Individual Products - Each individual product
            // Individual Products - Each individual variation (variation not specified)
            if ($based_on === 'individual__product' || ($based_on === 'individual__variation' && empty($cart_item['variation_id']))) {
                $quantities[$product_id][$cart_item_key] = $quantity;
            }

            // Individual Products - Each individual variation (variation specified)
            else if ($based_on === 'individual__variation') {
                $quantities[$cart_item['variation_id']][$cart_item_key] = $quantity;
            }

            // Individual Products - Each individual cart line item
            else if ($based_on === 'individual__configuration') {
                $quantities[$cart_item_key][$cart_item_key] = $quantity;
            }

            // All Matched Products - Quantities added up by category
            else if ($based_on === 'cumulative__categories') {

                // Get category ids
                $categories = RightPress_Helper::get_wc_product_category_ids_from_product_ids(array($product_id));

                // Iterate over categories and add quantities
                foreach ($categories as $category_id) {
                    $quantities[$category_id][$cart_item_key] = $quantity;
                }
            }

            // All Matched Products - All quantities added up
            else if ($based_on === 'cumulative__all') {
                $quantities['_all'][$cart_item_key] = $quantity;
            }
        }

        // Return quantities
        return $quantities;
    }

    /**
     * Get reference amount
     *
     * @access public
     * @param array $adjustment
     * @param float $base_amount
     * @param int $quantity
     * @param object $product
     * @param array $cart_item
     * @return mixed
     */
    public function get_reference_amount($adjustment, $base_amount = null, $quantity = 1, $product = null, $cart_item = null)
    {
        // Get rule selection method
        $selection_method = RP_WCDPD_Settings::get($this->context . '_rule_selection_method');

        // Calculate reference amount
        if (in_array($selection_method, array('smaller_price', 'bigger_price'), true)) {

            // Get adjusted amount
            $prices = RP_WCDPD_Controller_Methods_Product_Pricing::generate_prices_array($base_amount, $quantity);
            $prices = $this->apply_adjustment_to_prices($prices, $adjustment);
            $adjusted_amount = RP_WCDPD_Controller_Methods_Product_Pricing::get_price_from_prices_array($prices, $base_amount, $product, $cart_item);

            // Calculate reference amount
            return (float) ($base_amount - $adjusted_amount);
        }
        // Reference amount is not needed
        else {
            return null;
        }
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

        // Do not process identical prices multiple times
        $processed = array();

        // Iterate over prices
        foreach ($prices as $index => $price) {

            // Get correct processed price key
            $processed_key = (string) $price['adjusted'];

            // Price not yet processed
            if (!isset($processed[$processed_key])) {

                // Get adjusted amount
                $adjusted = RP_WCDPD_Pricing::adjust_amount($price['adjusted'], $rule['pricing_method'], $rule['pricing_value']);

                // Round adjusted amount to get predictable results
                $adjusted = RP_WCDPD_Pricing::round($adjusted);

                // Allow developers to override
                $adjusted = apply_filters('rp_wcdpd_product_pricing_adjusted_unit_price', $adjusted, $price['adjusted'], $adjustment);

                // Add to processed prices array
                $processed[$processed_key] = (float) $adjusted;
            }

            // Set adjusted price
            $this->set_adjusted_price($prices, $processed[$processed_key], $index, $adjustment, $cart_item_key);
        }

        return $prices;
    }

    /**
     * Set adjusted price
     *
     * @access public
     * @param array $prices
     * @param float $price
     * @param int $index
     * @param array $adjustment
     * @param string $cart_item_key
     * @return void
     */
    public function set_adjusted_price(&$prices, $price, $index, $adjustment, $cart_item_key = null)
    {
        // Check if limit could be applied
        if (RP_WCDPD_Settings::get('product_pricing_total_limit') && $cart_item_key !== null && !RP_WCDPD_Controller_Methods_Product_Pricing::running_test()) {

            // Reference original price
            $original_price = $prices[$index]['original'];

            // Get last limited price
            $last_limited_price = isset($prices[$index]['last_limited']) ? $prices[$index]['last_limited'] : $original_price;

            // Calculate discount amount
            $discount_amount = $last_limited_price - $price;

            // Check if adjustment is discount
            if ($discount_amount > 0) {

                // Limit discount amount
                $limited_discount_amount = RP_WCDPD_Limit_Product_Pricing::limit_discount($discount_amount, $original_price, $cart_item_key, $index);

                // Recalculate price
                $price = $last_limited_price - $limited_discount_amount;

                // Sanity check
                $price = $price < 0 ? 0 : $price;

                // Set last limited price
                $prices[$index]['last_limited'] = $price;
            }
        }

        // Add price to prices array
        $prices[$index]['adjusted'] = $price;

        // Set adjustment
        $prices[$index]['adjustments'][$adjustment['rule']['uid']] = $adjustment;
    }




}
}
