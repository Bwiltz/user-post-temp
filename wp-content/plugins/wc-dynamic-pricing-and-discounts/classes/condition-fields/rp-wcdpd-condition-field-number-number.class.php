<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field_Number')) {
    require_once('rp-wcdpd-condition-field-number.class.php');
}

/**
 * Condition Field: Number - Number
 *
 * @class RP_WCDPD_Condition_Field_Number_Number
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Number_Number')) {

class RP_WCDPD_Condition_Field_Number_Number extends RP_WCDPD_Condition_Field_Number
{
    protected $key = 'number';

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

RP_WCDPD_Condition_Field_Number_Number::get_instance();

}
