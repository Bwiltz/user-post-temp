<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Customer')) {
    require_once('rp-wcdpd-condition-customer.class.php');
}

/**
 * Condition: Customer - Meta
 *
 * @class RP_WCDPD_Condition_Customer_Meta
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Customer_Meta')) {

class RP_WCDPD_Condition_Customer_Meta extends RP_WCDPD_Condition_Customer
{
    protected $key          = 'meta';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'meta';
    protected $fields       = array(
        'before'    => array('meta_key'),
        'after'     => array('text'),
    );
    protected $main_field   = 'text';
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
        return __('User meta', 'rp_wcdpd');
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
        // User must be logged in
        if (is_user_logged_in()) {

            // Get meta from database
            $user_meta = RightPress_WC_Meta::customer_get_meta(get_current_user_id(), $params['condition']['meta_key'], false, 'edit');
            return RightPress_WC_Meta::normalize_meta_data($user_meta);
        }

        return array();
    }




}

RP_WCDPD_Condition_Customer_Meta::get_instance();

}
