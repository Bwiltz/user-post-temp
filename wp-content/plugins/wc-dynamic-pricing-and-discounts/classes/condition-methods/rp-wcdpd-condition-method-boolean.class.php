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
 * Condition Method: Boolean
 *
 * @class RP_WCDPD_Condition_Method_Boolean
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Boolean')) {

class RP_WCDPD_Condition_Method_Boolean extends RP_WCDPD_Condition_Method
{
    protected $key = 'boolean';

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
            'yes'   => __('yes', 'rp_wcdpd'),
            'no'    => __('no', 'rp_wcdpd'),
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
        // Yes
        if ($option_key === 'yes' && $value) {
            return true;
        }
        // No
        else if ($option_key === 'no' && !$value) {
            return true;
        }

        return false;
    }




}

RP_WCDPD_Condition_Method_Boolean::get_instance();

}
