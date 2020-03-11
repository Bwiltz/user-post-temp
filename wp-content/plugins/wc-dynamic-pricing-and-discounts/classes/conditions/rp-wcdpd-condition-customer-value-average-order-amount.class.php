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
 * Condition: Customer Value - Average Order Amount
 *
 * @class RP_WCDPD_Condition_Customer_Value_Average_Order_Amount
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Value_Average_Order_Amount')) {

class RP_WCDPD_Condition_Customer_Value_Average_Order_Amount extends RP_WCDPD_Condition_Customer_Value
{
    protected $key          = 'average_order_amount';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'before'    => array('timeframe_span'),
        'after'     => array('decimal'),
    );
    protected $main_field   = 'decimal';
    protected $position     = 20;

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
        return __('Spent - Average per order', 'rp_wcdpd');
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
        $total = 0.0;
        $number = 0;
        $value = 0.0;

        // Get order ids
        if ($order_ids = $this->get_order_ids_by_timeframe($params['condition']['timeframe_span'])) {

            // Get amount spent
            foreach ($order_ids as $order_id) {
                if ($order = wc_get_order($order_id)) {
                    $total += (float) RightPress_Conditions_Helper::order_get_total($order);
                    $number++;
                }
            }

            // Get average amount
            if ($total && $number) {
                return $total/$number;
            }
        }

        return $value;
    }




}

RP_WCDPD_Condition_Customer_Value_Average_Order_Amount::get_instance();

}
