<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity')) {
    require_once('rp-wcdpd-method-product-pricing-quantity.class.php');
}

/**
 * Product Pricing Method: Group
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity_Group
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_Group')) {

abstract class RP_WCDPD_Method_Product_Pricing_Quantity_Group extends RP_WCDPD_Method_Product_Pricing_Quantity
{
    protected $group_key        = 'group';
    protected $group_position   = 30;

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
        return __('Group', 'rp_wcdpd');
    }

    /**
     * Get cart items with quantities to adjust
     *
     * @access public
     * @param array $rule
     * @param array $cart_items
     * @return array
     */
    public function get_cart_items_to_adjust($rule, $cart_items = null)
    {
        $adjust = array();

        // Group cart item quantities
        $quantity_groups = $this->group_quantities($cart_items, $rule);

        // Sort quantity groups so that group products with fewer matched items
        // are processed first and therefore have higher chance of being filled
        // Related to issue #389
        RP_WCDPD_Helper::stable_uasort($quantity_groups, array($this, 'group_product_quantity_group_compare'));

        // Make a copy
        $untouched = $quantity_groups;

        // Start infinite loop to take care of rule repetition, will break out of it by ourselves
        while (true) {

            // Store reserved quantities in a separate array temporary until we are sure that all group products have sufficient quantities
            $current = array();

            // Track cart item quantities that can no longer be considered
            $used_quantities = $adjust;

            // Iterate over group products
            foreach ($quantity_groups as $group_product_key => $group_product) {

                $product_found = false;

                // Make sure group product matched some items
                if ($group_product !== null) {

                    // Iterate over quantity groups for this group product
                    foreach ($group_product as $quantity_group_key => $quantity_group) {

                        // Reserve quantities for this quantity group
                        if ($reserved_quantities = $this->reserve_quantities($quantity_group, $used_quantities, $rule['group_products'][$group_product_key]['quantity'], true)) {

                            // Add to used quantities array
                            $used_quantities = $this->merge_cart_item_quantities($used_quantities, $reserved_quantities);

                            // Add to current array
                            $current = $this->merge_cart_item_quantities($current, $reserved_quantities);

                            // Remove items from untouched items array
                            foreach ($untouched as $untouched_group_product_key => $untouched_group_product) {

                                if (!is_array($untouched_group_product) || empty($untouched_group_product)) {
                                    unset($untouched[$untouched_group_product_key]);
                                    continue;
                                }

                                foreach ($untouched_group_product as $untouched_item_key => $untouched_item) {

                                    if (!is_array($untouched_item) || empty($untouched_item)) {
                                        unset($untouched[$untouched_group_product_key][$untouched_item_key]);
                                        continue;
                                    }

                                    foreach ($untouched_item as $cart_item_key => $quantity) {
                                        if (isset($reserved_quantities[$cart_item_key])) {
                                            unset($untouched[$untouched_group_product_key][$untouched_item_key][$cart_item_key]);
                                        }
                                    }

                                    if (isset($untouched[$untouched_group_product_key][$untouched_item_key]) && empty($untouched[$untouched_group_product_key][$untouched_item_key])) {
                                        unset($untouched[$untouched_group_product_key][$untouched_item_key]);
                                    }
                                }

                                if (empty($untouched[$untouched_group_product_key])) {
                                    unset($untouched[$untouched_group_product_key]);
                                }
                            }

                            // Mark product as found
                            $product_found = true;
                            break;
                        }
                    }
                }

                // At least one product was not found
                if (!$product_found) {

                    // Void current array
                    $current = array();

                    // Clear untouched items array
                    $untouched = array();

                    // Do not check other group products
                    break;
                }
            }

            // Check if full group of products was made up
            if (!empty($current)) {

                // Add to main array
                $adjust = $this->merge_cart_item_quantities($adjust, $current);

                // Rule repetition is enabled
                if ($this->repeat) {
                    continue;
                }
                // We still have untouched items (e.g. we need to repeat in case repetition is disabled, the group is "3 of any" and we have 3 x AAA and 3 x BBB in cart)
                else if (!empty($untouched)) {
                    continue;
                }
            }

            // This loop can only be iterated explicitly, break out of it otherwise
            break;
        }

        return $adjust;
    }

    /**
     * Group quantities of matching cart items for Group rules
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
        $based_on = $rule['group_quantities_based_on'];

        // Filter out cart items that are not affected by this rule so we don't count them
        $cart_items = RP_WCDPD_Product_Pricing::filter_items_by_rules($cart_items, array($rule));

        // Iterate over group products
        foreach ($rule['group_products'] as $group_product_key => $group_product) {

            $match_found = false;

            // Iterate over cart items
            foreach ($cart_items as $cart_item_key => $cart_item) {

                // Get quantity
                $quantity = RP_WCDPD_Helper::cart_item_is_bundle($cart_item) ? 0 : $cart_item['quantity'];

                // Get absolute product id (i.e. parent product id for variations)
                $product_id = RP_WCDPD_WC_Product::product_get_absolute_id($cart_item['data']);

                // Conditions are not matched, move to next cart item
                if (!RP_WCDPD_Conditions::conditions_are_matched(array($group_product), array('cart_item' => $cart_item, 'cart_items' => $cart_items))) {
                    continue;
                }

                // Match found
                $match_found = true;

                // Each individual product
                // Each individual variation (variation not specified)
                if ($based_on === 'group_product' || ($based_on === 'group_variation' && empty($cart_item['variation_id']))) {
                    $quantities[$group_product_key][$product_id][$cart_item_key] = $quantity;
                }
                // Each individual variation (variation specified)
                else if ($based_on === 'group_variation') {
                    $quantities[$group_product_key][$cart_item['variation_id']][$cart_item_key] = $quantity;
                }
                //  Each individual cart line item
                else if ($based_on === 'group_configuration') {
                    $quantities[$group_product_key][$cart_item_key][$cart_item_key] = $quantity;
                }
                // Each individual category
                else if ($based_on === 'group_category') {

                    // Get category ids
                    $categories = RightPress_Helper::get_wc_product_category_ids_from_product_ids(array($product_id));

                    // Iterate over categories and add quantities
                    foreach ($categories as $category_id) {
                        $quantities[$group_product_key][$category_id][$cart_item_key] = $quantity;
                    }
                }
                // All quantities added up
                else if ($based_on === 'group_all') {
                    $quantities[$group_product_key]['_all'][$cart_item_key] = $quantity;
                }
            }

            // Match not found
            if (!$match_found) {
                $quantities[$group_product_key] = null;
            }
        }

        // Return quantities
        return $quantities;
    }

    /**
     * Group product quantity group compare function
     *
     * @access public
     * @param array $a
     * @param array $b
     * @return int
     */
    public function group_product_quantity_group_compare($a, $b)
    {
        // Sort order doesn't matter if at least one element is null (group is not formed)
        if ($a === null || $b === null) {
            return 0;
        }

        $sum_a = 0;
        $sum_b = 0;

        foreach ($a as $group_key => $group) {
            $sum_a += array_sum($group);
        }

        foreach ($b as $group_key => $group) {
            $sum_b += array_sum($group);
        }

        if ($sum_a === $sum_b) {
            return 0;
        }
        else {
            return $sum_a > $sum_b ? 1 : -1;
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

        // Get receive quantity
        $receive_quantity = !empty($adjustment['receive_quantity']) ? (int) $adjustment['receive_quantity'] : 1;

        // Do not process identical prices multiple times
        $processed = array();

        // Iterate over prices
        foreach ($prices as $index => $price) {

            // Get correct processed price key
            $processed_key =  (string) $price['adjusted'];

            // Price not yet processed
            if (!isset($processed[$processed_key])) {

                // Special handling - pricing is set per group
                if (in_array($rule['group_pricing_method'], array('discount__amount_per_group', 'fixed__price_per_group'), true)) {

                    // Get total quantity of all items in this group
                    $all_quantities = wp_list_pluck($rule['group_products'], 'quantity');
                    $total_quantity = array_sum($all_quantities);

                    // Get pricing value per quantity unit
                    $pricing_value_per_unit = RightPress_Helper::get_amount_in_currency($rule['group_pricing_value'], array('aelia')) / $total_quantity;

                    // Fixed discount per group
                    if ($rule['group_pricing_method'] === 'discount__amount_per_group') {
                        $adjusted = $price['adjusted'] - $pricing_value_per_unit;
                        $adjusted = $adjusted >= 0 ? $adjusted : 0;
                    }
                    // Fixed price per group
                    else {
                        $adjusted = $pricing_value_per_unit;
                    }
                }
                // Regular handling - pricing is set per quantity unit
                else {

                    // Get adjusted amount
                    $adjusted = RP_WCDPD_Pricing::adjust_amount($price['adjusted'], $rule['group_pricing_method'], $rule['group_pricing_value']);
                }

                // Round adjusted amount to get predictable results
                $adjusted = RP_WCDPD_Pricing::round($adjusted);

                // Allow developers to override
                $adjusted = apply_filters('rp_wcdpd_product_pricing_adjusted_unit_price', $adjusted, $price['adjusted'], $adjustment, array('receive_quantity' => $receive_quantity));

                // Add to processed prices array
                $processed[$processed_key] = (float) $adjusted;
            }

            // Set adjusted price
            $this->set_adjusted_price($prices, $processed[$processed_key], $index, $adjustment, $cart_item_key);

            // Decrease quantity
            $receive_quantity--;

            // Nothing left to adjust
            if (!$receive_quantity) {
                break;
            }
        }

        return $prices;
    }


}
}
