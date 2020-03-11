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
 * Condition Group: Product Property
 *
 * @class RP_WCDPD_Condition_Product_Property
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Product_Property')) {

abstract class RP_WCDPD_Condition_Product_Property extends RP_WCDPD_Condition
{
    protected $group_key        = 'product_property';
    protected $group_position   = 20;

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
        return __('Product Property', 'rp_wcdpd');
    }




}
}
