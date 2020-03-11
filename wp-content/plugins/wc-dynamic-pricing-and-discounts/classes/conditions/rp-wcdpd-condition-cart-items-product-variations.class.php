<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Cart_Items')) {
    require_once('rp-wcdpd-condition-cart-items.class.php');
}

/**
 * Condition: Cart Items - Product Variations
 *
 * @class RP_WCDPD_Condition_Cart_Items_Product_Variations
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Items_Product_Variations')) {

class RP_WCDPD_Condition_Cart_Items_Product_Variations extends RP_WCDPD_Condition_Cart_Items
{
    protected $key      = 'product_variations';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'list_advanced';
    protected $fields   = array(
        'after' => array('product_variations'),
    );
    protected $position = 20;

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
        return __('Cart items - Variations', 'rp_wcdpd');
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_value($params)
    {
        $cart_items = isset($params['cart_items']) ? $params['cart_items'] : null;
        return RightPress_Helper::get_wc_cart_product_variation_ids($cart_items);
    }




}

RP_WCDPD_Condition_Cart_Items_Product_Variations::get_instance();

}
