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
 * Condition Field: Multiselect - Shipping Methods
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Shipping_Methods
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect_Shipping_Methods')) {

class RP_WCDPD_Condition_Field_Multiselect_Shipping_Methods extends RP_WCDPD_Condition_Field_Multiselect
{
    protected $key = 'shipping_methods';

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
     * Load multiselect options
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function load_multiselect_options($ids = array(), $query = '')
    {
        return RightPress_Conditions_Helper::get_all_shipping_methods($ids, $query);
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('Select shipping methods', 'rp_wcdpd');
    }





}

RP_WCDPD_Condition_Field_Multiselect_Shipping_Methods::get_instance();

}
