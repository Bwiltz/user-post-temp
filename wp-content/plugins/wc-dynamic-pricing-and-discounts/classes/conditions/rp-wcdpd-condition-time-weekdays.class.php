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
 * Condition: Time - Weekdays
 *
 * @class RP_WCDPD_Condition_Time_Weekdays
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Time_Weekdays')) {

class RP_WCDPD_Condition_Time_Weekdays extends RP_WCDPD_Condition_Time
{
    protected $key      = 'weekdays';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('weekdays'),
    );
    protected $position = 40;

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
        return __('Days of week', 'rp_wcdpd');
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
        $date = RightPress_Helper::get_datetime_object();
        return $date->format('w');
    }




}

RP_WCDPD_Condition_Time_Weekdays::get_instance();

}
