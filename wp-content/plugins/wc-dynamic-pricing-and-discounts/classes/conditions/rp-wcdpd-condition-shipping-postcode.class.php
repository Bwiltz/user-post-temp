<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Shipping')) {
    require_once('rp-wcdpd-condition-shipping.class.php');
}

/**
 * Condition: Shipping - Postcode
 *
 * @class RP_WCDPD_Condition_Shipping_Postcode
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Shipping_Postcode')) {

class RP_WCDPD_Condition_Shipping_Postcode extends RP_WCDPD_Condition_Shipping
{
    protected $key      = 'postcode';
    protected $contexts = array('cart_discounts', 'checkout_fees');
    protected $method   = 'postcode';
    protected $fields   = array(
        'after' => array('postcode'),
    );
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
        return __('Shipping postcode', 'rp_wcdpd');
    }

    /**
     * Get shipping value
     *
     * @access public
     * @param object $customer
     * @return mixed
     */
    public function get_shipping_value($customer)
    {
        return $customer->get_shipping_postcode();
    }




}

RP_WCDPD_Condition_Shipping_Postcode::get_instance();

}
