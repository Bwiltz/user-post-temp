<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Cart')) {
    require_once('rp-wcdpd-condition-cart.class.php');
}

/**
 * Condition: Cart - Weight
 *
 * @class RP_WCDPD_Condition_Cart_Weight
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Weight')) {

class RP_WCDPD_Condition_Cart_Weight extends RP_WCDPD_Condition_Cart
{
    protected $key      = 'weight';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'numeric';
    protected $fields   = array(
        'after' => array('decimal'),
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
        return __('Cart total weight', 'rp_wcdpd');
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
        $cart_items = $this->get_cart_items($params);
        return RightPress_Helper::get_wc_cart_contents_weight($cart_items);
    }




}

RP_WCDPD_Condition_Cart_Weight::get_instance();

}
