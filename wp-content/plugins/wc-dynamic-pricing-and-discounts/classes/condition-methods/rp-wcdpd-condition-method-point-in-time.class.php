<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Method')) {
    require_once('rp-wcdpd-condition-method.class.php');
}

/**
 * Condition Method: Point In Time
 *
 * @class RP_WCDPD_Condition_Method_Point_In_Time
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Point_In_Time')) {

class RP_WCDPD_Condition_Method_Point_In_Time extends RP_WCDPD_Condition_Method
{
    protected $key = 'point_in_time';

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
        $this->hook();
    }

    /**
     * Get method options
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        return array(
            'later'     => __('within past', 'rp_wcdpd'),
            'earlier'   => __('earlier than', 'rp_wcdpd'),
        );
    }

    /**
     * Check against condition method
     *
     * @access public
     * @param string $option_key
     * @param mixed $value
     * @param mixed $condition_value
     * @return bool
     */
    public function check($option_key, $value, $condition_value)
    {
        // Get timeframe field
        $timeframe_field = RP_WCDPD_Controller_Condition_Fields::get_item('timeframe_event');

        // Get condition date
        $condition_date = $timeframe_field->get_date_from_timeframe($condition_value);

        // Earlier than or no order at all
        if ($option_key === 'earlier' && ($value === null || $value < $condition_date)) {
            return true;
        }
        // Within past
        else if ($option_key === 'later' && $value !== null && $value >= $condition_date) {
            return true;
        }

        return false;
    }




}

RP_WCDPD_Condition_Method_Point_In_Time::get_instance();

}
