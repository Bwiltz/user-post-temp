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
 * Condition Group: Cart Items
 *
 * @class RP_WCDPD_Condition_Cart_Items
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Items')) {

abstract class RP_WCDPD_Condition_Cart_Items extends RP_WCDPD_Condition
{
    protected $group_key        = 'cart_items';
    protected $group_position   = 120;
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
        return __('Cart Items', 'rp_wcdpd');
    }




}
}
