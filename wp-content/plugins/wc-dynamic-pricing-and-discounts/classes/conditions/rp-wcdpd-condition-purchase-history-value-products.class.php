<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Purchase_History_Value')) {
    require_once('rp-wcdpd-condition-purchase-history-value.class.php');
}

/**
 * Condition: Purchase History Value - Products
 *
 * @class RP_WCDPD_Condition_Purchase_History_Value_Products
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Purchase_History_Value_Products')) {

class RP_WCDPD_Condition_Purchase_History_Value_Products extends RP_WCDPD_Condition_Purchase_History_Value
{
    protected $key          = 'products';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'numeric';
    protected $fields       = array(
        'before'    => array('products'),
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
        return __('Value purchased - Products', 'rp_wcdpd');
    }




}

RP_WCDPD_Condition_Purchase_History_Value_Products::get_instance();

}
