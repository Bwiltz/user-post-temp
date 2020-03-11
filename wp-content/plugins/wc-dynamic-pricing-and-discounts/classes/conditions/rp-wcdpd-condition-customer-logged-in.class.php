<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Customer')) {
    require_once('rp-wcdpd-condition-customer.class.php');
}

/**
 * Condition: Customer - Logged In
 *
 * @class RP_WCDPD_Condition_Customer_Logged_In
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Logged_In')) {

class RP_WCDPD_Condition_Customer_Logged_In extends RP_WCDPD_Condition_Customer
{
    protected $key      = 'logged_in';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'boolean';
    protected $position = 50;

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
        return __('Is logged in', 'rp_wcdpd');
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
        return is_user_logged_in();
    }




}

RP_WCDPD_Condition_Customer_Logged_In::get_instance();

}
