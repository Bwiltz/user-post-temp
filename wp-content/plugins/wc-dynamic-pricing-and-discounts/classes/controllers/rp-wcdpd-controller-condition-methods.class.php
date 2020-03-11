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
 * Condition methods controller
 *
 * @class RP_WCDPD_Controller_Condition_Methods
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Controller_Condition_Methods')) {

class RP_WCDPD_Controller_Condition_Methods extends RP_WCDPD_Controller
{
    protected $item_key = 'condition_method';

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

RP_WCDPD_Controller_Condition_Methods::get_instance();

}
