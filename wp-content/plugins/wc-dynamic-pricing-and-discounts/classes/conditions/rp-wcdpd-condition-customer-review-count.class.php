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
 * Condition: Customer Value - Review Count
 *
 * @class RP_WCDPD_Condition_Customer_Value_Review_Count
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Value_Review_Count')) {

class RP_WCDPD_Condition_Customer_Value_Review_Count extends RP_WCDPD_Condition_Customer_Value
{
    protected $key          = 'review_count';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'after' => array('number'),
    );
    protected $position     = 60;

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
        return __('Customer review count', 'rp_wcdpd');
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
        $count = 0;

        // Get user id
        $user_id = isset($params['customer_id']) ? $params['customer_id'] : (is_user_logged_in() ? get_current_user_id() : null);

        // Get by customer id
        if ($user_id) {
            $reviews = get_comments(array('fields' => 'ids', 'post_type' => 'product', 'user_id' => $user_id));
            $count += count($reviews);
        }

        // Get billing email address
        $billing_email = RightPress_Conditions_Helper::get_checkout_billing_email();

        // Get by billing email (but only those made by guest user so that we don't count the same ones we found when querying by user id)
        if ($billing_email) {
            $reviews = get_comments(array('fields' => 'ids', 'post_type' => 'product', 'user_id' => 0, 'author_email' => $billing_email));
            $count += count($reviews);
        }

        return $count;
    }




}

RP_WCDPD_Condition_Customer_Value_Review_Count::get_instance();

}
