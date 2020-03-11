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
 * Condition Method: Coupons
 *
 * @class RP_WCDPD_Condition_Method_Coupons
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Coupons')) {

class RP_WCDPD_Condition_Method_Coupons extends RP_WCDPD_Condition_Method
{
    protected $key = 'coupons';

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
            'at_least_one_any'  => __('at least one of any', 'rp_wcdpd'),
            'at_least_one'      => __('at least one of selected', 'rp_wcdpd'),
            'all'               => __('all of selected', 'rp_wcdpd'),
            'only'              => __('only selected', 'rp_wcdpd'),
            'none'              => __('none of selected', 'rp_wcdpd'),
            'none_at_all'       => __('none at all', 'rp_wcdpd'),
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
        // Normalize value
        $value = array_map('intval', $value);
        sort($value);

        // Normalize condition value
        $condition_value = array_map('intval', $condition_value);
        sort($condition_value);

        // At least one of any
        if ($option_key === 'at_least_one_any' && !empty($value)) {
            return true;
        }
        // At least one of selected
        else if ($option_key === 'at_least_one' && count(array_intersect($value, $condition_value)) > 0) {
            return true;
        }
        // All of selected
        else if ($option_key === 'all' && count(array_intersect($value, $condition_value)) == count($condition_value)) {
            return true;
        }
        // Only selected
        else if ($option_key === 'only' && $value === $condition_value) {
            return true;
        }
        // None of selected
        else if ($option_key === 'none' && count(array_intersect($value, $condition_value)) === 0) {
            return true;
        }
        // None at all
        else if ($option_key === 'none_at_all' && empty($value)) {
            return true;
        }

        return false;
    }




}

RP_WCDPD_Condition_Method_Coupons::get_instance();

}
