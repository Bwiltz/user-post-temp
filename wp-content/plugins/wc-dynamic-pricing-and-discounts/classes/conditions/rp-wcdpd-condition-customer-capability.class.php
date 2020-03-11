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
 * Condition: Customer - Capability
 *
 * @class RP_WCDPD_Condition_Customer_Capability
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Capability')) {

class RP_WCDPD_Condition_Customer_Capability extends RP_WCDPD_Condition_Customer
{
    protected $key      = 'capability';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('capabilities'),
    );
    protected $position = 30;

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
        return __('User capability', 'rp_wcdpd');
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
        $user_capabilities = RightPress_Helper::current_user_capabilities();
        return apply_filters('rp_wcdpd_current_user_capabilities', $user_capabilities);
    }




}

RP_WCDPD_Condition_Customer_Capability::get_instance();

}
