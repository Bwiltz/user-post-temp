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
 * Cart Discount Method
 *
 * @class RP_WCDPD_Method_Cart_Discount
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Cart_Discount')) {

abstract class RP_WCDPD_Method_Cart_Discount extends RP_WCDPD_Method
{
    protected $context = 'cart_discounts';

    /**
     * Get cart subtotal
     *
     * @access public
     * @return float
     */
    public function get_cart_subtotal()
    {
        $include_tax = wc_prices_include_tax();
        return RP_WCDPD_WC_Cart::calculate_subtotal($include_tax);
    }


}
}
