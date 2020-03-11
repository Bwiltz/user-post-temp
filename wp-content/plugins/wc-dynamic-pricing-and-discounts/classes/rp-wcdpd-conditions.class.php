<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to conditions
 *
 * @class RP_WCDPD_Conditions
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Conditions')) {

class RP_WCDPD_Conditions
{
    private static $cache = array();

    /**
     * Check if rule conditions are matched
     *
     * @access public
     * @param array $rule
     * @param array $params
     * @return bool
     */
    public static function rule_conditions_are_matched($rule, $params = array())
    {
        // Check conditions
        if (!empty($rule['conditions']) && is_array($rule['conditions'])) {
            return RP_WCDPD_Conditions::conditions_are_matched($rule['conditions'], $params);
        }

        // No conditions to check
        return true;
    }

    /**
     * Check if conditions are matched
     *
     * @access public
     * @param array $conditions
     * @param array $params
     * @return bool
     */
    public static function conditions_are_matched($conditions, $params = array())
    {
        $product_conditions_for_cart = array();

        // Get cart item params if cart item is set
        if (!empty($params['cart_item'])) {
            $params = array_merge($params, RP_WCDPD_Conditions::get_cart_item_params($params['cart_item']));
        }

        // Regular condition handling
        foreach ($conditions as $condition) {

            // Handle product conditions for cart discount and checkout fee rules separately
            if (!empty($params['context']) && in_array($params['context'], array('cart_discounts', 'checkout_fees'), true)) {
                if (RP_WCDPD_Conditions::is_group($condition, array('product', 'product_property', 'product_other', 'custom_taxonomy'))) {
                    $product_conditions_for_cart[] = $condition;
                    continue;
                }
            }

            // Add condition to params
            $params = array_merge($params, array('condition' => $condition));

            // Check single condition
            if (!RP_WCDPD_Conditions::condition_is_matched($params)) {
                return false;
            }
        }

        // Special handling of product conditions in cart discount and checkout fee rules
        if (!empty($product_conditions_for_cart)) {

            // Get cart items by product conditions
            $cart_items = RP_WCDPD_Conditions::get_cart_items_by_product_conditions($product_conditions_for_cart);

            // No cart items matched
            if (empty($cart_items)) {
                return false;
            }
        }

        // All conditions are matched
        return true;
    }

    /**
     * Check if single condition is matched
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_is_matched($params)
    {
        // Get condition
        if ($condition = RP_WCDPD_Controller_Conditions::get_item($params['condition']['type'])) {

            // Condition is disabled
            if (!$condition->is_enabled()) {
                return false;
            }

            // Maybe skip cart conditions
            if (!empty($params['skip_cart_conditions']) && $condition->is_cart()) {
                return false;
            }

            // Special handling for coupons applied
            if ($condition->get_combined_key() === 'cart__coupons') {
                $params['cache_coupons'] = RightPress_Helper::get_wc_cart_applied_coupon_ids();
            }

            // Use caching on regular requests
            if (!is_ajax()) {

                // Get condition cache hash
                $cache_hash = RP_WCDPD_Conditions::get_cache_hash($params);

                // Result not in cache
                if (!isset(self::$cache[$cache_hash])) {

                    // Check condition
                    self::$cache[$cache_hash] = $condition->check($params);
                }

                // Return result from cache
                return self::$cache[$cache_hash];
            }
            // Do not use caching
            else {

                // Check condition
                return $condition->check($params);
            }
        }

        return false;
    }

    /**
     * Filter rules by conditions
     *
     * @access public
     * @param array $rules
     * @param array $params
     * @return array
     */
    public static function filter_rules($rules, $params = array())
    {
        $filtered = array();

        foreach ($rules as $rule) {
            if (RP_WCDPD_Conditions::rule_conditions_are_matched($rule, $params)) {
                $filtered[] = $rule;
            }
        }

        return $filtered;
    }

    /**
     * Check if cart item or product needs to be excluded by exclude rules
     *
     * @access public
     * @param array $exclude_rules
     * @param array $params
     * @return bool
     */
    public static function exclude_item_by_rules($exclude_rules, $params)
    {
        // Filter exclude rules by conditions
        $filtered_exclude_rules = RP_WCDPD_Conditions::filter_rules($exclude_rules, $params);

        // Remaining exclude rules mean that this item must be excluded from any other rules
        return !empty($filtered_exclude_rules);
    }

    /**
     * Get cart item params for conditions
     *
     * @access public
     * @param array $cart_item
     * @return array
     */
    public static function get_cart_item_params($cart_item)
    {
        return array(
            'item_id'               => !empty($cart_item['product_id']) ? $cart_item['product_id'] : null,
            'child_id'              => !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : null,
            'variation_attributes'  => !empty($cart_item['variation']) ? $cart_item['variation'] : null,
        );
    }

    /**
     * Return conditions
     *
     * @access public
     * @return array
     */
    public static function get_conditions()
    {
        return RP_WCDPD_Controller_Conditions::get_items();
    }

    /**
     * Return conditions for display in admin ui
     *
     * @access public
     * @param string $context
     * @return array
     */
    public static function get_conditions_for_display($context = 'product_pricing')
    {
        return RP_WCDPD_Controller_Conditions::get_items_for_display($context);
    }

    /**
     * Return condition method options for specific condition
     *
     * @access public
     * @param string $combined_condition_key
     * @return array
     */
    public static function get_condition_method_options_for_display($combined_condition_key)
    {
        if ($condition = RP_WCDPD_Controller_Conditions::get_item($combined_condition_key)) {
            if ($method = RP_WCDPD_Controller_Condition_Methods::get_item($condition->get_method())) {
                return $method->get_options();
            }
        }

        // No methods found for this condition
        return array();
    }

    /**
     * Display condition fields
     *
     * @access public
     * @param string $context
     * @param string $combined_condition_key
     * @param string $position
     * @param string $alias
     * @return void
     */
    public static function display_fields($context, $combined_condition_key, $position, $alias = 'condition')
    {
        // Load condition
        if ($condition = RP_WCDPD_Controller_Conditions::get_item($combined_condition_key)) {

            // Get condition fields
            $condition_fields = $condition->get_fields();

            // Check if at least one field is defined
            if (!empty($condition_fields[$position])) {

                // Get total field count (all positions)
                $field_count = count($condition_fields, COUNT_RECURSIVE) - count($condition_fields);

                foreach ($condition_fields[$position] as $field_key) {
                    if ($field = RP_WCDPD_Controller_Condition_Fields::get_item($field_key)) {

                        // Determine field size
                        if ($field_count === 2 || $position === 'before') {
                            $field_size = 'single';
                        }
                        else if ($field_count === 1) {
                            $field_size = 'double';
                        }
                        else {
                            $field_size = 'triple';
                        }

                        // Open container
                        echo '<div class="rp_wcdpd_' . $alias . '_setting_fields_' . $field_size . '">';

                        // Print field
                        $field->display($context, $alias);

                        // Close container
                        echo '</div>';
                    }
                }
            }
        }
    }

    /**
     * Check if condition uses field
     *
     * @access public
     * @param string $combined_key
     * @param string $field
     * @return bool
     */
    public static function uses_field($combined_key, $field)
    {
        // Load condition
        if ($condition = RP_WCDPD_Controller_Conditions::get_item($combined_key)) {

            // Iterate over condition fields
            foreach ($condition->get_fields() as $position => $fields) {
                if (in_array($field, $fields, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if condition exists
     *
     * @access public
     * @param string $combined_key
     * @return bool
     */
    public static function condition_exists($combined_key)
    {
        return RP_WCDPD_Controller_Conditions::item_exists($combined_key);
    }

    /**
     * Check if condition method option exists
     *
     * @access public
     * @param string $combined_condition_key
     * @param string $option_key
     * @return bool
     */
    public static function condition_method_option_exists($combined_condition_key, $option_key)
    {
        $options = RP_WCDPD_Conditions::get_condition_method_options_for_display($combined_condition_key);
        return isset($options[$option_key]);
    }

    /**
     * Get condition cache hash for condition check
     *
     * @access public
     * @param array $params
     * @return string
     */
    public static function get_cache_hash($params)
    {
        // Unset condition uid
        unset($params['condition']['uid']);

        // Unset cart item
        unset($params['cart_item']);

        // Do not cache any cart conditions when running pricing tests (we modify cart contents on the fly)
        if (RP_WCDPD_Controller_Methods_Product_Pricing::running_test() && RightPress_Helper::string_begins_with_substring($params['condition']['type'], 'cart_')) {
            $params['running_test'] = RightPress_Helper::get_hash();
        }

        // Hash remaining condition check params
        return RightPress_Helper::get_hash(true, $params);
    }

    /**
     * Get cart items filtered by product conditions
     *
     * Leaves those cart items for which all product conditions match
     * Ignores non-product conditions
     *
     * @access public
     * @param array $conditions
     * @return array
     */
    public static function get_cart_items_by_product_conditions($conditions = array())
    {
        global $woocommerce;

        // Get cart items
        $cart_items = $woocommerce->cart->get_cart();

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Check if cart item matches product conditions
            if (!RP_WCDPD_Conditions::cart_item_matches_product_conditions($cart_item, $conditions)) {

                // Cart item does not match product conditions - remove cart item from array
                unset($cart_items[$cart_item_key]);
            }
        }

        // Return remaining cart items
        return $cart_items;
    }

    /**
     * Check if cart item matches product conditions
     *
     * @access public
     * @param array $cart_item
     * @param array $conditions
     * @return bool
     */
    public static function cart_item_matches_product_conditions($cart_item, $conditions)
    {
        // Iterate over conditions
        foreach ($conditions as $condition) {

            // Skip non-product conditions
            if (!RP_WCDPD_Conditions::is_group($condition, array('product', 'product_property', 'product_other', 'custom_taxonomy'))) {
                continue;
            }

            // Get cart item params
            $params = RP_WCDPD_Conditions::get_cart_item_params($cart_item);
            $params['condition'] = $condition;
            $params['cart_item'] = $cart_item;

            // Check if condition is matched
            if (!RP_WCDPD_Conditions::condition_is_matched($params)) {

                // At least one product condition is not matched
                return false;
            }
        }

        // If we reached this point conditions are matched
        return true;
    }

    /**
     * Get sum of cart item quantities filtered by product conditions
     *
     * @access public
     * @param array $conditions
     * @return int
     */
    public static function get_sum_of_cart_item_quantities_by_product_conditions($conditions = array())
    {
        $sum = 0;

        // Get cart items filtered by product conditions
        $cart_items = RP_WCDPD_Conditions::get_cart_items_by_product_conditions($conditions);

        // Add all quantities
        foreach ($cart_items as $cart_item) {
            $sum += $cart_item['quantity'];
        }

        return $sum;
    }

    /**
     * Get sum of cart item subtotals filtered by product conditions
     *
     * @access public
     * @param array $conditions
     * @param bool $include_tax
     * @return float
     */
    public static function get_sum_of_cart_item_subtotals_by_product_conditions($conditions = array(), $include_tax = true)
    {
        $sum = 0;

        // Get cart items filtered by product conditions
        $cart_items = RP_WCDPD_Conditions::get_cart_items_by_product_conditions($conditions);

        // Add all quantities
        foreach ($cart_items as $cart_item) {

            // Add subtotal
            $sum += $cart_item['line_subtotal'];

            // Add subtotal tax
            if ($include_tax) {
                $sum += $cart_item['line_subtotal_tax'];
            }
        }

        return $sum;
    }

    /**
     * Check if amounts in conditions should include tax
     *
     * @access public
     * @return bool
     */
    public static function amounts_include_tax()
    {
        return (wc_tax_enabled() && RP_WCDPD_Settings::get('condition_amounts_include_tax'));
    }

    /**
     * Get list of all custom product taxonomies
     *
     * @access public
     * @return void
     */
    public static function get_all_custom_taxonomies()
    {
        $taxonomies = array();

        // Supported object types
        $object_types = apply_filters('rp_wcdpd_custom_condition_taxonomy_object_types', array('product', 'product_variation'));

        // Do not include taxonomies that we already have as conditions
        $blacklist = array('product_cat', 'product_tag');

        // Iterate over all taxonomies
        foreach (get_taxonomies(array(), 'objects') as $taxonomy_key => $taxonomy) {
            if (array_intersect($taxonomy->object_type, $object_types) && !in_array($taxonomy->name, $blacklist, true)) {
                $taxonomies[$taxonomy->name] = $taxonomy->name;
            }
        }

        return apply_filters('rp_wcdpd_custom_condition_taxonomies', $taxonomies);
    }

    /**
     * Check if condition is of given type(s)
     *
     * @access public
     * @param array $condition
     * @param string|array $types
     * @return bool
     */
    public static function is_type($condition, $types)
    {
        return (isset($condition['type']) && in_array($condition['type'], (array) $types, true));
    }

    /**
     * Check if condition belongs to given group(s)
     *
     * @access public
     * @param array $condition
     * @param string|array $groups
     * @return bool
     */
    public static function is_group($condition, $groups)
    {
        if (isset($condition['type'])) {
            foreach ((array) $groups as $group) {
                if (RightPress_Helper::string_begins_with_substring($condition['type'], ($group . '__'))) {
                    return true;
                }
            }
        }

        return false;
    }





}
}
