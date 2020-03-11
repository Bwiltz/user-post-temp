<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Purchase_History')) {
    require_once('rp-wcdpd-condition-purchase-history.class.php');
}

/**
 * Condition: Purchase History - Product Tags
 *
 * @class RP_WCDPD_Condition_Purchase_History_Product_Tags
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Purchase_History_Product_Tags')) {

class RP_WCDPD_Condition_Purchase_History_Product_Tags extends RP_WCDPD_Condition_Purchase_History
{
    protected $key          = 'product_tags';
    protected $contexts     = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method       = 'list_advanced';
    protected $fields       = array(
        'before'    => array('timeframe_span'),
        'after'     => array('product_tags'),
    );
    protected $main_field   = 'product_tags';
    protected $position     = 50;

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
        return __('Purchased - Tags', 'rp_wcdpd');
    }

    /**
     * Get value by order
     *
     * @access protected
     * @param int $order_id
     * @return array
     */
    protected function get_purchase_history_value_by_order($order_id)
    {
        return RightPress_Helper::get_wc_order_product_tag_ids($order_id);
    }




}

RP_WCDPD_Condition_Purchase_History_Product_Tags::get_instance();

}
