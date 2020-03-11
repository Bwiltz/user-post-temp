<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Custom_Taxonomy')) {
    require_once('rp-wcdpd-condition-custom-taxonomy.class.php');
}

/**
 * Condition: Custom Taxonomy - Product
 *
 * This is a special condition - it is instantiated with different settings for
 * each custom taxonomy that is enabled
 *
 * @class RP_WCDPD_Condition_Custom_Taxonomy_Product
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Custom_Taxonomy_Product')) {

class RP_WCDPD_Condition_Custom_Taxonomy_Product extends RP_WCDPD_Condition_Custom_Taxonomy
{
    protected $contexts = array('product_pricing_product', 'product_pricing_bogo_product', 'product_pricing_group_product', 'cart_discounts_product', 'checkout_fees_product');
    protected $method   = 'list';
    protected $position = 10;

    protected $key      = null;
    protected $fields   = null;
    protected $label    = null;

    /**
     * Constructor class
     *
     * @access public
     * @param string $key
     * @param array $fields
     * @return void
     */
    public function __construct($key, $fields, $label)
    {
        // Set properties
        $this->key      = $key;
        $this->fields   = $fields;
        $this->label    = $label;

        // Run parent constructor
        parent::__construct();

        // Hook dynamic condition
        $this->hook();
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return $this->label;
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_value($params)
    {
        $terms = array();

        // No parent or child ids set
        if (empty($params['item_id']) && empty($params['child_id'])) {
            return null;
        }

        // Get terms by child
        if (!empty($params['child_id'])) {
            $terms = get_the_terms($params['child_id'], $this->key);
        }

        // Get terms by parent
        if ((!is_array($terms) || empty($terms)) && !empty($params['item_id'])) {
            $terms = get_the_terms($params['item_id'], $this->key);
        }

        // No terms returned
        if (!is_array($terms) || empty($terms)) {
            return null;
        }

        // Get ids
        $term_ids = wp_list_pluck($terms, 'term_id');
        $term_ids = array_map('strval', $term_ids);

        // Return term ids
        return $term_ids;
    }




}
}
