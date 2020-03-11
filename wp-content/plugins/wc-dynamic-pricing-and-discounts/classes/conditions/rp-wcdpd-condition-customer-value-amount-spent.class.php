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
 * Condition: Customer Value - Amount Spent
 *
 * @class RP_WCDPD_Condition_Customer_Value_Amount_Spent
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Value_Amount_Spent')) {

class RP_WCDPD_Condition_Customer_Value_Amount_Spent extends RP_WCDPD_Condition_Customer_Value
{
    protected $key          = 'amount_spent';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'before'    => array('timeframe_span'),
        'after'     => array('decimal'),
    );
    protected $main_field   = 'decimal';
    protected $position     = 10;

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
        return __('Spent - Total', 'rp_wcdpd');
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
        $value = 0.0;

        // Get order ids
        if ($order_ids = $this->get_order_ids_by_timeframe($params['condition']['timeframe_span'])) {

            // Get amount spent
            foreach ($order_ids as $order_id) {
                if ($order = wc_get_order($order_id)) {
                    $value += (float) RightPress_Conditions_Helper::order_get_total($order);
                }
            }
        }

        return $value;
    }




}

RP_WCDPD_Condition_Customer_Value_Amount_Spent::get_instance();

}
