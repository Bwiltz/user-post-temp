<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Volume')) {
    require_once('rp-wcdpd-method-product-pricing-volume.class.php');
}

/**
 * Product Pricing Method: Volume Bulk
 *
 * @class RP_WCDPD_Method_Product_Pricing_Volume_Bulk
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Volume_Bulk')) {

class RP_WCDPD_Method_Product_Pricing_Volume_Bulk extends RP_WCDPD_Method_Product_Pricing_Volume
{
    protected $key      = 'bulk';
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
        return __('Bulk pricing', 'rp_wcdpd');
    }

    /**
     * Get matching quantity range keys with allocated cart item quantities
     *
     * @access public
     * @param array $rule
     * @param int $quantity_group
     * @return array
     */
    public function get_quantity_ranges_with_allocated_quantities($rule, $quantity_group)
    {
        $matched = array();

        // Get total quantity
        $total_quantity = array_sum($quantity_group);

        // Iterate over quantity ranges
        foreach ($rule['quantity_ranges'] as $quantity_range_key => $quantity_range) {

            // Check if total quantity falls into current range
            if ($quantity_range['from'] <= $total_quantity && ($quantity_range['to'] === null || $total_quantity <= $quantity_range['to'])) {
                $matched[$quantity_range_key] = $quantity_group;
                break;
            }
        }

        return $matched;
    }


}

RP_WCDPD_Method_Product_Pricing_Volume_Bulk::get_instance();

}
