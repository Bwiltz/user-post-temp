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
 * Condition Method: Numeric
 *
 * @class RP_WCDPD_Condition_Method_Numeric
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Numeric')) {

class RP_WCDPD_Condition_Method_Numeric extends RP_WCDPD_Condition_Method
{
    protected $key = 'numeric';

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
            'at_least'      => __('at least', 'rp_wcdpd'),
            'more_than'     => __('more than', 'rp_wcdpd'),
            'not_more_than' => __('not more than', 'rp_wcdpd'),
            'less_than'     => __('less than', 'rp_wcdpd'),
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
        // Convert floats to strings (floats have precision problems and can't be compared directly)
        if (is_float($value) || is_float($condition_value)) {
            $value = sprintf('%.10f', (float) $value);
            $condition_value = sprintf('%.10f', (float) $condition_value);
        }

        // Compare
        if ($option_key === 'less_than' && $value < $condition_value) {
            return true;
        }
        else if ($option_key === 'not_more_than' && $value <= $condition_value) {
            return true;
        }
        else if ($option_key === 'at_least' && $value >= $condition_value) {
            return true;
        }
        else if ($option_key === 'more_than' && $value > $condition_value) {
            return true;
        }

        return false;
    }




}

RP_WCDPD_Condition_Method_Numeric::get_instance();

}
