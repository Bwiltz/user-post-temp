<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Limit')) {
    require_once('rp-wcdpd-limit.class.php');
}

/**
 * Product Pricing Limit Controller
 *
 * @class RP_WCDPD_Limit_Product_Pricing
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Limit_Product_Pricing')) {

class RP_WCDPD_Limit_Product_Pricing extends RP_WCDPD_Limit
{
    protected $context = 'product_pricing';

    protected $price_limit = array();

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

    }

    /**
     * Get method controller
     *
     * @access protected
     * @return object
     */
    protected function get_method_controller()
    {
        return RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();
    }

    /**
     * Limit product price discount
     *
     * @access public
     * @param float $discount
     * @param float $reference
     * @param string $cart_item_key
     * @param int $index
     * @return float
     */
    public static function limit_discount($discount, $reference, $cart_item_key, $index)
    {
        $discount = (string) $discount;

        // Discount is not positive
        if ($discount <= 0) {
            return 0.0;
        }

        // Get instance
        $instance = self::get_instance();

        // Limit discount
        $limited_discount = $instance->limit_amount($discount, $reference, $cart_item_key, $index);

        // Return limited discount
        return (float) $limited_discount;
    }

    /**
     * Calculate initial limit
     *
     * @access protected
     * @param float $value
     * @param float $reference
     * @param string $cart_item_key
     * @param int $index
     * @return float|bool
     */
    protected function calculate_initial_limit($value, $reference = null, $cart_item_key = null, $index = null)
    {
        // Cart item details are required but were not provided
        if ($this->is_price_limit() && ($cart_item_key === null || $index === null)) {
            return false;
        }

        // Get initial limit value
        return parent::calculate_initial_limit($value, $reference, $cart_item_key, $index);
    }

    /**
     * Get limit amount
     *
     * @access protected
     * @param string $cart_item_key
     * @param int $index
     * @return float|bool|null
     */
    protected function get_limit($cart_item_key = null, $index = null)
    {
        // Price limit
        if ($this->is_price_limit()) {

            // Limit is set
            if ($cart_item_key !== null && $index !== null && isset($this->price_limit[$cart_item_key][$index])) {
                return $this->price_limit[$cart_item_key][$index];
            }
            // Limit is not set
            else {
                return null;
            }
        }
        // Total limit
        else {
            return $this->total_limit;
        }
    }

    /**
     * Set limit amount
     *
     * @access protected
     * @param flaot $limit
     * @param string $cart_item_key
     * @param int $index
     * @return float|bool|null
     */
    protected function set_limit($limit, $cart_item_key = null, $index = null)
    {
        // Price limit
        if ($this->is_price_limit()) {

            // Set limit
            if ($cart_item_key !== null && $index !== null) {
                $this->price_limit[$cart_item_key][$index] = $limit;
            }
        }
        // Total limit
        else {
            $this->total_limit = $limit;
        }
    }

    /**
     * Check if limit is per product price
     *
     * @access protected
     * @return bool
     */
    protected function is_price_limit()
    {
        return in_array($this->get_method(), array('price_discount_amount', 'price_discount_percentage'), true);
    }

    /**
     * Reset limit
     *
     * @access public
     * @return void
     */
    public static function reset()
    {
        // Get instance
        $instance = RP_WCDPD_Limit_Product_Pricing::get_instance();

        // Reset limits
        $instance->total_limit = null;
        $instance->price_limit = array();
    }





}

RP_WCDPD_Limit_Product_Pricing::get_instance();

}
