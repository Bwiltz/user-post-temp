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
 * Product Pricing Method: Parent class for all methods that use quantity grouping
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity')) {

abstract class RP_WCDPD_Method_Product_Pricing_Quantity extends RP_WCDPD_Method_Product_Pricing
{

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

        // Get cart items with quantities to adjust
        $cart_items_to_adjust = $this->get_cart_items_to_adjust($rule, $cart_items);

        // Iterate over all cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Check if rule applies to current cart item
            // Note: conditions are not checked here as they were checked when grouping quantities, if cart item is not there - conditions do not match
            if (isset($cart_items_to_adjust[$cart_item_key])) {

                // Make sure that rule does not get applied multiple times
                if (!RP_WCDPD_Controller_Methods_Product_Pricing::is_already_processed($rule['uid'], $cart_item_key)) {

                    // Get product base price
                    $base_price = RP_WCDPD_Pricing::get_product_base_price($cart_item['data']);

                    // Add adjustment to main array
                    $adjustments[$cart_item_key] = array(
                        'rule'              => $rule,
                        'receive_quantity'  => $cart_items_to_adjust[$cart_item_key],
                        'reference_amount'  => $this->get_reference_amount(array('rule' => $rule, 'receive_quantity' => $cart_items_to_adjust[$cart_item_key]), $base_price, $cart_item['quantity'], $cart_item['data'], $cart_item),
                    );
                }
            }
        }

        return $adjustments;
    }

    /**
     * Reserve quantities from quantity group
     *
     * @access public
     * @param array $quantity_group
     * @param array $used_quantities
     * @param int $quantity
     * @param bool $require_all
     * @return mixed
     */
    public function reserve_quantities($quantity_group, $used_quantities, $reserve_quantity, $require_all = false)
    {
        $quantities = array();

        // No items found
        if ($quantity_group === null) {
            return false;
        }

        // Iterate over cart item quantities in quantity group
        foreach ($quantity_group as $cart_item_key => $cart_item_quantity) {

            // Account for used quantities
            if (isset($used_quantities[$cart_item_key])) {
                $cart_item_quantity -= $used_quantities[$cart_item_key];
            }

            // All units used up
            if ($cart_item_quantity < 1) {
                continue;
            }

            // Proceed depending on which quantity is bigger
            if ($reserve_quantity > $cart_item_quantity) {
                $quantities[$cart_item_key] = $cart_item_quantity;
                $reserve_quantity -= $cart_item_quantity;
            }
            else {
                $quantities[$cart_item_key] = $reserve_quantity;
                $reserve_quantity = 0;
                break;
            }
        }

        // Return reserved quantities or false if min requirement was not met
        return (empty($quantities) || ($require_all && $reserve_quantity > 0)) ? false : $quantities;
    }

    /**
     * Merge cart item quantities
     *
     * @access public
     * @param array $output
     * @param array $input
     * @return array
     */
    public function merge_cart_item_quantities($output, $input)
    {
        foreach ($input as $cart_item_key => $quantity) {
            $output[$cart_item_key] = isset($output[$cart_item_key]) ? ($output[$cart_item_key] + $quantity) : $quantity;
        }

        return $output;
    }



}
}
