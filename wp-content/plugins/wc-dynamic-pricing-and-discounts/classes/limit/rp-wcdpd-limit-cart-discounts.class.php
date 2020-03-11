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
 * Cart Discount Limit Controller
 *
 * @class RP_WCDPD_Limit_Cart_Discounts
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Limit_Cart_Discounts')) {

class RP_WCDPD_Limit_Cart_Discounts extends RP_WCDPD_Limit
{
    protected $context = 'cart_discounts';

    protected $limited_coupon_cart_item_amounts     = array();
    protected $coupon_cart_item_limit               = null;
    protected $processing_coupon_cart_item_limit    = false;

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
        return RP_WCDPD_Controller_Methods_Cart_Discount::get_instance();
    }

    /**
     * Round limited amount
     *
     * @access public
     * @param float $amount
     * @return float
     */
    protected function round($amount)
    {
        return round($amount, wc_get_price_decimals());
    }

    /**
     * Get limited discount amount per coupon and cart item
     *
     * @access public
     * @param float $amount
     * @param string $coupon_code
     * @param array $cart_item_key
     * @param array $rule
     * @return float
     */
    public static function get_limited_discount_amount_per_coupon_and_cart_item($amount, $coupon_code, $cart_item_key, $rule)
    {
        $key = $coupon_code . '__' . $cart_item_key;

        // Get instance
        $instance = RP_WCDPD_Limit_Cart_Discounts::get_instance();

        // Not yet limited, limit now
        if (!isset($instance->limited_coupon_cart_item_amounts[$key])) {

            // Add flag
            $instance->processing_coupon_cart_item_limit = true;

            // Get method controller
            $method_controller = $instance->get_method_controller();

            // Get method
            if ($rule_method = $method_controller->get_method_from_rule($rule)) {

                // Get limited amount
                $instance->limited_coupon_cart_item_amounts[$key] = $instance->get_method() ? $instance->round($instance->limit_amount($amount, $rule_method->get_cart_subtotal())) : $amount;
            }
            else {

                // Unable to limit discount, applying zero discount to be safe
                $instance->limited_coupon_cart_item_amounts[$key] = 0.0;
            }

            // Remove flag
            $instance->processing_coupon_cart_item_limit = false;
        }

        // Return limited amount
        return $instance->limited_coupon_cart_item_amounts[$key];
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
        return $this->processing_coupon_cart_item_limit ? $this->coupon_cart_item_limit : $this->total_limit;
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
        if ($this->processing_coupon_cart_item_limit) {
            $this->coupon_cart_item_limit = $limit;
        }
        else {
            $this->total_limit = $limit;
        }
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
        $instance = RP_WCDPD_Limit_Cart_Discounts::get_instance();

        // Reset limit
        $instance->total_limit = null;

        // Reset coupon cart item limit
        $instance->processing_coupon_cart_item_limit = null;
    }




}

RP_WCDPD_Limit_Cart_Discounts::get_instance();

}
