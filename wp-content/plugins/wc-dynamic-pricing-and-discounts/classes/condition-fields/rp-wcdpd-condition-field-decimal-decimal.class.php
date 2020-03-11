<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field_Decimal')) {
    require_once('rp-wcdpd-condition-field-decimal.class.php');
}

/**
 * Condition Field: Decimal - Decimal
 *
 * @class RP_WCDPD_Condition_Field_Decimal_Decimal
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Decimal_Decimal')) {

class RP_WCDPD_Condition_Field_Decimal_Decimal extends RP_WCDPD_Condition_Field_Decimal
{
    protected $key = 'decimal';

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

RP_WCDPD_Condition_Field_Decimal_Decimal::get_instance();

}
