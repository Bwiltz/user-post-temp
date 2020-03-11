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
 * Condition Method: Date
 *
 * @class RP_WCDPD_Condition_Method_Date
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Date')) {

class RP_WCDPD_Condition_Method_Date extends RP_WCDPD_Condition_Method
{
    protected $key = 'date';

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
            'from'          => __('from', 'rp_wcdpd'),
            'to'            => __('to', 'rp_wcdpd'),
            'specific_date' => __('specific date', 'rp_wcdpd'),
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
        // Get condition date
        if ($condition_date = $this->get_datetime($option_key, $condition_value)) {

            // From
            if ($option_key === 'from' && $value >= $condition_date) {
                return true;
            }
            // To
            else if ($option_key === 'to' && $value <= $condition_date) {
                return true;
            }
            // Specific date
            else if ($option_key === 'specific_date' && $value == $condition_date) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get datetime from condition value
     *
     * @access public
     * @param string $option_key
     * @param mixed $condition_value
     * @return object
     */
    public function get_datetime($option_key, $condition_value)
    {
        // Get condition date
        try {
            $condition_date = RightPress_Helper::get_datetime_object($condition_value, false);
            $condition_date->setTime(0, 0, 0);
            return $condition_date;
        }
        catch (Exception $e) {
            return false;
        }
    }




}

RP_WCDPD_Condition_Method_Date::get_instance();

}
