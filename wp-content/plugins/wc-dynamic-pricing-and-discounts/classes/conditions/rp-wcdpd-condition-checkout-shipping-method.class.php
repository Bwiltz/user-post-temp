<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Checkout')) {
    require_once('rp-wcdpd-condition-checkout.class.php');
}

/**
 * Condition: Checkout - Shipping Method
 *
 * @class RP_WCDPD_Condition_Checkout_Shipping_Method
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Checkout_Shipping_Method')) {

class RP_WCDPD_Condition_Checkout_Shipping_Method extends RP_WCDPD_Condition_Checkout
{
    protected $key      = 'shipping_method';
    protected $contexts = array('cart_discounts', 'checkout_fees');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('shipping_methods'),
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
        return __('Shipping method', 'rp_wcdpd');
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
        global $woocommerce;

        // Get chosen shipping methods
        $shipping_methods = $woocommerce->session->get('chosen_shipping_methods');

        // Get first shipping method (currently not supporting multiple packages)
        if (!empty($shipping_methods)) {
            $shipping_method = array_shift($shipping_methods);
            return strtok($shipping_method, ':');
        }

        return null;
    }




}

RP_WCDPD_Condition_Checkout_Shipping_Method::get_instance();

}
