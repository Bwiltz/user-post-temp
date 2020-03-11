<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to price and discount calculations
 *
 * @class RP_WCDPD_Pricing
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Pricing')) {

class RP_WCDPD_Pricing
{

    /**
     * Get pricing methods for display
     *
     * @access public
     * @param string $context
     * @return array
     */
    public static function get_pricing_methods_for_display($context = null)
    {
        return RP_WCDPD_Controller_Pricing_Methods::get_items_for_display($context);
    }

    /**
     * Check if pricing method exists
     *
     * @access public
     * @param string $combined_key
     * @param string $context
     * @return bool
     */
    public static function pricing_method_exists($combined_key, $context = null)
    {
        return RP_WCDPD_Controller_Pricing_Methods::item_exists($combined_key, $context);
    }

    /**
     * Get adjustment value
     *
     * @access public
     * @param string $combined_key
     * @param float $setting
     * @param float $amount
     * @param array $adjustment
     * @return float
     */
    public static function get_adjustment_value($combined_key, $setting, $amount = 0, $adjustment = null)
    {
        // Load pricing method
        if ($pricing_method = RP_WCDPD_Controller_Pricing_Methods::get_item($combined_key)) {
            return $pricing_method->calculate($setting, $amount, $adjustment);
        }

        return 0;
    }

    /**
     * Adjusted amount
     *
     * @access public
     * @param float $amount
     * @param string $combined_key
     * @param float $setting
     * @return float
     */
    public static function adjust_amount($amount, $combined_key, $setting)
    {
        // Load pricing method
        if ($pricing_method = RP_WCDPD_Controller_Pricing_Methods::get_item($combined_key)) {
            return $pricing_method->adjust($amount, $setting);
        }

        return $amount;
    }

    /**
     * Round amount
     *
     * @access public
     * @param float $amount
     * @param int $decimals
     * @return float
     */
    public static function round($amount, $decimals = null)
    {
        // Get decimals
        if ($decimals === null) {
            $decimals = apply_filters('rp_wcdpd_decimals', wc_get_price_decimals());
        }

        // Round amount if allowed in settings
        if (RP_WCDPD_Settings::get('product_pricing_irrational_price_handling') === 'round') {
            $rounded_amount = round($amount, $decimals);
        }
        else {
            $rounded_amount = $amount;
        }

        // Allow developers to do their own rounding
        return apply_filters('rp_wcdpd_rounded_amount', $rounded_amount, $amount, $decimals);
    }

    /**
     * Get product base price
     *
     * @access public
     * @param object $product
     * @return float
     */
    public static function get_product_base_price($product)
    {
        // Product is on sale and regular price has to be used as base price
        if (RP_WCDPD_Settings::get('product_pricing_sale_price_handling') === 'regular' && RP_WCDPD_Product_Pricing::product_is_on_sale($product)) {
            return RightPress_WC_Legacy::product_get_regular_price($product, 'edit');
        }
        // Otherwise always use final product price
        else {
            return RightPress_WC_Legacy::product_get_price($product, 'edit');
        }
    }

    /**
     * Get pricing settings label by context
     *
     * @access public
     * @param string $context
     * @return string
     */
    public static function get_pricing_settings_label($context)
    {
        if ($context === 'product_pricing') {
            return __('Adjustment', 'rp_wcdpd');
        }
        else if ($context === 'cart_discounts') {
            return __('Discount', 'rp_wcdpd');
        }
        else if ($context === 'checkout_fees') {
            return __('Fee', 'rp_wcdpd');
        }
    }




}
}
