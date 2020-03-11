<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing')) {
    require_once('rp-wcdpd-method-product-pricing.class.php');
}

/**
 * Product Pricing Method: Exclude
 *
 * @class RP_WCDPD_Method_Product_Pricing_Exclude
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Exclude')) {

class RP_WCDPD_Method_Product_Pricing_Exclude extends RP_WCDPD_Method_Product_Pricing
{
    protected $key              = 'exclude';
    protected $group_key        = 'other';
    protected $group_position   = 50;
    protected $position         = 10;

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
        $this->hook_group();
        $this->hook();

        // Exclude cart items based on rules
        add_filter('rp_wcdpd_product_pricing_cart_items', array($this, 'exclude_cart_items'));
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return __('Other', 'rp_wcdpd');
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return __('Exclude products from all rules', 'rp_wcdpd');
    }

    /**
     * Get adjustments
     *
     * Note: This method does not offer any adjustments therefore we need to override parent method
     *
     * @access public
     * @param array $rule
     * @param array $cart_items
     * @return array
     */
    public function get_adjustments($rule, $cart_items = null)
    {
        return array();
    }

    /**
     * Exclude cart items based on rules
     *
     * @access public
     * @param array $cart_items
     * @return array
     */
    public function exclude_cart_items($cart_items)
    {
        // Get all product pricing rules
        $rules = RP_WCDPD_Rules::get('product_pricing', array(
            'methods'       => array('exclude'),
            'cart_items'    => $cart_items,
        ));

        // Exclude items that have at least one matching exclude rule
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if (RP_WCDPD_Conditions::exclude_item_by_rules($rules, array('cart_item' => $cart_item, 'cart_items' => $cart_items))) {
                unset($cart_items[$cart_item_key]);
            }
        }

        // Return remaining cart items
        return $cart_items;
    }



}

RP_WCDPD_Method_Product_Pricing_Exclude::get_instance();

}
