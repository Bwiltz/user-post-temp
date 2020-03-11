<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product Display Price Override
 *
 * @class RP_WCDPD_Product_Price_Override
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Product_Price_Override')) {

class RP_WCDPD_Product_Price_Override extends RP_WCDPD_WC_Price_Cache
{
    protected $cache_prefix = 'rp_wcdpd';
    protected $priority     = 20;

    protected $rules = null;
    protected $product_condition_values = array();
    protected $non_product_condition_values = null;

    protected $skip_all = false;

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
        add_action('init', array($this, 'on_init'));
    }

    /**
     * On init
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Check if display price override is enabled
        if (!RP_WCDPD_Settings::get('product_pricing_change_display_prices')) {
            return;
        }

        // Construct parent class
        parent::__construct();
    }

    /**
     * Check if price can be changed
     *
     * @access public
     * @param object $product
     * @param float $price
     * @param string $price_type
     * @return bool
     */
    public function proceed($product, $price, $price_type)
    {

        // Don't change prices in admin ui
        if (is_admin() && !defined('DOING_AJAX')) {
            return false;
        }

        // Don't run if cart was not loaded yet (we need to flag products that are in cart first for this to work fine)
        if (!did_action('woocommerce_cart_loaded_from_session')) {
            return false;
        }

        // Make sure that product is not cart item
        if (!empty($product->rp_wcdpd_in_cart)) {
            return false;
        }

        // Already running a pricing test
        if (RP_WCDPD_Controller_Methods_Product_Pricing::running_test()) {
            return false;
        }

        // Don't change regular prices
        if ($price_type === 'regular_price' && RP_WCDPD_Settings::get('product_pricing_display_regular_price')) {
            return false;
        }

        // Get product pricing rules
        $rules = $this->get_rules();

        // No rules configured
        if (empty($rules)) {
            return false;
        }

        return true;
    }

    /**
     * Get rules
     *
     * @access public
     * @return array
     */
    public function get_rules()
    {
        // Rules not loaded yet
        if ($this->rules === null) {

            $params = array();

            // Simple rules only
            if (RP_WCDPD_Settings::get('product_pricing_change_display_prices') === 'change_simple') {
                $params['methods'] = array('simple');
            }

            // Load rules
            $this->rules = RP_WCDPD_Rules::get('product_pricing', $params);
        }

        return $this->rules;
    }

    /**
     * Get cached price validation hash
     * Used to identify outdated cached prices
     *
     * @access public
     * @param object $product
     * @param float $price
     * @param string $price_type
     * @return string
     */
    public function get_hash($product, $price, $price_type)
    {
        global $woocommerce;

        // Data for hash
        $data = array(

            // Request price
            $price_type,
            (float) $price,

            // Prices set in product settings
            (float) RightPress_WC_Legacy::product_get_price($product, 'edit'),
            (float) RightPress_WC_Legacy::product_get_regular_price($product, 'edit'),
            (float) RightPress_WC_Legacy::product_get_sale_price($product, 'edit'),

            // Plugin settings hash
            $this->get_settings_hash($product),

            // Get product condition values
            $this->get_product_condition_values($product),

            // Get non-product condition values
            $this->get_non_product_condition_values(),
        );

        // Hash cart contents if all rule types are used
        if (RP_WCDPD_Settings::get('product_pricing_change_display_prices') === 'change_all') {
            if (is_object($woocommerce) && isset($woocommerce->cart) && is_object($woocommerce->cart) && isset($woocommerce->cart->cart_contents)) {
                $data[] = $woocommerce->cart->cart_contents;
            }
        }

        // Return hash
        return RightPress_Helper::get_hash(false, $data);
    }

    /**
     * Get settings hash
     *
     * @access public
     * @param object $product
     * @return string
     */
    public function get_settings_hash($product)
    {
        return $this->cache_prefix . '_' . RightPress_Helper::get_hash(false, array(
            $this->get_rules(),
            RP_WCDPD_Settings::get('product_pricing_rule_selection_method'),
            RP_WCDPD_Settings::get('product_pricing_sale_price_handling'),
            RP_WCDPD_Settings::get('product_pricing_irrational_price_handling'),
            RP_WCDPD_Settings::get('product_pricing_change_display_prices'),
            RP_WCDPD_Settings::get('condition_amounts_include_tax'),
        ));
    }

    /**
     * Calculate price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return foat
     */
    public function calculate_price($product, $price)
    {
        // Select correct price to base calculations on
        if (RP_WCDPD_Settings::get('product_pricing_sale_price_handling') === 'regular') {

            // Start price observation
            $this->start_observation();

            // Get product id
            $product_id = RightPress_WC_Legacy::product_get_id($product);

            // Run price methods to observe prices
            $product->get_sale_price();
            $product->get_regular_price();

            // Get observed prices
            $observed_prices = $this->get_observed();

            // Extract observed prices
            $_sale_price    = $observed_prices[$product_id]['sale_price'];
            $_regular_price = $observed_prices[$product_id]['regular_price'];

            // Stop price observation
            $this->stop_observation();

            // Choose correct base price
            if ($_sale_price !== '' && $_sale_price < $_regular_price) {
                $base_price = $_regular_price;
            }
            else {
                $base_price = $price;
            }
        }
        else {
            $base_price = $price;
        }

        // Get adjusted price
        $adjusted_price = $this->get_adjusted_price($product, $base_price);

        // Return adjusted price if it was adjusted, otherwise return original price as it was
        // Note: We can't return $adjusted_price in the latter case since $base_price may ignore sale price set in WooCommerce product settings
        return ((string) $adjusted_price !== (string) $base_price) ? $adjusted_price : $price;
    }

    /**
     * Calculate sale price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return foat
     */
    public function calculate_sale_price($product, $price)
    {
        // Get final price
        $final_price = $product->get_price();

        // Get regular price
        $regular_price = $product->get_regular_price();

        // Determine sale price
        return ($final_price < $regular_price) ? $final_price : '';
    }

    /**
     * Calculate regular price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return foat
     */
    public function calculate_regular_price($product, $price)
    {
        if (RP_WCDPD_Settings::get('product_pricing_display_regular_price')) {
            return $price;
        }
        else {
            return $product->get_price();
        }
    }

    /**
     * Get adjusted price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return float
     */
    public function get_adjusted_price($product, $price)
    {
        // All rule types
        if (RP_WCDPD_Settings::get('product_pricing_change_display_prices') === 'change_all') {

            // Get variation attributes
            $variation_attributes = $product->is_type('variation') ? $product->get_variation_attributes() : array();

            // Test product price
            $test_price = RP_WCDPD_Product_Pricing::test_product_price($product, 1, $variation_attributes);

            if ($test_price !== false && $test_price !== null) {
                $price = $test_price;
            }
        }
        // Simple rules only
        else if (RP_WCDPD_Settings::get('product_pricing_change_display_prices') === 'change_simple') {

            $controller = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();

            // Get simple product pricing rules applicable to this product
            $applicable_rules = RP_WCDPD_Product_Pricing::get_applicable_rules_for_product($product, array('simple'));

            // Apply adjustments
            if (is_array($applicable_rules) && !empty($applicable_rules)) {
                foreach ($applicable_rules as $applicable_rule) {
                    if ($method = $controller->get_method_from_rule($applicable_rule)) {
                        $prices = RP_WCDPD_Controller_Methods_Product_Pricing::generate_prices_array($price, 1);
                        $prices = $method->apply_adjustment_to_prices($prices, array('rule' => $applicable_rule));
                        $price = RP_WCDPD_Controller_Methods_Product_Pricing::get_price_from_prices_array($prices, $price, $product);
                    }
                }
            }
        }

        return $price;
    }

    /**
     * Get values for all product conditions for all rules
     *
     * @access public
     * @param object $product
     * @return array
     */
    public function get_product_condition_values($product)
    {
        // Get product id
        $product_id = RightPress_WC_Legacy::product_get_id($product);

        // Get values and store in cache
        if (!isset($this->product_condition_values[$product_id])) {

            // Define condition params
            $params = array(
                'item_id'               => $product->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($product) : $product_id,
                'child_id'              => $product->is_type('variation') ? $product_id : null,
                'variation_attributes'  => $product->is_type('variation') ? $product->get_variation_attributes() : null,
            );

            // Get values
            $this->product_condition_values[$product_id] = $this->get_condition_values(true, $params);
        }

        // Return from cache
        return $this->product_condition_values[$product_id];
    }

    /**
     * Get values for all non-product conditions for all rules
     *
     * @access public
     * @return array
     */
    public function get_non_product_condition_values()
    {
        // Get values and store in cache
        if ($this->non_product_condition_values === null) {
            $this->non_product_condition_values = $this->get_condition_values(false);
        }

        // Return from cache
        return $this->non_product_condition_values;
    }

    /**
     * Get values for all conditions for all rules
     * Checks either product or non-product conditions during one run
     *
     * @access public
     * @param bool $product_conditions
     * @param array $params
     * @return array
     */
    public function get_condition_values($product_conditions, $params = array())
    {
        $values = array();
        $processed = array();

        // Iterate over rules
        foreach ($this->get_rules() as $rule) {

            // Iterate over conditions
            if (!empty($rule['conditions'])) {
                foreach ($rule['conditions'] as $rule_condition) {

                    // Check if condition is product condition
                    $is_product = RP_WCDPD_Conditions::is_group($rule_condition, array('product', 'product_property', 'product_other', 'custom_taxonomy'));

                    // Check if we need to get value for current condition
                    if ($is_product && !$product_conditions || !$is_product && $product_conditions) {
                        continue;
                    }

                    // Set condition
                    $params['condition'] = $rule_condition;

                    // Get condition value
                    if ($condition = RP_WCDPD_Controller_Conditions::get_item($rule_condition['type'])) {
                        $values[][$rule_condition['type']] = $condition->get_value($params);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Maybe skip cache
     *
     * @access public
     * @return bool
     */
    public function skip()
    {
        // We check this already
        if ($this->skip_all) {
            return true;
        }

        // Pricing rules contain customer conditions
        if (RP_WCDPD_Rules::rules_have_condition_groups(array('product_pricing'), array('customer', 'customer_value', 'purchase_history'))) {

            // Customer is logged in
            if (is_user_logged_in()) {
                $this->skip_all = true;
                return true;
            }
        }

        // Pricing rules contain cart conditions
        if (RP_WCDPD_Rules::rules_have_condition_groups(array('product_pricing'), array('cart', 'cart_items', 'cart_item_quantities', 'cart_item_subtotals', 'checkout', 'shipping'))) {

            global $woocommerce;

            // Cart is not empty
            if (is_object($woocommerce) && isset($woocommerce->cart) && is_object($woocommerce->cart) && !empty($woocommerce->cart->cart_contents)) {
                $this->skip_all = true;
                return true;
            }
        }

        // Do not skip cache
        return false;
    }




}

RP_WCDPD_Product_Price_Override::get_instance();

}
