<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY')) {
    require_once('rp-wcdpd-method-product-pricing-quantity-bogo-xy.class.php');
}

/**
 * Product Pricing Method: BOGO XY Repeat
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Repeat
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Repeat')) {

class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Repeat extends RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY
{
    protected $key      = 'bogo_repeat';
    protected $position = 40;

    // Other properties
    protected $repeat = true;

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
        return __('Buy x get y - Repeating', 'rp_wcdpd');
    }


}

RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Repeat::get_instance();

}
