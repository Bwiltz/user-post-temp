<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin specific methods used by multiple classes
 *
 * @class RP_WCDPD_Helper
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Helper')) {

class RP_WCDPD_Helper
{

    /**
     * Stable asort
     *
     * Adapted from https://github.com/vanderlee/PHP-stable-sort-functions/
     *
     * Can't move it to RightPress_Helper since it can't work with references
     *
     * @access public
     * @param array $array
     * @param string $sort_flags
     * @return array
     */
    public static function stable_asort(&$array, $sort_flags = SORT_REGULAR)
    {
        $index = 0;

        foreach ($array as &$item) {
            $item = array($index++, $item);
        }

        $result = uasort($array, function($a, $b) use($sort_flags) {

            if ($a[1] == $b[1]) {
                return $a[0] - $b[0];
            }

            $set = array(-1 => $a[1], 1 => $b[1]);
            asort($set, $sort_flags);
            reset($set);
            return key($set);
        });

        foreach ($array as &$item) {
            $item = $item[1];
        }

        return $result;
    }

    /**
     * Stable uasort
     *
     * Adapted from https://github.com/vanderlee/PHP-stable-sort-functions/
     *
     * Can't move it to RightPress_Helper since it can't work with references
     *
     * @access public
     * @param array $array
     * @param callable $value_compare_func
     * @return array
     */
    public static function stable_uasort(&$array, $value_compare_func)
    {
        $index = 0;

        foreach ($array as &$item) {
            $item = array($index++, $item);
        }

        $result = @uasort($array, function($a, $b) use($value_compare_func) {
            $result = call_user_func($value_compare_func, $a[1], $b[1]);
            return $result == 0 ? $a[0] - $b[0] : $result;
        });

        foreach ($array as &$item) {
            $item = $item[1];
        }

        return $result;
    }

    /**
     * Filter out bundle cart items
     *
     * @access public
     * @param array $cart_items
     * @return array
     */
    public static function filter_out_bundle_cart_items($cart_items)
    {
        // Remove bundle items
        if (is_array($cart_items)) {
            foreach ($cart_items as $cart_item_key => $cart_item) {
                if (RP_WCDPD_Helper::cart_item_is_bundle($cart_item)) {
                    unset($cart_items[$cart_item_key]);
                    break;
                }
            }
        }

        // Return filtered items
        return $cart_items;
    }

    /**
     * Cart item is product bundle
     *
     * @access public
     * @param array $cart_item
     * @return bool
     */
    public static function cart_item_is_bundle($cart_item)
    {
        // Flags to use
        // Note: developers need to return array('bundled_by') to reverse the operation
        // for the "official" Product Bundles plugin, issues #359 and #437
        $flags = apply_filters('rp_wcdpd_bundle_cart_item_filter_flags', array('bundled_items'));

        // Check if cart item is product bundle
        foreach ($flags as $flag) {
            if (isset($cart_item[$flag])) {
                return true;
            }
        }

        // Not bundle
        return false;
    }

    /**
     * Multiple tax classes are set up
     *
     * @access public
     * @return bool
     */
    public static function wc_has_multiple_tax_classes()
    {
        $count = 0;

        // Get all tax classes
        $tax_classes = WC_Tax::get_tax_classes();

        // Add default tax class
        if (!in_array('', $tax_classes, true)) {
            $tax_classes[] = '';
        }

        // Count tax classes that have at least one tax rate added
        foreach ($tax_classes as $tax_class) {
            if (WC_Tax::get_rates_for_tax_class($tax_class)) {
                $count++;
            }
        }

        return $count > 1;
    }









}
}
