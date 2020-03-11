<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to product pricing rules
 *
 * @class RP_WCDPD_Product_Pricing
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Product_Pricing')) {

class RP_WCDPD_Product_Pricing
{

    /**
     * Remove cart items that are not affected by specific pricing rules
     *
     * @access public
     * @param array $cart_items
     * @param array $rules
     * @return array
     */
    public static function filter_items_by_rules($cart_items, $rules)
    {
        $filtered = array();
        $keys_added = array();

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Cart item already in list
            if (in_array($cart_item_key, $keys_added, true)) {
                continue;
            }

            // Filter rules by conditions to leave those that apply to current cart item
            $current_rules = RP_WCDPD_Conditions::filter_rules($rules, array(
                'cart_item'     => $cart_item,
                'cart_items'    => $cart_items,
            ));

            // Add to results array if at least one rule applies to this cart item
            if (!empty($current_rules)) {
                $filtered[$cart_item_key] = $cart_item;
                $keys_added[] = $cart_item_key;
            }
        }

        return $filtered;
    }

    /**
     * Get product pricing rules applicable to product
     *
     * Note: this must only be used in promotion tools when product is not yet in cart
     *
     * @access public
     * @param object $product
     * @param array $methods
     * @param bool $skip_cart_conditions
     * @param mixed $reference_amount_callback
     * @return array
     */
    public static function get_applicable_rules_for_product($product, $methods = null, $skip_cart_conditions = false, $reference_amount_callback = null)
    {
        // Maybe exclude products already on sale
        if (RP_WCDPD_Settings::get('product_pricing_sale_price_handling') === 'exclude' && RP_WCDPD_Product_Pricing::product_is_on_sale($product)) {
            return false;
        }

        // Get product pricing rules
        if ($rules = RP_WCDPD_Rules::get('product_pricing', array('methods' => $methods))) {

            // Get product id
            $product_id = RightPress_WC_Legacy::product_get_id($product);

            // Product details for condition checks
            $filter_config = array(
                'item_id'               => $product->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($product) : $product_id,
                'child_id'              => $product->is_type('variation') ? $product_id : null,
                'variation_attributes'  => $product->is_type('variation') ? $product->get_variation_attributes() : null,
                'skip_cart_conditions'  => $skip_cart_conditions,
            );

            // Get exclude rules
            $exclude_rules = RP_WCDPD_Rules::get('product_pricing', array('methods' => array('exclude')));

            // Check product against exclude rules
            if (empty($exclude_rules) || !RP_WCDPD_Conditions::exclude_item_by_rules($exclude_rules, $filter_config)) {

                // Filter rules by conditions
                if ($rules = RP_WCDPD_Conditions::filter_rules($rules, $filter_config)) {

                    // Filter rules by exclusivity settings
                    if ($mockup_adjustments = RP_WCDPD_Rules::filter_by_exclusivity('product_pricing', RP_WCDPD_Product_Pricing::get_mockup_adjustments($rules, $product, $reference_amount_callback))) {

                        // Exctract rules and return
                        return wp_list_pluck($mockup_adjustments, 'rule');
                    }
                }
            }
        }

        // No rules found
        return array();
    }

    /**
     * Get mockup adjustments for use in rule exclusivity checks
     *
     * Wraps rules into arrays and calculates reference amount if needed
     *
     * Note: this must only be used in promotion tools when product is not yet in cart
     *
     * @access public
     * @param array $rules
     * @param object $product
     * @param mixed $reference_amount_callback
     * @return array
     */
    public static function get_mockup_adjustments($rules, $product, $reference_amount_callback = null)
    {
        $adjustments = array();

        // Check if reference amount is needed
        $selection_method = RP_WCDPD_Settings::get('product_pricing_rule_selection_method');
        $calculate_reference_amount = in_array($selection_method, array('smaller_price', 'bigger_price'), true);

        // Get base amount
        if ($calculate_reference_amount) {
            $base_amount = RightPress_WC_Legacy::product_get_price($product, 'edit');
        }

        // Iterate over rules
        foreach ($rules as $rule) {

            // Wrap rule
            $adjustment = array(
                'rule' => $rule,
            );

            // Maybe calculate reference amount
            if ($calculate_reference_amount) {

                // Get reference amount callback from method if not provided
                if ($reference_amount_callback === null) {

                    // Load controller
                    $controller = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();

                    // Load method
                    if ($method = $controller->get_method_from_rule($rule)) {
                        $reference_amount_callback = array($method, 'get_reference_amount');
                    }
                }

                // Get reference amount
                if ($reference_amount_callback !== null) {
                    $adjustment['reference_amount'] = call_user_func($reference_amount_callback, $adjustment, $base_amount, 1, $product);
                }
                else {
                    $adjustment['reference_amount'] = 0.0;
                }
            }

            // Add to main array
            $adjustments[] = $adjustment;
        }

        return $adjustments;
    }

    /**
     * Safe check for is on sale
     *
     * @access public
     * @param object $product
     * @return bool
     */
    public static function product_is_on_sale($product)
    {
        // Special case
        if (RP_WCDPD_Settings::get('product_pricing_change_display_prices')) {

            if (RightPress_Helper::wc_version_gte('3.0')) {
                return $product->is_on_sale('edit');
            }
            else {

                // Note: do we need to handle grouped and variable products?
                if ($product->is_type('grouped') || $product->is_type('variable')) {
                    return false;
                }

                // Note: review this later
                $price = RightPress_WC_Legacy::product_get_price($product, 'edit');
                $sale_price = RightPress_WC_Legacy::product_get_sale_price($product, 'edit');
                $regular_price = RightPress_WC_Legacy::product_get_regular_price($product, 'edit');
		return ($sale_price !== $regular_price && $sale_price === $price);
            }
        }
        // Regular handling
        else {
            return $product->is_on_sale();
        }
    }

    /**
     * Test product price
     *
     * This method checks what the product price would be if it was added to cart
     *
     * Return values:
     *   float - price would be adjusted
     *   null  - price would not be adjusted
     *   false - error ocurred
     *   array - when change set was requested and pricing is adjusted
     *
     * @access public
     * @param mixed $product
     * @param int $quantity
     * @param array $variation_attributes
     * @param bool $return_change_set
     * @return float|bool|null|array
     */
    public static function test_product_price($product, $quantity = 1, $variation_attributes = array(), $return_change_set = false)
    {
        global $woocommerce;

        $result = false;

        // Add test flag
        RP_WCDPD_Controller_Methods_Product_Pricing::start_test();

        // Load product if id was passed
        if (!is_a($product, 'WC_Product')) {
            $product = wc_get_product($product);
        }

        // Cart not instantiated yet
        if (!isset($woocommerce->cart) || !is_object($woocommerce->cart) || !isset($woocommerce->cart->cart_contents)) {
            return false;
        }

        // Get cart items
        $cart_items = $woocommerce->cart->cart_contents;

        // Simulate add to cart
        if ($cart_item_key = RP_WCDPD_Product_Pricing::simulate_add_to_cart($cart_items, RightPress_WC_Legacy::product_get_id($product), $quantity, 0, $variation_attributes)) {

            // Get cart item change set
            $changes = RP_WCDPD_Controller_Methods_Product_Pricing::get_change_set($cart_items, $cart_item_key, $quantity);

            // Price was adjusted
            if (!empty($changes[$cart_item_key])) {

                // Return changeset for this particular product
                if ($return_change_set) {
                    $result = $changes[$cart_item_key];
                }
                // Return price
                else {
                    $result = $changes[$cart_item_key]['price'];
                }
            }
            // Price was not adjusted
            else {
                $result = null;
            }
        }

        // Remove test flag
        RP_WCDPD_Controller_Methods_Product_Pricing::end_test();

        // Return result
        return $result;
    }

    /**
     * Simulate add to cart
     *
     * Based on WC_Cart:add_to_cart() version 3.1
     *
     * Returns cart item key and directly modifies cart items array
     *
     * @access public
     * @param array $cart_items
     * @param int $product_id
     * @param int $quantity
     * @return string
     */
    public static function simulate_add_to_cart(&$cart_items, $product_id, $quantity, $variation_id = 0, $variation = array(), $cart_item_data = array())
    {
        try {

            global $woocommerce;

            // Sanitize ids
            $product_id = absint($product_id);
            $variation_id = absint($variation_id);

            // Get parent product id if needed
            if ('product_variation' === get_post_type($product_id)) {
                $variation_id = $product_id;
                $product_id   = wp_get_post_parent_id($variation_id);
            }

            // Get the product
            $product = wc_get_product($variation_id ? $variation_id : $product_id);

            // Sanity check
            if ($quantity <= 0 || !$product) {
                return false;
            }

            // Load cart item data - may be added by other plugins
            $cart_item_data = (array) apply_filters('woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id);

            // Generate cart item key
            $cart_item_key = $woocommerce->cart->generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

            // Item is already in the cart
            if (isset($cart_items[$cart_item_key])) {

                // Increase quantity
                $cart_items[$cart_item_key]['quantity'] += $quantity;
            }
            // Item not yet in cart
            else {

                // Add item after merging with $cart_item_data
                // Note: the array got filtered by woocommerce_add_cart_item, removed it for Custom Fields compatibility
                $cart_items[$cart_item_key] = array_merge($cart_item_data, array(
                    'product_id'    => $product_id,
                    'variation_id'  => $variation_id,
                    'variation'     => $variation,
                    'quantity'      => $quantity,
                    'data'          => $product,
                ));
            }

            // Return cart item key
            return $cart_item_key;
        }
        catch (Exception $e) {
            return false;
        }
    }





}
}
