<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Time')) {
    require_once('rp-wcdpd-condition-time.class.php');
}

/**
 * Condition: Time - Time
 *
 * @class RP_WCDPD_Condition_Time_Time
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Time_Time')) {

class RP_WCDPD_Condition_Time_Time extends RP_WCDPD_Condition_Time
{
    protected $key      = 'time';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'time';
    protected $fields   = array(
        'after' => array('time'),
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
        return __('Time', 'rp_wcdpd');
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
        return RightPress_Helper::get_datetime_object();
    }




}

RP_WCDPD_Condition_Time_Time::get_instance();

}
