<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field_Select_Timeframe')) {
    require_once('rp-wcdpd-condition-field-select-timeframe.class.php');
}

/**
 * Condition Field: Select - Timeframe Event
 *
 * @class RP_WCDPD_Condition_Field_Select_Timeframe_Event
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Select_Timeframe_Event')) {

class RP_WCDPD_Condition_Field_Select_Timeframe_Event extends RP_WCDPD_Condition_Field_Select_Timeframe
{
    protected $key = 'timeframe_event';

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
        $this->hook();
    }





}

RP_WCDPD_Condition_Field_Select_Timeframe_Event::get_instance();

}
