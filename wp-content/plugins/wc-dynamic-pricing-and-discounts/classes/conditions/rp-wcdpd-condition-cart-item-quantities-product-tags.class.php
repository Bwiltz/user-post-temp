<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Cart_Item_Quantities')) {
    require_once('rp-wcdpd-condition-cart-item-quantities.class.php');
}

/**
 * Condition: Cart Item Quantities - Product Tags
 *
 * @class RP_WCDPD_Condition_Cart_Item_Quantities_Product_Tags
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Item_Quantities_Product_Tags')) {

class RP_WCDPD_Condition_Cart_Item_Quantities_Product_Tags extends RP_WCDPD_Condition_Cart_Item_Quantities
{
    protected $key          = 'product_tags';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'before'    => array('product_tags'),
        'after'     => array('number'),
    );
    protected $main_field   = 'number';
    protected $position     = 50;

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
        return __('Cart item quantity - Tags', 'rp_wcdpd');
    }




}

RP_WCDPD_Condition_Cart_Item_Quantities_Product_Tags::get_instance();

}
