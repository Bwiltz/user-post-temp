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
 * Condition: Shipping - State
 *
 * @class RP_WCDPD_Condition_Shipping_State
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Shipping_State')) {

class RP_WCDPD_Condition_Shipping_State extends RP_WCDPD_Condition_Shipping
{
    protected $key      = 'state';
    protected $contexts = array('cart_discounts', 'checkout_fees');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('states'),
    );
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
        return __('Shipping state', 'rp_wcdpd');
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
        $shipping_country = $customer->get_shipping_country();
        $shipping_state = $customer->get_shipping_state();

        if ($shipping_country && $shipping_state) {
            return $shipping_country . '_' . $shipping_state;
        }

        return null;
    }




}

RP_WCDPD_Condition_Shipping_State::get_instance();

}
