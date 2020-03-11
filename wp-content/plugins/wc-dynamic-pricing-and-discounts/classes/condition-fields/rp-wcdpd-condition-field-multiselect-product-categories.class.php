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
 * Condition Field: Multiselect - Product Categories
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Product_Categories
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect_Product_Categories')) {

class RP_WCDPD_Condition_Field_Multiselect_Product_Categories extends RP_WCDPD_Condition_Field_Multiselect
{
    protected $key                  = 'product_categories';
    protected $supports_hierarchy   = true;

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
     * Get child ids for fields that support hierarchy
     *
     * @access public
     * @param array $values
     * @return array
     */
    public function get_children($values)
    {
        $values_with_children = array();

        foreach ($values as $value) {
            $values_with_children[$value] = RightPress_Helper::get_term_with_children($value, 'product_cat');
        }

        return $values_with_children;
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
        return RightPress_Conditions_Helper::get_all_product_categories($ids, $query);
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('Select product categories', 'rp_wcdpd');
    }




}

RP_WCDPD_Condition_Field_Multiselect_Product_Categories::get_instance();

}
