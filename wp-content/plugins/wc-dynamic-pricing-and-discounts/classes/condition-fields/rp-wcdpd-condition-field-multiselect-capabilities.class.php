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
 * Condition Field: Multiselect - Capabilities
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Capabilities
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect_Capabilities')) {

class RP_WCDPD_Condition_Field_Multiselect_Capabilities extends RP_WCDPD_Condition_Field_Multiselect
{
    protected $key = 'capabilities';

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
        $all_capabilities = RightPress_Conditions_Helper::get_all_capabilities($ids, $query);
        return apply_filters('rp_wcdpd_all_capabilities', $all_capabilities);
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('Select user capabilities', 'rp_wcdpd');
    }





}

RP_WCDPD_Condition_Field_Multiselect_Capabilities::get_instance();

}
