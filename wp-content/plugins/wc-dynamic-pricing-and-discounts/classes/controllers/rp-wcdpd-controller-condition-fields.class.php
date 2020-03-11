<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Controller')) {
    require_once('rp-wcdpd-controller.class.php');
}

/**
 * Condition fields controller
 *
 * @class RP_WCDPD_Controller_Condition_Fields
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Controller_Condition_Fields')) {

class RP_WCDPD_Controller_Condition_Fields extends RP_WCDPD_Controller
{
    protected $item_key = 'condition_field';

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




}

RP_WCDPD_Controller_Condition_Fields::get_instance();

}
