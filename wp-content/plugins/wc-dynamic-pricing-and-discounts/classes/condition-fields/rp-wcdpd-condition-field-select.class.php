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
 * Condition Field Group: Select
 *
 * @class RP_WCDPD_Condition_Field_Select
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Select')) {

abstract class RP_WCDPD_Condition_Field_Select extends RP_WCDPD_Condition_Field
{
    protected $is_grouped = false;

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
        RightPress_Form::select($this->get_field_attributes($context, $alias), false, $this->is_grouped);
    }





}
}
