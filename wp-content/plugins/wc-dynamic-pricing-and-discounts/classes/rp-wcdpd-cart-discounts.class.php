<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to cart discount rules
 *
 * @class RP_WCDPD_Cart_Discounts
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Cart_Discounts')) {

class RP_WCDPD_Cart_Discounts
{

    /**
     * Get coupon data
     *
     * @access public
     * @param array $data
     * @return array
     */
    public static function get_coupon_data_array($data)
    {
        // Amount is mandatory
        if (!isset($data['amount'])) {
            return false;
        }

        // Individual use property
        if (RP_WCDPD_Settings::get('cart_discounts_allow_coupons')) {
            $data['individual_use'] = (RightPress_Helper::wc_version_gte('3.0') ? false : 'no');
        }
        else {
            $data['individual_use'] = (RightPress_Helper::wc_version_gte('3.0') ? true : 'yes');
        }

        // Return coupon data array
        return apply_filters('rp_wcdpd_cart_discount_coupon_data', array_merge(array(
            'id'                            => PHP_INT_MAX,
            'type'                          => 'fixed_cart',
            'discount_type'                 => 'fixed_cart',
            'amount'                        => 0,
            'product_ids'                   => array(),
            'exclude_product_ids'           => array(),
            'usage_limit'                   => '',
            'usage_limit_per_user'          => '',
            'limit_usage_to_x_items'        => '',
            'usage_count'                   => '',
            'expiry_date'                   => '',
            'apply_before_tax'              => 'yes',
            'free_shipping'                 => (RightPress_Helper::wc_version_gte('3.0') ? false : 'no'),
            'product_categories'            => array(),
            'exclude_product_categories'    => array(),
            'exclude_sale_items'            => (RightPress_Helper::wc_version_gte('3.0') ? false : 'no'),
            'minimum_amount'                => '',
            'maximum_amount'                => '',
            'customer_email'                => '',
        ), $data));
    }

    /**
     * Get cart discount fake coupon value html
     *
     * @access public
     * @param object $coupon
     * @return string
     */
    public static function get_cart_discount_coupon_html($coupon)
    {
        // Get coupon code
        $code = RightPress_WC_Legacy::coupon_get_code($coupon);

        // Get coupon discount amount
        if (RightPress_Helper::wc_version_gte('2.3')) {
            $amount = WC()->cart->get_coupon_discount_amount($code, WC()->cart->display_cart_ex_tax);
        }
        else {
            $amount = WC()->cart->coupon_discount_amounts[$code];
        }

        // Format html
        return apply_filters('woocommerce_coupon_discount_amount_html', ('-' . wc_price($amount)), $coupon);
    }



}
}
