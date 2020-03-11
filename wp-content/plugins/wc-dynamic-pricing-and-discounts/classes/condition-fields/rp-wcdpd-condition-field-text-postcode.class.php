<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field_Text')) {
    require_once('rp-wcdpd-condition-field-text.class.php');
}

/**
 * Condition Field: Text - Postcode
 *
 * @class RP_WCDPD_Condition_Field_Text_Postcode
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Text_Postcode')) {

class RP_WCDPD_Condition_Field_Text_Postcode extends RP_WCDPD_Condition_Field_Text
{
    protected $key = 'postcode';

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

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('e.g. 90210, 902**, 90200-90299, SW1A 1AA, NSW 2001', 'rp_wcdpd');
    }





}

RP_WCDPD_Condition_Field_Text_Postcode::get_instance();

}
