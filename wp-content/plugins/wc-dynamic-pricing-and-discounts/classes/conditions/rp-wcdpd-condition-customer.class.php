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
 * Condition Group: Customer
 *
 * @class RP_WCDPD_Condition_Customer
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer')) {

abstract class RP_WCDPD_Condition_Customer extends RP_WCDPD_Condition
{
    protected $group_key        = 'customer';
    protected $group_position   = 150;
    protected $is_customer      = true;

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
        return __('Customer', 'rp_wcdpd');
    }




}
}
