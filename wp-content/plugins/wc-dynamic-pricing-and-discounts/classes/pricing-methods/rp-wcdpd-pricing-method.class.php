<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent pricing method class
 *
 * @class RP_WCDPD_Pricing_Method
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Pricing_Method')) {

abstract class RP_WCDPD_Pricing_Method extends RP_WCDPD_Item
{
    protected $item_key = 'pricing_method';

    /**
     * Calculate adjustment value
     *
     * @access public
     * @param float $setting
     * @param float $amount
     * @param array $adjustment
     * @return float
     */
    public function calculate($setting, $amount = 0, $adjustment = null)
    {
        return RightPress_Helper::get_amount_in_currency($setting, array('aelia'));
    }

    /**
     * Adjust amount
     *
     * @access public
     * @param float $amount
     * @param float $setting
     * @return float
     */
    public function adjust($amount, $setting)
    {
        $amount += $this->calculate($setting, $amount);
        return (float) ($amount >= 0 ? $amount : 0);
    }





}
}
