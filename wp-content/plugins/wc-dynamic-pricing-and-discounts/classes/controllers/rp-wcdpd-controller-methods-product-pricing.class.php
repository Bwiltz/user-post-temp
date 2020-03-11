<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Controller_Methods')) {
    require_once('rp-wcdpd-controller-methods.class.php');
}

/**
 * Product Pricing method controller
 *
 * @class RP_WCDPD_Controller_Methods_Product_Pricing
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Controller_Methods_Product_Pricing')) {

class RP_WCDPD_Controller_Methods_Product_Pricing extends RP_WCDPD_Controller_Methods
{
    protected $context = 'product_pricing';

    // Track which rules were processed for cart items
    protected $rules_processed = array();

    // Flag to indicate when system is running a product price test
    protected $running_test = false;

    // Store cart item price composition
    protected $price_composition = array();

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
        // Apply pricing rules to cart
        add_action('woocommerce_cart_loaded_from_session', array($this, 'cart_loaded_from_session'), 100);
        add_action('woocommerce_before_calculate_totals', array($this, 'apply'), 100);
        add_action('woocommerce_applied_coupon', array($this, 'apply'), 100);

        // Maybe change cart item display price
        add_filter('woocommerce_cart_item_price', array($this, 'cart_item_price'), 10, 3);
    }

    /**
     * Check if coupon is being applied in this request
     *
     * @access public
     * @return bool
     */
    public static function coupon_is_being_applied()
    {
        return (!empty($_POST['apply_coupon']) && !empty($_POST['coupon_code']));
    }

    /**
     * Cart loaded from session
     *
     * @access public
     * @param object $cart
     * @return void
     */
    public function cart_loaded_from_session($cart)
    {
        if (!defined('RP_WCDPD_CART_LOADED_FROM_SESSION')) {
            define('RP_WCDPD_CART_LOADED_FROM_SESSION', true);
        }

        // Iterate over cart items
        foreach ($cart->cart_contents as $cart_item_key => $cart_item) {

            // Add flag that indicates that cart item's product is in cart
            $cart->cart_contents[$cart_item_key]['data']->rp_wcdpd_in_cart = true;

            // Unset any previously set adjustment meta data
            if (isset($cart->cart_contents[$cart_item_key]['rp_wcdpd_data'])) {
                unset($cart->cart_contents[$cart_item_key]['rp_wcdpd_data']);
            }
        }

        // Apply pricing rules to cart
        $this->apply($cart);
    }

    /**
     * Apply pricing rules to cart
     *
     * @access public
     * @param object $cart
     * @return void
     */
    public function apply($cart = null)
    {
        // Do not run before woocommerce_cart_loaded_from_session is run
        if (!defined('RP_WCDPD_CART_LOADED_FROM_SESSION')) {
            return;
        }

        // Special handling for "apply_coupon" requests - pricing rules must
        // be applied after coupon is applied to get coupon conditions right
        if (RP_WCDPD_Controller_Methods_Product_Pricing::coupon_is_being_applied()) {
            if (current_action() !== 'woocommerce_applied_coupon' && !did_action('woocommerce_applied_coupon')) {
                return;
            }
        }

        // Load cart if not passed in
        if (!is_a($cart, 'WC_Cart')) {

            global $woocommerce;

            // Cart not instantiated yet
            if (!isset($woocommerce->cart) || !is_object($woocommerce->cart)) {
                return;
            }

            // Reference cart
            $cart = $woocommerce->cart;
        }

        // Cart is empty
        if (!is_array($cart->cart_contents) || empty($cart->cart_contents)) {
            return;
        }

        // Get cart item changes
        $changes = RP_WCDPD_Controller_Methods_Product_Pricing::get_change_set($cart->cart_contents);

        // Nothing to apply - trigger action
        if (empty($changes)) {
            do_action('rp_wcdpd_product_pricing_nothing_to_apply');
        }

        $triggered = array();

        // Apply changes if any
        foreach ($changes as $cart_item_key => $cart_item_changes) {

            // Rule applied - trigger action
            foreach ($cart_item_changes['data']['adjustments'] as $rule_uid => $data) {
                if (!in_array($rule_uid, $triggered, true)) {
                    do_action('rp_wcdpd_product_pricing_rule_applied_to_cart', $rule_uid, $data);
                    $triggered[] = $rule_uid;
                }
            }

            // Set price
            RightPress_WC_Legacy::product_set_price($cart->cart_contents[$cart_item_key]['data'], $cart_item_changes['price']);

            // Set extra data
            $cart->cart_contents[$cart_item_key]['rp_wcdpd_data'] = $cart_item_changes['data'];
        }

        // Recalculate totals
        if (current_filter() !== 'woocommerce_before_calculate_totals') {
            $cart->calculate_totals();
        }
    }

    /**
     * Get changes for cart items
     *
     * Allows limiting which cart item to get pricing changes for and for how
     * many quantity units (counting from the lastly added)
     *
     * @access public
     * @param array $cart_items
     * @param string $cart_item_key_target
     * @param int $quantity_target
     * @return array
     */
    public static function get_change_set($cart_items, $cart_item_key_target = null, $quantity_target = null)
    {
        $changes = array();

        // Reset limits
        RP_WCDPD_Limit_Product_Pricing::reset();

        // Get controller instance
        $controller = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();

        // Sort cart items by price from cheapest
        $cart_items = RP_WCDPD_WC_Cart::sort_cart_items_by_price($cart_items, 'ascending');

        // Apply exclude rules and allow developers to exclude items
        $cart_items = apply_filters('rp_wcdpd_product_pricing_cart_items', $cart_items);

        // Maybe exclude items that are already on sale
        if (RP_WCDPD_Settings::get('product_pricing_sale_price_handling') === 'exclude') {
            $cart_items = $controller->exclude_cart_items_already_on_sale($cart_items);
        }

        // Get applicable product pricing adjustments
        $adjustments = $controller->get_applicable_adjustments($cart_items);

        // Iterate over adjustments for cart items
        foreach ($adjustments as $cart_item_key => $cart_item_adjustments) {

            // Specific cart item requested
            if ($cart_item_key_target !== null && $cart_item_key_target !== $cart_item_key) {
                continue;
            }

            // Get current extra data
            $data = !empty($cart_items[$cart_item_key]['rp_wcdpd_data']) ? $cart_items[$cart_item_key]['rp_wcdpd_data'] : array();

            // Get cart item quantity
            $cart_item_quantity = $cart_items[$cart_item_key]['quantity'];

            // Get base price
            if (RP_WCDPD_Controller_Methods_Product_Pricing::running_test() && isset($data['initial_price'])) {
                $base_price = $data['initial_price'];
            }
            else {
                $base_price = RP_WCDPD_Pricing::get_product_base_price($cart_items[$cart_item_key]['data']);
            }

            // Get final WooCommerce product price without any adjustments to use for items that were not adjusted
            // Note: We can't use base price as it depends on setting product_pricing_sale_price_handling
            if (RP_WCDPD_Controller_Methods_Product_Pricing::running_test() && isset($data['wc_price'])) {
                $wc_price = $data['wc_price'];
            }
            else {
                $wc_price = RightPress_WC_Legacy::product_get_price($cart_items[$cart_item_key]['data'], 'edit');
            }

            // Set initial cart item price
            $data['initial_price'] = apply_filters('rp_wcdpd_product_pricing_initial_price', $base_price, $cart_items[$cart_item_key]);

            // Set final WooCommerce product price without any adjustments
            $data['wc_price'] = $wc_price;

            // Store individual price for each quantity unit
            $prices = RP_WCDPD_Controller_Methods_Product_Pricing::generate_prices_array($base_price, $cart_item_quantity);

            // Filter cart item adjustments by rule selection method and exclusivity settings
            $cart_item_adjustments = RP_WCDPD_Rules::filter_by_exclusivity($controller->context, $cart_item_adjustments);

            // Apply remaining cart item adjustments
            foreach ($cart_item_adjustments as $rule_uid => $adjustment) {

                // Apply adjustment to prices of current cart item
                if ($method = $controller->get_method_from_rule($adjustment['rule'])) {
                    $prices = $method->apply_adjustment_to_prices($prices, $adjustment, $cart_item_key);
                }

                // Sort prices so that units with least discounts come up first for the next rule
                RP_WCDPD_Helper::stable_uasort($prices, array('RP_WCDPD_Controller_Methods_Product_Pricing', 'sort_prices_by_adjustment_count_asc'));
            }

            // Maybe store price composition
            $controller->maybe_store_price_compositon($cart_item_key, $prices, $wc_price);

            // Regular handling
            if ($quantity_target === null) {

                // Calculate average price from prices array
                $average_price = RP_WCDPD_Controller_Methods_Product_Pricing::get_price_from_prices_array($prices, $wc_price, $cart_items[$cart_item_key]['data'], $cart_items[$cart_item_key]);
            }
            // Specified number of quantity units
            else {

                // Calculate average price for specified number of quantity units
                $average_price = RP_WCDPD_Controller_Methods_Product_Pricing::get_price_from_prices_array_for_quantity_target($prices, $wc_price, $quantity_target);

                // Price for specified number of quantity units was not adjusted
                if ($average_price === null) {
                    continue;
                }
            }

            // Set adjustments to extra data
            foreach ($cart_item_adjustments as $rule_uid => $adjustment) {
                $data['adjustments'][$rule_uid] = array();
            }

            // Add to changes set
            $changes[$cart_item_key] = array(
                'price' => $average_price,
                'data'  => $data,
            );
        }

        return $changes;
    }

    /**
     * Maybe store price composition
     *
     * @access public
     * @param string $cart_item_key
     * @param array $prices
     * @param float $wc_price
     * @return void
     */
    public function maybe_store_price_compositon($cart_item_key, $prices, $wc_price)
    {
        $composition = array();

        // Running a pricing test
        if (RP_WCDPD_Controller_Methods_Product_Pricing::running_test()) {
            return;
        }

        // Get decimals setting
        $decimals = wc_get_price_decimals();
        $decimals = $decimals > 1 ? $decimals : 1;

        // Iterate over prices
        foreach ($prices as $price) {

            // Get key
            $key = number_format($price['adjusted'], $decimals);

            // Not yet added
            if (!isset($composition[$key])) {
                $composition[$key] = array(
                    'quantity'  => 1,
                    'price'     => $price['adjusted'],
                );
            }
            // Already added - increment quantity
            else {
                $composition[$key]['quantity']++;
            }

            // Add missing adjustments
            if (!empty($price['adjustments'])) {
                foreach ($price['adjustments'] as $rule_uid => $adjustment) {
                    if (!isset($composition[$key]['adjustments'][$rule_uid])) {
                        $composition[$key]['adjustments'][$rule_uid] = $adjustment;
                    }
                }
            }
        }

        // Fix prices with no adjustments
        foreach ($composition as $key => $value) {
            if (empty($value['adjustments'])) {

                // Unset current entry
                unset($composition[$key]);

                // Get new key
                $new_key = number_format($wc_price, $decimals);

                // Not yet added
                if (!isset($composition[$new_key])) {
                    $composition[$new_key] = array_merge($value, array('price' => $wc_price));
                }
                // Already added
                else {
                    $composition[$new_key]['quantity']++;
                }
            }
        }

        // Reset any previously set price composition data for this cart item
        if (isset($this->price_composition[$cart_item_key])) {
            unset($this->price_composition[$cart_item_key]);
        }

        // Set for display if at least two different prices are used
        if (count($composition) > 1) {

            // Sort list of prices
            krsort($composition);

            // Set for display
            $this->price_composition[$cart_item_key] = $composition;
        }
    }

    /**
     * Maybe change cart item display price
     *
     * @access public
     * @param string $price_html
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function cart_item_price($price_html, $cart_item, $cart_item_key)
    {
        // Check if pricing was adjusted for this cart item
        if (isset($cart_item['rp_wcdpd_data']['initial_price'])) {

            // Get initial price including potential tax
            $initial_price = RP_WCDPD_WC_Cart::get_cart_item_price_for_display($cart_item, $cart_item['rp_wcdpd_data']['initial_price']);

            // realmag777 currency switcher compatibility
            $initial_price = apply_filters('woocs_convert_price', $initial_price, false);

            // Display multiple prices
            if (isset($this->price_composition[$cart_item_key])) {

                $price_html = '';

                // Iterate over prices
                foreach ($this->price_composition[$cart_item_key] as $price_data) {

                    // Tax adjustment
                    if (get_option('woocommerce_tax_display_cart') === 'excl') {
                        $price = RightPress_WC_Legacy::product_get_price_excluding_tax($cart_item['data'], 1, $price_data['price']);
                    }
                    else {
                        $price = RightPress_WC_Legacy::product_get_price_including_tax($cart_item['data'], 1, $price_data['price']);
                    }

                    // Format current price
                    $current_price = wc_price($price);

                    // Maybe prepend initial price
                    if ($this->price_was_discounted($price, $initial_price) && RP_WCDPD_Settings::get('product_pricing_display_regular_price')) {
                        $current_price = '<del>' . wc_price($initial_price) . '</del> <ins>' . $current_price . '</ins>';
                    }

                    // Maybe add public description
                    if (!empty($price_data['adjustments']) && $this->prices_differ($price, $initial_price)) {
                        $current_price = $this->maybe_add_public_description($current_price, array_keys($price_data['adjustments']));
                    }

                    // Append to string
                    $price_html .= '<div style="float: left;">' . $current_price . '</div><div style="float: right; padding-left: 1em;">&times; ' . $price_data['quantity'] . '</div><div style="clear: both;"></div>';
                }

                $price_html = '<div style="display: inline-block;">' . $price_html . '</div>';
            }
            // Display single price
            else {

                // Get adjusted price
                $adjusted_price = RP_WCDPD_WC_Cart::get_cart_item_price_for_display($cart_item);

                // realmag777 currency switcher compatibility
                $adjusted_price = apply_filters('woocs_convert_price', $adjusted_price, false);

                // Adjusted price is lower than initial price
                if ($this->price_was_discounted($adjusted_price, $initial_price) && RP_WCDPD_Settings::get('product_pricing_display_regular_price')) {
                    $price_html = '<del>' . wc_price($initial_price) . '</del> <ins>' . wc_price($adjusted_price) . '</ins>';
                }

                // Maybe add public description
                $price_html = $this->maybe_add_public_description($price_html, array_keys($cart_item['rp_wcdpd_data']['adjustments']));
            }
        }

        return $price_html;
    }

    /**
     * Check if price was discounted
     *
     * Only used for display purposes, not actual calculation
     *
     * @access public
     * @param float $adjusted_price
     * @param float $initial_price
     * @return bool
     */
    public function price_was_discounted($adjusted_price, $initial_price)
    {
        $decimals = wc_get_price_decimals();
        return (string) round($adjusted_price, $decimals) < (string) round($initial_price, $decimals);
    }

    /**
     * Check if prices differ
     *
     * Only used for display purposes, not actual calculation
     *
     * @access public
     * @param float $adjusted_price
     * @param float $initial_price
     * @return bool
     */
    public function prices_differ($adjusted_price, $initial_price)
    {
        $decimals = wc_get_price_decimals();
        return (string) round($adjusted_price, $decimals) != (string) round($initial_price, $decimals);
    }

    /**
     * Exclude cart items that are already on sale
     *
     * @access public
     * @param array $cart_items
     * @return array
     */
    public function exclude_cart_items_already_on_sale($cart_items)
    {
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if ($cart_item['data']->is_on_sale()) {
                unset($cart_items[$cart_item_key]);
            }
        }

        return $cart_items;
    }

    /**
     * Check if rule is already processed for cart item
     * Mark processed if it is not processed yet
     *
     * @access public
     * @param string $rule_key
     * @param string $cart_item_key
     * @return bool
     */
    public static function is_already_processed($rule_key, $cart_item_key)
    {
        // Get instance
        $instance = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();

        // Only testing
        if (RP_WCDPD_Controller_Methods_Product_Pricing::running_test()) {
            return false;
        }

        // Rule already processed
        if (isset($instance->rules_processed[$rule_key]) && in_array($cart_item_key, $instance->rules_processed[$rule_key], true)) {
            return true;
        }
        // Rule not processed yet - mark as processed
        else {
            $instance->rules_processed[$rule_key][] = $cart_item_key;
            return false;
        }
    }

    /**
     * Sort prices by adjustment count ascending
     *
     * @access public
     * @param object $a
     * @param object $b
     * @return array
     */
    public static function sort_prices_by_adjustment_count_asc($a, $b)
    {
        $count_a = count($a['adjustments']);
        $count_b = count($b['adjustments']);

        // Compare
        if ($count_a > $count_b) {
            return 1;
        }
        else if ($count_a < $count_b) {
            return -1;
        }
        else {
            return 0;
        }
    }

    /**
     * Generate prices array for a cart item
     *
     * @access public
     * @param float $base_price
     * @param int $cart_item_quantity
     * @return array
     */
    public static function generate_prices_array($base_price, $cart_item_quantity)
    {
        $prices = array();

        // Fix base price
        $base_price = (float) $base_price;

        // Iterate over quantity units
        for ($i = 1; $i <= $cart_item_quantity; $i++) {
            $prices[$i] = array(
                'original'      => $base_price,
                'adjusted'      => $base_price,
                'adjustments'   => array(),
            );
        }

        return $prices;
    }

    /**
     * Calculate average price from prices array
     *
     * @access public
     * @param array $prices
     * @param float $wc_price
     * @param object $product
     * @param array $cart_item
     * @return float
     */
    public static function get_price_from_prices_array($prices, $wc_price, $product, $cart_item = null)
    {
        $subtotal = 0.0;
        $count = 0;

        // Iterate over prices
        foreach ($prices as $price) {

            // Price was adjusted
            if (!empty($price['adjustments'])) {
                $subtotal += $price['adjusted'];
            }
            // Price was not adjusted
            else {
                $subtotal += $wc_price;
            }

            // Increase count
            $count++;
        }

        // Calculate average price
        $average_price = $subtotal / $count;

        // Round cart item price so that we end up with correct cart line subtotal
        $average_price = RP_WCDPD_Pricing::round($average_price);

        // Allow developers to override
        $average_price = apply_filters('rp_wcdpd_product_pricing_adjusted_price', $average_price, $prices, $product, $cart_item);

        // Return price
        return $average_price;
    }

    /**
     * Calculate average price from prices array
     *
     * @access public
     * @param array $prices
     * @param float $wc_price
     * @param int $quantity_target
     * @return float|null
     */
    public static function get_price_from_prices_array_for_quantity_target($prices, $wc_price, $quantity_target = null)
    {
        $subtotal = 0.0;
        $count = 0;
        $adjusted = false;

        // Restore prices sort order and reverse it
        RP_WCDPD_Helper::stable_asort($prices);
        $prices = array_reverse($prices, true);

        // Iterate over prices
        foreach ($prices as $price) {

            // Price was adjusted
            if (!empty($price['adjustments'])) {
                $subtotal += $price['adjusted'];
                $adjusted = true;
            }
            // Price was not adjusted
            else {
                $subtotal += $wc_price;
            }

            // Increase count
            $count++;

            // Price for a specific number of quantity units was requested
            if ($quantity_target !== null && $count >= $quantity_target) {
                break;
            }
        }

        // Price was not adjusted
        if (!$adjusted) {
            return null;
        }

        // Calculate average price
        $average_price = $subtotal / $count;

        // Round cart item price so that we end up with correct cart line subtotal
        $average_price = RP_WCDPD_Pricing::round($average_price);

        // Return price
        return $average_price;
    }

    /**
     * Check if system is currently running a product pricing test
     * See RP_WCDPD_Product_Pricing::test_product_price() for more details
     *
     * @access public
     * @return bool
     */
    public static function running_test()
    {
        $instance = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();
        return $instance->running_test;
    }

    /**
     * Enter product pricing test mode
     *
     * @access public
     * @return void
     */
    public static function start_test()
    {
        $instance = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();
        $instance->running_test = true;
    }

    /**
     * Exit product pricing test mode
     *
     * @access public
     * @return void
     */
    public static function end_test()
    {
        $instance = RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();
        $instance->running_test = false;
    }





}

RP_WCDPD_Controller_Methods_Product_Pricing::get_instance();

}
