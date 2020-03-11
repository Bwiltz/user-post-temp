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
 * Condition Group: Custom Taxonomy
 *
 * @class RP_WCDPD_Condition_Custom_Taxonomy
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Custom_Taxonomy')) {

abstract class RP_WCDPD_Condition_Custom_Taxonomy extends RP_WCDPD_Condition
{
    protected $group_key        = 'custom_taxonomy';
    protected $group_position   = 30;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
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
        return __('Custom Taxonomy', 'rp_wcdpd');
    }

    /**
     * Check if condition is enabled
     *
     * @access public
     * @return bool
     */
    public function is_enabled()
    {
        // Custom taxonomy conditions are always enabled -
        // they are not loaded at all when they are not used
        return true;
    }




}
}
