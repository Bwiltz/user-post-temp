<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Product_Property')) {
    require_once('rp-wcdpd-condition-product-property.class.php');
}

/**
 * Condition: Product Property - Regular Price
 *
 * @class RP_WCDPD_Condition_Product_Property_Regular_Price
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Product_Property_Regular_Price')) {

class RP_WCDPD_Condition_Product_Property_Regular_Price extends RP_WCDPD_Condition_Product_Property
{
    protected $key          = 'regular_price';
    protected $contexts     = array('product_pricing_product', 'product_pricing_bogo_product', 'cart_discounts_product', 'checkout_fees_product');
    protected $method       = 'numeric';
    protected $fields       = array(
        'after'     => array('decimal'),
    );
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
        return __('Product regular price', 'rp_wcdpd');
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
        if (!empty($params['item_id']) || !empty($params['child_id'])) {

            // Select correct product id
            $product_id = !empty($params['child_id']) ? $params['child_id'] : $params['item_id'];

            // Attempt to load product
            if ($product = wc_get_product($product_id)) {

                // Get product regular price
                $price = RightPress_WC_Legacy::product_get_regular_price($product, 'edit');

                // Return price if it is set
                return $price !== '' ? (float) $price : null;
            }
        }

        return null;
    }




}

RP_WCDPD_Condition_Product_Property_Regular_Price::get_instance();

}
