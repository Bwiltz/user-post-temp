<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Cart')) {
    require_once('rp-wcdpd-condition-cart.class.php');
}

/**
 * Condition: Cart - Coupons
 *
 * @class RP_WCDPD_Condition_Cart_Coupons
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Coupons')) {

class RP_WCDPD_Condition_Cart_Coupons extends RP_WCDPD_Condition_Cart
{
    protected $key      = 'coupons';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'coupons';
    protected $fields   = array(
        'after' => array('coupons'),
    );
    protected $position = 50;

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
        return __('Coupons applied', 'rp_wcdpd');
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
        $coupon_ids = RightPress_Helper::get_wc_cart_applied_coupon_ids();

        // Remove coupons with empty id - they are not real coupons and most probably are our cart discounts
        foreach ($coupon_ids as $coupon_id_key => $coupon_id) {
            if ($coupon_id === 0) {
                unset($coupon_ids[$coupon_id_key]);
            }
        }

        return $coupon_ids;
    }




}

RP_WCDPD_Condition_Cart_Coupons::get_instance();

}
