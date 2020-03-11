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
 * Condition Field: Multiselect - Custom Taxonomy
 *
 * This is a special condition field - it is instantiated with different
 * settings for each custom taxonomy that is enabled
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Custom_Taxonomy
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Multiselect_Custom_Taxonomy')) {

class RP_WCDPD_Condition_Field_Multiselect_Custom_Taxonomy extends RP_WCDPD_Condition_Field_Multiselect
{
    protected $key                  = null;
    protected $taxonomy_key         = null;
    protected $supports_hierarchy   = null;

    /**
     * Constructor class
     *
     * @access public
     * @param string $key
     * @param string $taxonomy_key
     * @param bool $supports_hierarchy
     * @return void
     */
    public function __construct($key, $taxonomy_key, $supports_hierarchy)
    {
        // Set properties
        $this->key                  = $key;
        $this->taxonomy_key         = $taxonomy_key;
        $this->supports_hierarchy   = $supports_hierarchy;

        // Hook dynamic condition field
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
            $values_with_children[$value] = RightPress_Helper::get_term_with_children($value, $this->taxonomy_key);
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
        // Load taxonomy
        if ($taxonomy = get_taxonomy($this->taxonomy_key)) {

            // Hierarchical
            if ($taxonomy->hierarchical) {
                return RightPress_Conditions_Helper::get_all_hierarchical_taxonomy_terms($this->taxonomy_key, $ids, $query);
            }
            // Non-hierarchical
            else {
                return RightPress_Conditions_Helper::get_all_non_hierarchical_taxonomy_terms($this->taxonomy_key, $ids, $query);
            }
        }

        // No options found
        return array();
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('Select taxonomy terms', 'rp_wcdpd');
    }





}
}
