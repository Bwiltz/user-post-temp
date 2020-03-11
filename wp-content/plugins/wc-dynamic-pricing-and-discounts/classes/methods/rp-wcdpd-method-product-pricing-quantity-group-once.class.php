<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_Group')) {
    require_once('rp-wcdpd-method-product-pricing-quantity-group.class.php');
}

/**
 * Product Pricing Method: Group Once
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity_Group_Once
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_Group_Once')) {

class RP_WCDPD_Method_Product_Pricing_Quantity_Group_Once extends RP_WCDPD_Method_Product_Pricing_Quantity_Group
{
    protected $key      = 'group';
    protected $position = 10;

    // Other properties
    protected $repeat = false;

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
        return __('Group of products', 'rp_wcdpd');
    }


}

RP_WCDPD_Method_Product_Pricing_Quantity_Group_Once::get_instance();

}
