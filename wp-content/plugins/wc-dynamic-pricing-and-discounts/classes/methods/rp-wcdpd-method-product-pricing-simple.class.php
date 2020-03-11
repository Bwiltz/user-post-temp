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
 * Product Pricing Method: Simple
 *
 * @class RP_WCDPD_Method_Product_Pricing_Simple
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Simple')) {

class RP_WCDPD_Method_Product_Pricing_Simple extends RP_WCDPD_Method_Product_Pricing
{
    protected $key              = 'simple';
    protected $group_key        = 'simple';
    protected $group_position   = 10;
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
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return __('Simple', 'rp_wcdpd');
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return __('Simple adjustment', 'rp_wcdpd');
    }

    /**
     * Get cart item adjustments by rule
     *
     * @access public
     * @param array $rule
     * @param array $cart_items
     * @return array
     */
    public function get_adjustments($rule, $cart_items = null)
    {
        $adjustments = array();

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Check if rule applies to current cart item
            if (RP_WCDPD_Conditions::rule_conditions_are_matched($rule, array('cart_item' => $cart_item, 'cart_items' => $cart_items))) {

                // Make sure that rule does not get applied multiple times
                if (!RP_WCDPD_Controller_Methods_Product_Pricing::is_already_processed($rule['uid'], $cart_item_key)) {

                    // Get product base price
                    $base_price = RP_WCDPD_Pricing::get_product_base_price($cart_item['data']);

                    // Add adjustment to main array
                    $adjustments[$cart_item_key] = array(
                        'rule'              => $rule,
                        'reference_amount'  => $this->get_reference_amount(array('rule' => $rule), $base_price, $cart_item['quantity'], $cart_item['data'], $cart_item),
                    );
                }
            }
        }

        return $adjustments;
    }



}

RP_WCDPD_Method_Product_Pricing_Simple::get_instance();

}
