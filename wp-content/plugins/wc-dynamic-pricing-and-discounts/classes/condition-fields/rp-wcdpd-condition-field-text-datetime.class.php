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
 * Condition Field: Text - Datetime
 *
 * @class RP_WCDPD_Condition_Field_Text_Datetime
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Text_Datetime')) {

class RP_WCDPD_Condition_Field_Text_Datetime extends RP_WCDPD_Condition_Field_Text
{
    protected $key = 'datetime';

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
     * Get class
     *
     * @access public
     * @param string $context
     * @param string $alias
     * @return string
     */
    public function get_class($context, $alias = 'condition')
    {
        return 'rp_wcdpd_' . $context . '_' . $alias . '_' . $this->key . ' rp_wcdpd_child_element_field rp_wcdpd_datetime';
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('select date and time', 'rp_wcdpd');
    }

    /**
     * Validate field value
     *
     * @access public
     * @param array $posted
     * @param object $condition
     * @param string $method_option_key
     * @return bool
     */
    public function validate($posted, $condition, $method_option_key)
    {
        return isset($posted[$this->key]) && RightPress_Helper::is_date($posted[$this->key], 'Y-m-d H:i');
    }





}

RP_WCDPD_Condition_Field_Text_Datetime::get_instance();

}
