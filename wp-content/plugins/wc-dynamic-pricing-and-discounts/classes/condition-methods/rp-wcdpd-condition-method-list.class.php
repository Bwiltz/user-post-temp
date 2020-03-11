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
 * Condition Method: List
 *
 * @class RP_WCDPD_Condition_Method_List
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_List')) {

class RP_WCDPD_Condition_Method_List extends RP_WCDPD_Condition_Method
{
    protected $key = 'list';

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
            'in_list'       => __('in list', 'rp_wcdpd'),
            'not_in_list'   => __('not in list', 'rp_wcdpd'),
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
        $value = (array) $value;
        $value = array_map('strval', $value);

        // Fix multidimensional condition value array since this method does not need parent/child relationship data
        if (RightPress_Helper::array_is_multidimensional($condition_value)) {
            $condition_value = call_user_func_array('array_merge', $condition_value);
        }

        // Normalize condition value
        $condition_value = array_map('strval', $condition_value);

        // Proceed depending on method
        if ($option_key === 'not_in_list') {
            if (count(array_intersect($value, $condition_value)) == 0) {
                return true;
            }
        }
        else {
            if (count(array_intersect($value, $condition_value)) > 0) {
                return true;
            }
        }

        return false;
    }




}

RP_WCDPD_Condition_Method_List::get_instance();

}
