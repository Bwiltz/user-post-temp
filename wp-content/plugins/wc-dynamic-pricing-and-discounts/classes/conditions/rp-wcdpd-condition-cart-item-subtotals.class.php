<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition')) {
    require_once('rp-wcdpd-condition.class.php');
}

/**
 * Condition Group: Cart Item Subtotals
 *
 * @class RP_WCDPD_Condition_Cart_Item_Subtotals
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Cart_Item_Subtotals')) {

abstract class RP_WCDPD_Condition_Cart_Item_Subtotals extends RP_WCDPD_Condition
{
    protected $group_key        = 'cart_item_subtotals';
    protected $group_position   = 140;
    protected $is_cart          = true;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->hook_group();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return __('Cart Items - Subtotal', 'rp_wcdpd');
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_value($params)
    {
        if (!empty($params['condition'][$this->key])) {

            $sum = RightPress_Helper::get_wc_cart_sum_of_item_subtotals(array(
                $this->key => $params['condition'][$this->key],
            ), RP_WCDPD_Conditions::amounts_include_tax());

            return RightPress_Helper::get_amount_in_currency($sum, array('realmag777'));
        }

        return null;
    }

    /**
     * Get condition value
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_condition_value($params)
    {
        // Load field
        if ($field_key = $this->get_main_field()) {
            if ($field = RP_WCDPD_Controller_Condition_Fields::get_item($field_key)) {
                if (isset($params['condition'][$field_key])) {
                    return RightPress_Helper::get_amount_in_currency($params['condition'][$field_key]);
                }
            }
        }

        return null;
    }




}
}
