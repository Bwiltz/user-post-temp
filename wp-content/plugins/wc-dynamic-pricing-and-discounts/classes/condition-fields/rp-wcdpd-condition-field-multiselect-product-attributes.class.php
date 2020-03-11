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
 * Condition Field: Multiselect - Product Attributes
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Product_Attributes
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect_Product_Attributes')) {

class RP_WCDPD_Condition_Field_Multiselect_Product_Attributes extends RP_WCDPD_Condition_Field_Multiselect
{
    protected $key                  = 'product_attributes';
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
        global $wpdb;

        $values_with_children = array();

        foreach ($values as $value) {

            // Determine taxonomy
            $taxonomy = $wpdb->get_var('SELECT taxonomy FROM ' . $wpdb->term_taxonomy . ' WHERE term_id = ' . absint($value));

            // Taxonomy was found
            if ($taxonomy !== null) {
                $values_with_children[$value] = RightPress_Helper::get_term_with_children($value, $taxonomy);
            }
            // Taxonomy was not found
            else {
                $values_with_children[$value] = array($value);
            }
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
        return RightPress_Conditions_Helper::get_all_product_attributes($ids, $query);
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('Select product attributes', 'rp_wcdpd');
    }





}

RP_WCDPD_Condition_Field_Multiselect_Product_Attributes::get_instance();

}
