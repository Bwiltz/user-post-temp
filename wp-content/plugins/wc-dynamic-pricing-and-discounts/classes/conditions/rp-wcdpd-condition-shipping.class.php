<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition')) {
    require_once('rp-wcdpd-condition.class.php');
}

/**
 * Condition Group: Shipping
 *
 * @class RP_WCDPD_Condition_Shipping
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Shipping')) {

abstract class RP_WCDPD_Condition_Shipping extends RP_WCDPD_Condition
{
    protected $group_key        = 'shipping';
    protected $group_position   = 210;
    protected $is_cart          = true;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->hook_group();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return __('Shipping Address', 'rp_wcdpd');
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
        global $woocommerce;

        $value = null;

        // Attempt to get value
        if (is_object($woocommerce->customer)) {
            $value = $this->get_shipping_value($woocommerce->customer);
        }

        return !RightPress_Helper::is_empty($value) ? $value : null;
    }




}
}
