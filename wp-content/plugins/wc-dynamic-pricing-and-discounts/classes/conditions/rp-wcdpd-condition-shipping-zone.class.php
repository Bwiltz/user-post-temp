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
 * Condition: Shipping - Zone
 *
 * Available since WooCommerce 2.6
 *
 * @class RP_WCDPD_Condition_Shipping_Zone
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Shipping_Zone')) {

class RP_WCDPD_Condition_Shipping_Zone extends RP_WCDPD_Condition_Shipping
{
    protected $key      = 'zone';
    protected $contexts = array('cart_discounts', 'checkout_fees');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('shipping_zones'),
    );
    protected $position = 40;

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

        // Only load this condition type on WC 2.6+
        if (RightPress_Helper::wc_version_gte('2.6')) {
            $this->hook();
        }
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return __('Shipping zone', 'rp_wcdpd');
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
        // Get shipping zone
        $zone = wc_get_shipping_zone(array(
            'destination' => array(
                'country'   => $customer->get_shipping_country(),
                'state'     => $customer->get_shipping_state(),
                'postcode'  => $customer->get_shipping_postcode(),
            ),
        ));

        // Return shipping zone id
        return $zone ? (string) $zone->get_id() : null;
    }




}

RP_WCDPD_Condition_Shipping_Zone::get_instance();

}
