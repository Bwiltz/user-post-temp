<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Product')) {
    require_once('rp-wcdpd-condition-product.class.php');
}

/**
 * Condition: Product - Product
 *
 * @class RP_WCDPD_Condition_Product_Product
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Product_Product')) {

class RP_WCDPD_Condition_Product_Product extends RP_WCDPD_Condition_Product
{
    protected $key      = 'product';
    protected $contexts = array('product_pricing_product', 'product_pricing_bogo_product', 'product_pricing_group_product', 'cart_discounts_product', 'checkout_fees_product');
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('products'),
    );
    protected $position = 10;

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
        return __('Product', 'rp_wcdpd');
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
        if (!empty($params['item_id'])) {
            return $params['item_id'];
        }

        return null;
    }




}

RP_WCDPD_Condition_Product_Product::get_instance();

}
