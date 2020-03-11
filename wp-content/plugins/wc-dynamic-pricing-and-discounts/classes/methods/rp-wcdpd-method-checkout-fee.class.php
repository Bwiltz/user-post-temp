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
 * Checkout Fee Method
 *
 * @class RP_WCDPD_Method_Checkout_Fee
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Checkout_Fee')) {

abstract class RP_WCDPD_Method_Checkout_Fee extends RP_WCDPD_Method
{
    protected $context = 'checkout_fees';

    /**
     * Get cart subtotal
     *
     * @access public
     * @return float
     */
    public function get_cart_subtotal()
    {
        return RightPress_Helper::get_wc_cart_subtotal(false);
    }


}
}
