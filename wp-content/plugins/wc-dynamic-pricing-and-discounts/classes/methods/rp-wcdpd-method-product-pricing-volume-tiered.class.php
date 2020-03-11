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
 * Product Pricing Method: Volume Tiered
 *
 * @class RP_WCDPD_Method_Product_Pricing_Volume_Tiered
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Volume_Tiered')) {

class RP_WCDPD_Method_Product_Pricing_Volume_Tiered extends RP_WCDPD_Method_Product_Pricing_Volume
{
    protected $key      = 'tiered';
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
        return __('Tiered pricing', 'rp_wcdpd');
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

            // Include range if quantity falls into it
            if ($quantity_range['from'] === null || $quantity_range['from'] <= $total_quantity) {

                // Find out overlapping ranges
                $count_from_incl    = ($quantity_range['from'] === null ? 1 : $quantity_range['from']);
                $count_to_incl      = (($quantity_range['to'] === null || $total_quantity <= $quantity_range['to']) ? $total_quantity : $quantity_range['to']);

                // Track progress
                $working_quantity = 0;

                // Iterate over cart items in a quantity group
                foreach ($quantity_group as $cart_item_key => $cart_item_quantity) {

                    // Find out overlapping ranges
                    $current_count_from_incl    = $working_quantity + 1;
                    $current_count_to_incl      = $working_quantity + $cart_item_quantity;

                    // Update working quantity
                    $working_quantity = $current_count_to_incl;

                    // Skip this cart item as it does not fall into current range
                    if ($working_quantity < $count_from_incl) {
                        continue;
                    }

                    // Update current overlapping ranges
                    $current_count_from_incl    = ($current_count_from_incl > $count_from_incl ? $current_count_from_incl : $count_from_incl);
                    $current_count_to_incl      = ($current_count_to_incl < $count_to_incl ? $current_count_to_incl : $count_to_incl);

                    // Add cart item to matched array
                    $matched[$quantity_range_key][$cart_item_key] = ($current_count_to_incl - $current_count_from_incl + 1);

                    // Nothing else fits into this range
                    if ($working_quantity >= $count_to_incl) {
                        break;
                    }
                }
            }

            // Stop iterating if this was the last range that quantity falls into
            if ($quantity_range['to'] === null || $quantity_range['to'] >= $total_quantity) {
                break;
            }
        }

        return $matched;
    }



}

RP_WCDPD_Method_Product_Pricing_Volume_Tiered::get_instance();

}
