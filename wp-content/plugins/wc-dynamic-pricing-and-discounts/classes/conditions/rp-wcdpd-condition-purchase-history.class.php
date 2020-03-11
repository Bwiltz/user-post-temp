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
 * Condition Group: Purchase History
 *
 * @class RP_WCDPD_Condition_Purchase_History
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Purchase_History')) {

abstract class RP_WCDPD_Condition_Purchase_History extends RP_WCDPD_Condition
{
    protected $group_key        = 'purchase_history';
    protected $group_position   = 170;
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
        return __('Purchase History', 'rp_wcdpd');
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
        $value = array();

        // Get order ids
        if ($order_ids = $this->get_order_ids_by_timeframe($params['condition']['timeframe_span'])) {

            // Iterate over matching order ids
            foreach ($order_ids as $order_id) {

                // Get ids for current order
                $current = $this->get_purchase_history_value_by_order($order_id);

                // Add to array
                $value = array_merge($value, $current);
            }
        }

        return array_unique($value);
    }




}
}
