<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Customer_Value')) {
    require_once('rp-wcdpd-condition-customer-value.class.php');
}

/**
 * Condition: Customer Value - Order Count
 *
 * @class RP_WCDPD_Condition_Customer_Value_Order_Count
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Value_Order_Count')) {

class RP_WCDPD_Condition_Customer_Value_Order_Count extends RP_WCDPD_Condition_Customer_Value
{
    protected $key          = 'order_count';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'before'    => array('timeframe_span'),
        'after'     => array('number'),
    );
    protected $main_field   = 'number';
    protected $position     = 40;

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
        parent::__construct();

        $this->hook();
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return __('Order count', 'rp_wcdpd');
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
        // Get order ids
        if ($order_ids = $this->get_order_ids_by_timeframe($params['condition']['timeframe_span'])) {

            // Return order count
            return count($order_ids);
        }

        return 0;
    }




}

RP_WCDPD_Condition_Customer_Value_Order_Count::get_instance();

}
