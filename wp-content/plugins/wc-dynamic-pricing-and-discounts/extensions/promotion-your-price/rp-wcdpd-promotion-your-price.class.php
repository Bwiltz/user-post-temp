<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Promotion: Your Price
 *
 * @class RP_WCDPD_Promotion_Your_Price
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Promotion_Your_Price')) {

class RP_WCDPD_Promotion_Your_Price
{

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
        // Register settings structure
        add_filter('rp_wcdpd_settings_structure', array($this, 'register_settings_structure'), 100);

        // Set up promotion tool
        add_action('init', array($this, 'set_up_promotion_tool'));
    }

    /**
     * Register settings structure
     *
     * @access public
     * @param array $settings
     * @return array
     */
    public function register_settings_structure($settings)
    {
        $settings['promo']['children']['your_price'] = array(
            'title' => __('Your Price', 'rp_wcdpd'),
            'info'  => __('Displays a dynamically updated price on a single product page. This price reflects all pricing adjustments that would be applicable if a specified quantity was added to cart.', 'rp_wcdpd'),
            'children' => array(
                'promo_your_price' => array(
                    'title'     => __('Enable', 'rp_wcdpd'),
                    'type'      => 'checkbox',
                    'default'   => '0',
                ),
                'promo_your_price_label' => array(
                    'title'     => __('Label', 'rp_wcdpd'),
                    'type'      => 'text',
                    'default'   => __('Your Price:', 'rp_wcdpd'),
                    'required'  => false,
                ),
            ),
        );

        return $settings;
    }

    /**
     * Set up promotion tool
     *
     * @access public
     * @return void
     */
    public function set_up_promotion_tool()
    {
        // Check this promotion tool is active
        if (!RP_WCDPD_Settings::get('promo_your_price')) {
            return;
        }

        // Load shared live product price update functionality
        require_once RP_WCDPD_PLUGIN_PATH . 'rightpress/components/rightpress-live-product-price-update/rightpress-live-product-price-update.class.php';

        // Filter price
        add_filter('rightpress_live_product_price_update', array($this, 'get_price'), 1, 4);
    }

    /**
     * Get price filter callback
     *
     * Note: this callback must always be executed before any other callbacks
     * since it takes its own price and ignores the provided one
     *
     * @access public
     * @param array $price_data
     * @param object $product
     * @param int $quantity
     * @param array $variation_attributes
     * @return float
     */
    public function get_price($price_data, $product, $quantity = 1, $variation_attributes = array())
    {
        // Get test price
        $test_price = RP_WCDPD_Product_Pricing::test_product_price($product, $quantity, $variation_attributes);

        // Check if price would be adjusted
        if ($test_price !== null && $test_price !== false) {

            // Set price and label
            $data = array(
                'price' => $test_price,
                'label' => RP_WCDPD_Settings::get('promo_your_price_label'),
            );

            // Add to changeset
            $price_data['changeset']['wc-dynamic-pricing-and-discounts'] = $data;

            // Overwrite main properties
            $price_data = array_merge($price_data, $data);
        }

        // Return value
        return $price_data;
    }




}

RP_WCDPD_Promotion_Your_Price::get_instance();

}
