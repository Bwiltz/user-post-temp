<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Pricing_Method_Fee_Per_Cart_Item')) {
    require_once('rp-wcdpd-pricing-method-fee-per-cart-item.class.php');
}

/**
 * Pricing Method: Fee Per Cart Item - Percentage
 *
 * @class RP_WCDPD_Pricing_Method_Fee_Per_Cart_Item_Percentage
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Pricing_Method_Fee_Per_Cart_Item_Percentage')) {

class RP_WCDPD_Pricing_Method_Fee_Per_Cart_Item_Percentage extends RP_WCDPD_Pricing_Method_Fee_Per_Cart_Item
{
    protected $key      = 'percentage';
    protected $contexts = array('checkout_fees_simple');
    protected $position = 20;

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
        parent::__construct();

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
        return __('Percentage fee per item', 'rp_wcdpd');
    }

    /**
     * Calculate adjustment value
     *
     * @access public
     * @param float $setting
     * @param float $amount
     * @param array $adjustment
     * @return float
     */
    public function calculate($setting, $amount = 0, $adjustment = null)
    {
        // Get conditions
        $conditions = (is_array($adjustment) && !empty($adjustment['rule']['conditions'])) ? $adjustment['rule']['conditions'] : array();

        // Get cart item subtotal for calculation
        $subtotal = RP_WCDPD_Conditions::get_sum_of_cart_item_subtotals_by_product_conditions($conditions, false);

        // Calculate adjustment
        return (float) ($subtotal * $setting / 100);

    }





}

RP_WCDPD_Pricing_Method_Fee_Per_Cart_Item_Percentage::get_instance();

}
