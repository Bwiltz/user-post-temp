<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Product_Other')) {
    require_once('rp-wcdpd-condition-product-other.class.php');
}

/**
 * Condition: Product Other - Pricing Rules Applied
 *
 * Note: This is only for backwards compatibility, not displayed on new setups
 *
 * @class RP_WCDPD_Condition_Product_Other_Pricing_Rules_Applied
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Product_Other_Pricing_Rules_Applied')) {

class RP_WCDPD_Condition_Product_Other_Pricing_Rules_Applied extends RP_WCDPD_Condition_Product_Other
{
    protected $key      = 'pricing_rules_applied';
    protected $contexts = array('cart_discounts_product');
    protected $method   = 'boolean';
    protected $position = 10;

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
        return __('Any pricing rule applied', 'rp_wcdpd');
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
        if (!empty($params['cart_item']) && isset($params['cart_item']['rp_wcdpd_data'])) {
            return true;
        }

        return false;
    }




}

RP_WCDPD_Condition_Product_Other_Pricing_Rules_Applied::get_instance();

}
