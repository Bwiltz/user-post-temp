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
 * Condition: Checkout - Payment Method
 *
 * @class RP_WCDPD_Condition_Checkout_Payment_Method
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Checkout_Payment_Method')) {

class RP_WCDPD_Condition_Checkout_Payment_Method extends RP_WCDPD_Condition_Checkout
{
    protected $key      = 'payment_method';
    protected $contexts = array('cart_discounts', 'checkout_fees');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('payment_methods'),
    );
    protected $position = 10;

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
        return __('Payment method', 'rp_wcdpd');
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

        // Get chosen payment method
        $payment_method = $woocommerce->session->get('chosen_payment_method');

        // Check if payment gateway was chosen
        return (is_string($payment_method) && !empty($payment_method)) ? $payment_method : null;
    }




}

RP_WCDPD_Condition_Checkout_Payment_Method::get_instance();

}
