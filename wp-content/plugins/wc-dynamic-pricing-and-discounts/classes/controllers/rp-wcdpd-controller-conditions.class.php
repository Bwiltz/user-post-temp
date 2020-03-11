<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Controller')) {
    require_once('rp-wcdpd-controller.class.php');
}

/**
 * Conditions controller
 *
 * @class RP_WCDPD_Controller_Conditions
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Controller_Conditions')) {

class RP_WCDPD_Controller_Conditions extends RP_WCDPD_Controller
{
    protected $item_key             = 'condition';
    protected $items_are_grouped    = true;

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
        // Generate custom taxonomy conditions
        add_action('wp_loaded', array($this, 'generate_custom_taxonomy_conditions'));

        // Ajax handlers
        add_action('wp_ajax_rp_wcdpd_load_multiselect_options', array($this, 'ajax_load_multiselect_options'));
    }

    /**
     * Generate custom taxonomy conditions
     *
     * @access public
     * @return void
     */
    public function generate_custom_taxonomy_conditions()
    {
        // Get enabled taxonomies
        $enabled = RP_WCDPD_Settings::get('conditions_custom_taxonomies');

        // Get available taxonomies
        $available = get_taxonomies(array(), 'objects');

        // Generate conditions for available enabled taxonomies
        if (!empty($enabled)) {
            foreach ($available as $taxonomy) {
                if (in_array($taxonomy->name, $enabled, true)) {

                    $condition_field_key = ('custom_taxonomy_' . $taxonomy->name);

                    // Instantiate condition
                    new RP_WCDPD_Condition_Custom_Taxonomy_Product($taxonomy->name, array('after' => array($condition_field_key)), $taxonomy->name);

                    // Instantiate confition field
                    new RP_WCDPD_Condition_Field_Multiselect_Custom_Taxonomy($condition_field_key, $taxonomy->name, $taxonomy->hierarchical);
                }
            }
        }
    }

    /**
     * Load multiselect options
     *
     * @access public
     * @return void
     */
    public function ajax_load_multiselect_options()
    {
        $options = array();

        // Check if required vars are set
        if (!empty($_POST['type']) && !empty($_POST['query'])) {

            // Load corresponding condition field
            if ($field = RP_WCDPD_Controller_Condition_Fields::get_item($_POST['type'])) {

                // Get options
                $options = $field->get_multiselect_options(array(
                    'query'     => $_POST['query'],
                    'selected'  => (!empty($_POST['selected']) && is_array($_POST['selected'])) ? $_POST['selected'] : array(),
                ));
            }
        }

        // Return data and exit
        echo RightPress_Helper::json_encode_multiselect_options(array('results' => array_values($options)));
        exit;
    }




}

RP_WCDPD_Controller_Conditions::get_instance();

}
