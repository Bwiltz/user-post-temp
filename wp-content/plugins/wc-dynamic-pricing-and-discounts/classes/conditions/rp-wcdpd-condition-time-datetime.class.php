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
 * Condition: Time - Datetime
 *
 * @class RP_WCDPD_Condition_Time_Datetime
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Time_Datetime')) {

class RP_WCDPD_Condition_Time_Datetime extends RP_WCDPD_Condition_Time
{
    protected $key      = 'datetime';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'datetime';
    protected $fields   = array(
        'after' => array('datetime'),
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
        return __('Date & time', 'rp_wcdpd');
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

RP_WCDPD_Condition_Time_Datetime::get_instance();

}
