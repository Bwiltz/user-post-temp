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
 * Condition Group: Purchase History Value
 *
 * @class RP_WCDPD_Condition_Purchase_History_Value
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Purchase_History_Value')) {

abstract class RP_WCDPD_Condition_Purchase_History_Value extends RP_WCDPD_Condition
{
    protected $group_key        = 'purchase_history_value';
    protected $group_position   = 190;
    protected $is_customer      = true;

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
        return __('Purchase History - Value', 'rp_wcdpd');
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return float
     */
    public function get_value($params)
    {
        $value = 0.0;

        // Get all order ids for this customer
        if ($order_ids = RightPress_Conditions_Helper::get_order_ids()) {

            // Iterate over matching order ids
            foreach ($order_ids as $order_id) {

                // Load order
                if ($order = wc_get_order($order_id)) {

                    // Add sum of matching order item values
                    $value += RightPress_Helper::get_wc_order_sum_of_item_values($order->get_items(), array(
                        $this->key => $params['condition'][$this->key],
                    ), RP_WCDPD_Conditions::amounts_include_tax());
                }
            }
        }

        return $value;
    }







}
}
