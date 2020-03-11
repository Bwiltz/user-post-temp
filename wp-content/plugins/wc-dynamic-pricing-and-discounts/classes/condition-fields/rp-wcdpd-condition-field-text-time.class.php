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
 * Condition Field: Text - Time
 *
 * @class RP_WCDPD_Condition_Field_Text_Time
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Text_Time')) {

class RP_WCDPD_Condition_Field_Text_Time extends RP_WCDPD_Condition_Field_Text
{
    protected $key = 'time';

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
        return 'rp_wcdpd_' . $context . '_' . $alias . '_' . $this->key . ' rp_wcdpd_child_element_field rp_wcdpd_time';
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return __('select time', 'rp_wcdpd');
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
        // New time picker
        if (RightPress_Helper::is_date($posted[$this->key], 'H:i')) {
            return true;
        }
        // Old datepicker (in case old value is submitted again without opening new time picker)
        else if (in_array($posted[$this->key], array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23))) {
            return true;
        }

        return false;
    }

    /**
     * Sanitize field value
     *
     * @access public
     * @param array $posted
     * @param object $condition
     * @param string $method_option_key
     * @return mixed
     */
    public function sanitize($posted, $condition, $method_option_key)
    {
        if (isset($posted[$this->key])) {

            // Already in H:i format
            if (RightPress_Helper::is_date($posted[$this->key], 'H:i')) {
                return (string) $posted[$this->key];
            }
            // Convert from old format
            else {
                $value = strlen((string) $posted[$this->key]) === 1 ? ('0' . $posted[$this->key]) : $posted[$this->key];
                return $value . ':00';
            }
        }

        return null;
    }





}

RP_WCDPD_Condition_Field_Text_Time::get_instance();

}
