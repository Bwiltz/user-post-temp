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
 * Condition Field: Text - Meta Key
 *
 * @class RP_WCDPD_Condition_Field_Text_Meta_Key
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Text_Meta_Key')) {

class RP_WCDPD_Condition_Field_Text_Meta_Key extends RP_WCDPD_Condition_Field_Text
{
    protected $key = 'meta_key';

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
        return __('meta field key', 'rp_wcdpd');
    }





}

RP_WCDPD_Condition_Field_Text_Meta_Key::get_instance();

}
