<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Cart_Item_Subtotals')) {
    require_once('rp-wcdpd-condition-cart-item-subtotals.class.php');
}

/**
 * Condition: Cart Item Subtotals - Product Attributes
 *
 * @class RP_WCDPD_Condition_Cart_Item_Subtotals_Product_Attributes
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Item_Subtotals_Product_Attributes')) {

class RP_WCDPD_Condition_Cart_Item_Subtotals_Product_Attributes extends RP_WCDPD_Condition_Cart_Item_Subtotals
{
    protected $key          = 'product_attributes';
    protected $contexts     = array('cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'before'    => array('product_attributes'),
        'after'     => array('decimal'),
    );
    protected $main_field   = 'decimal';
    protected $position     = 40;

    // Singleton instance
    protected static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->hook();
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return __('Cart item subtotal - Attributes', 'rp_wcdpd');
    }




}

RP_WCDPD_Condition_Cart_Item_Subtotals_Product_Attributes::get_instance();

}
