<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field')) {
    require_once('rp-wcdpd-condition-field.class.php');
}

/**
 * Condition Field Group: Decimal
 *
 * @class RP_WCDPD_Condition_Field_Decimal
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Decimal')) {

abstract class RP_WCDPD_Condition_Field_Decimal extends RP_WCDPD_Condition_Field
{

    /**
     * Display field
     *
     * @access public
     * @param string $context
     * @param string $alias
     * @return void
     */
    public function display($context, $alias = 'condition')
    {
        RightPress_Form::decimal($this->get_field_attributes($context, $alias));
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return '0.00';
    }

    /**
     * Get validation rules
     *
     * @access public
     * @return string
     */
    public function get_validation_rules()
    {
        return 'required,number_min_0';
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
        return isset($posted[$this->key]) && !RightPress_Helper::is_empty($posted[$this->key]) && RP_WCDPD_Settings::sanitize_numeric_value($posted[$this->key]) !== false;
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
        $sanitized = RP_WCDPD_Settings::sanitize_numeric_value($posted[$this->key]);
        return abs((float) $sanitized);
    }





}
}
