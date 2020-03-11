<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Cart
 *
 * @class RP_WCDPD_WC_Cart
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_WC_Cart')) {

class RP_WCDPD_WC_Cart
{

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
        // Maybe automatically add BXGYF product to cart
        add_action('woocommerce_add_to_cart', array($this, 'maybe_add_free_product_to_cart'), 10, 6);
    }

    /**
     * Sort cart items by price
     *
     * @access public
     * @param array $cart_items
     * @param string $sort_order
     * @return array
     */
    public static function sort_cart_items_by_price($cart_items = null, $sort_order = 'ascending')
    {
        // Get cart items if not passed in
        if ($cart_items === null) {
            global $woocommerce;
            $cart_items = $woocommerce->cart->cart_contents;
        }

        // Sort cart items
        $sort_comparison_method = 'sort_cart_items_by_price_' . $sort_order . '_comparison';
        RP_WCDPD_Helper::stable_uasort($cart_items, array('RP_WCDPD_WC_Cart', $sort_comparison_method));

        // Return sorted cart items
        return $cart_items;
    }

    /**
     * Sort cart items by price ascending comparison method
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    public static function sort_cart_items_by_price_ascending_comparison($a, $b)
    {
        return RP_WCDPD_WC_Cart::sort_cart_items_by_price_comparison($a, $b, 'ascending');
    }

    /**
     * Sort cart items by price descending comparison method
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    public static function sort_cart_items_by_price_descending_comparison($a, $b)
    {
        return RP_WCDPD_WC_Cart::sort_cart_items_by_price_comparison($a, $b, 'descending');
    }

    /**
     * Sort cart items by price comparison method
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @param string $sort_order
     * @return bool
     */
    public static function sort_cart_items_by_price_comparison($a, $b, $sort_order)
    {
        // Get cart item prices
        $price_a = $a['data']->get_price();
        $price_b = $b['data']->get_price();

        // Compare prices
        if ($price_a < $price_b) {
            return ($sort_order === 'ascending' ? -1 : 1);
        }
        else if ($price_a > $price_b) {
            return ($sort_order === 'ascending' ? 1 : -1);
        }
        else {
            return 0;
        }
    }

    /**
     * Calculate our own cart subtotal since in some cases it may not be available yet
     *
     * Note: looks like line_subtotal and line_subtotal_tax are not always set
     * when this is called, however calculating by adding $product->get_price()
     * is not a good idea. Need to monitor if it affects anything from the users
     * perspective.
     *
     * @access public
     * @return float
     */
    public static function calculate_subtotal($include_tax = false)
    {
        global $woocommerce;

        $subtotal = 0;

        // Iterate over cart items
        foreach ($woocommerce->cart->get_cart() as $cart_item) {

            if (isset($cart_item['line_subtotal'])) {

                // Add line subtotal
                $subtotal += $cart_item['line_subtotal'];

                // Add line subtotal tax
                if (isset($cart_item['line_subtotal_tax']) && $include_tax) {
                    $subtotal += $cart_item['line_subtotal_tax'];
                }
            }
        }

        return $subtotal;
    }

    /**
     * Get cart item price for display including or exluding of tax depending on corresponding WooCommerce setting
     *
     * @access public
     * @param array $cart_item
     * @param float $price
     * @return float
     */
    public static function get_cart_item_price_for_display($cart_item, $price = null)
    {
        // Reference cart item product
        $product = $cart_item['data'];

        // Get product price
        if ($price === null) {
            $price = RightPress_WC_Legacy::product_get_price($product);
        }

        // Include or exclude tax
        if (get_option('woocommerce_tax_display_cart') === 'excl') {
            $price = RightPress_WC_Legacy::product_get_price_excluding_tax($product, 1, $price);
        }
        else {
            $price = RightPress_WC_Legacy::product_get_price_including_tax($product, 1, $price);
        }

        return (float) $price;
    }

    /**
     * Remove all regular coupons
     *
     * @access public
     * @return void
     */
    public static function remove_all_regular_coupons()
    {
        global $woocommerce;

        // Get applied coupons
        $applied_coupons = $woocommerce->cart->get_applied_coupons();

        if (is_array($applied_coupons) && !empty($applied_coupons)) {
            foreach ($applied_coupons as $applied_coupon) {
                if (!RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($applied_coupon)) {
                    $woocommerce->cart->remove_coupon($applied_coupon);
                    wc_add_notice(sprintf(__('Sorry, coupon "%s" is not valid when other discounts are applied to the cart.', 'rp_wcdpd'), $applied_coupon), 'error');
                }
            }
        }
    }

    /**
     * Maybe automatically add BXGYF product to cart
     *
     * @access public
     * @param string $cart_item_key
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @param array $variation
     * @param array $cart_item_data
     * @return void
     */
    public function maybe_add_free_product_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        global $woocommerce;

        // Functionality disabled
        if (!RP_WCDPD_Settings::get('product_pricing_bxgyf_auto_add')) {
            return;
        }

        // Get BXGY rules
        foreach (RP_WCDPD_Rules::get('product_pricing', array('methods' => array('bogo', 'bogo_repeat'))) as $rule_key => $rule) {

            // Check if current rule is applicable to product that was just added to cart
            $matched = RP_WCDPD_Conditions::rule_conditions_are_matched($rule, array(
                'item_id'               => ($product_id ? $product_id : null),
                'child_id'              => ($variation_id ? $variation_id : null),
                'variation_attributes'  => ($variation ? $variation : null),
            ));

            // Not applicable
            if (!$matched) {
                continue;
            }

            // Rule must provide an explicit 100% discount
            if (!($rule['bogo_pricing_method'] === 'discount__percentage' && $rule['bogo_pricing_value'] == 100) && !($rule['bogo_pricing_method'] === 'fixed__price' && $rule['bogo_pricing_value'] == 0)) {
                continue;
            }

            // Rule must have exactly one "get" product condition defined
            if (empty($rule['bogo_product_conditions']) || !is_array($rule['bogo_product_conditions']) || count($rule['bogo_product_conditions']) > 1) {
                continue;
            }

            // Reference "get" condition
            $condition = array_pop($rule['bogo_product_conditions']);

            // Condition must be either product or product variation
            if (!RP_WCDPD_Conditions::is_type($condition, array('product__product', 'product__variation'))) {
                continue;
            }

            $is_variation = RP_WCDPD_Conditions::is_type($condition, 'product__variation');

            // Product condition must have exactly one product selected
            if (!$is_variation && (empty($condition['products']) || !is_array($condition['products']) || count($condition['products']) > 1)) {
                continue;
            }

            // Product variation condition must have exactly one product variation selected
            if ($is_variation && (empty($condition['product_variations']) || !is_array($condition['product_variations']) || count($condition['product_variations']) > 1)) {
                continue;
            }

            // Get id
            $id = $is_variation ? array_pop($condition['product_variations']) : array_pop($condition['products']);

            // Load product
            $product = wc_get_product($id);

            // Unable to load product
            if (!$product) {
                continue;
            }

            // Product type mismatch
            if ((!$is_variation && !$product->is_type('simple')) || ($is_variation && !$product->is_type('variation'))) {
                continue;
            }

            // Get variation attributes
            $variation_attributes = $is_variation ? $product->get_variation_attributes() : array();

            // Variation contains undefined attributes
            if ($is_variation) {

                $found = false;

                foreach ($variation_attributes as $attribute) {
                    if ($attribute == '') {
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    continue;
                }
            }

            // Make sure product is not in cart yet
            if ($cart_items = $woocommerce->cart->get_cart()) {

                $found = false;

                foreach ($cart_items as $cart_item) {
                    if (($is_variation && $cart_item['variation_id'] == $id) || (!$is_variation && $cart_item['product_id'] == $id)) {
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    continue;
                }
            }

            // Purchase quantity
            $purchase_quantity = $rule['bogo_purchase_quantity'];

            // Quantity added to cart is lower than purchase quantity
            if ($quantity < $purchase_quantity) {
                continue;
            }

            // Get multiplier
            $multiplier = (int) ($quantity / $purchase_quantity);

            // Receive quantity
            $receive_quantity = $rule['bogo_receive_quantity'] * ($rule['method'] === 'bogo_repeat' ? $multiplier : 1);

            // Run a pricing test to make sure this product would be free if it was added to cart
            $price_data = RP_WCDPD_Product_Pricing::test_product_price($product, $receive_quantity, $variation_attributes, true);

            // Price is not zero
            if (!isset($price_data['price']) || !is_numeric($price_data['price']) || $price_data['price'] != 0) {
                continue;
            }

            // Current rule did not adjust product price
            if (empty($price_data['data']['adjustments']) || !is_array($price_data['data']['adjustments']) || !in_array($rule['uid'], array_keys($price_data['data']['adjustments']), true)) {
                continue;
            }

            // Allow other plugins to skip this action
            if (apply_filters('rp_wcdpd_add_free_product_to_cart', true, $product, $rule, $cart_item_key)) {

                $product_id = RP_WCDPD_WC_Product::product_get_absolute_id($product);
                $variation_id = $is_variation ? $id : 0;

                // Add free product to cart
                remove_action('woocommerce_add_to_cart', array($this, 'maybe_add_free_product_to_cart'));
                $woocommerce->cart->add_to_cart($product_id, $receive_quantity, $variation_id, $variation_attributes);
                add_action('woocommerce_add_to_cart', array($this, 'maybe_add_free_product_to_cart'), 10, 6);
            }

            // Do not check other rules
            break;
        }
    }



}

RP_WCDPD_WC_Cart::get_instance();

}
