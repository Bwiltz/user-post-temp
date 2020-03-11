<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Products
 *
 * @class RP_WCDPD_WC_Product
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_WC_Product')) {

class RP_WCDPD_WC_Product
{

    /**
     * Get absolute product id
     *
     * @access public
     * @param object $product
     * @return int
     */
    public static function product_get_absolute_id($product)
    {
        if ($product->is_type('variation')) {
            return RightPress_WC_Legacy::product_variation_get_parent_id($product);
        }
        else {
            return RightPress_WC_Legacy::product_get_id($product);
        }
    }


}
}
