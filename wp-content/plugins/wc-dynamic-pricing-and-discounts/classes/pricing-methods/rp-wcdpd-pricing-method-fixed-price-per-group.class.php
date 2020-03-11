<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Pricing_Method_Fixed')) {
    require_once('rp-wcdpd-pricing-method-fixed.class.php');
}

/**
 * Pricing Method: Fixed - Price Per Group
 *
 * @class RP_WCDPD_Pricing_Method_Fixed_Price_Per_Group
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Pricing_Method_Fixed_Price_Per_Group')) {

class RP_WCDPD_Pricing_Method_Fixed_Price_Per_Group extends RP_WCDPD_Pricing_Method_Fixed
{
    protected $key      = 'price_per_group';
    protected $contexts = array('product_pricing_group');
    protected $position = 30;

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
        return __('Fixed price per group', 'rp_wcdpd');
    }





}

RP_WCDPD_Pricing_Method_Fixed_Price_Per_Group::get_instance();

}
