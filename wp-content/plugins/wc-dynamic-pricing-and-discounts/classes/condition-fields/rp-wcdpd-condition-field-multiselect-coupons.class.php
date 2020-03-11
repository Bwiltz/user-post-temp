<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect')) {
    require_once('rp-wcdpd-condition-field-multiselect.class.php');
}

/**
 * Condition Field: Multiselect - Coupons
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Coupons
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect_Coupons')) {

class RP_WCDPD_Condition_Field_Multiselect_Coupons extends RP_WCDPD_Condition_Field_Multiselect
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
     * Get disabled value
     *
     * @access public
     * @return string
     */
    public function get_disabled()
    {
        return 'disabled';
    }

    /**
     * Validate field value
     *
     * @access public
     * @param array $posted
     * @param object $condition
     * @param string $method_option_key
     * @return bool
     */
    public function validate($posted, $condition, $method_option_key)
    {
        // At least one item selected
        if (isset($posted[$this->key]) && !RightPress_Helper::is_empty($posted[$this->key])) {
            return true;
        }
        // Items not required
        else if (in_array($method_option_key, array('at_least_one_any', 'none_at_all'), true)) {
            return true;
        }

        return false;
    }

    /**
     * Load multiselect options
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function load_multiselect_options($ids = array(), $query = '')
    {
        return RightPress_Conditions_Helper::get_all_coupons($ids, $query, 'rp_wcdpd');
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('Select coupons', 'rp_wcdpd');
    }





}

RP_WCDPD_Condition_Field_Multiselect_Coupons::get_instance();

}
