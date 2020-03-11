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
 * Condition Field: Text - Text
 *
 * @class RP_WCDPD_Condition_Field_Text_Text
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Text_Text')) {

class RP_WCDPD_Condition_Field_Text_Text extends RP_WCDPD_Condition_Field_Text
{
    protected $key = 'text';

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
        // Value is not empty
        if (isset($posted[$this->key]) && !RightPress_Helper::is_empty($posted[$this->key])) {
            return true;
        }

        // Special case for specific condition methods
        if (in_array($condition->get_method(), array('meta'), true) && in_array($method_option_key, array('is_empty', 'is_not_empty', 'is_checked', 'is_not_checked'), true)) {
            return true;
        }

        return false;
    }





}

RP_WCDPD_Condition_Field_Text_Text::get_instance();

}
