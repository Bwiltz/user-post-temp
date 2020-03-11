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
 * Cart Discount method controller
 *
 * @class RP_WCDPD_Controller_Methods_Cart_Discount
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Controller_Methods_Cart_Discount')) {

class RP_WCDPD_Controller_Methods_Cart_Discount extends RP_WCDPD_Controller_Methods
{
    protected $context = 'cart_discounts';

    protected $preparing_cart_discounts = false;

    public $applying_cart_discount = false;

    // Store prepared adjustments
    protected $applicable_adjustments = array();
    protected $adjustments_prepared = false;

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
        // Prepare cart discounts
        add_action('woocommerce_before_calculate_totals', array($this, 'prepare_cart_discounts'), 1);

        // Apply cart discounts
        add_action('woocommerce_after_calculate_totals', array($this, 'apply'));

        // Remove no longer applicable cart discounts
        add_action('woocommerce_before_calculate_totals', array($this, 'remove_cart_discounts'), 2);
        add_action('woocommerce_check_cart_items', array($this, 'remove_cart_discounts'), 99);

        // Invalidate no longer applicable cart discounts
        add_filter('woocommerce_coupon_is_valid', array($this, 'maybe_invalidate_cart_discount'), 10, 2);

        // Provide discount data
        add_filter('woocommerce_get_shop_coupon_data', array($this, 'get_fake_coupon_data'), 10, 2);

        // Cart item validation for per item percentage discounts
        add_filter('woocommerce_coupon_is_valid_for_product', array($this, 'discount_is_valid_for_cart_item'), 99, 4);

        // Maybe limit cart discount amount
        add_filter('woocommerce_coupon_get_discount_amount', array($this, 'maybe_limit_cart_discount_amount'), 9, 5);

        // Change fake coupon label
        add_filter('woocommerce_cart_totals_coupon_label', array($this, 'change_fake_coupon_label'), 99, 2);

        // Change fake coupon html
        add_filter('woocommerce_cart_totals_coupon_html', array($this, 'change_fake_coupon_html'), 99, 2);

        // Maybe prevent regular coupons from being applied
        add_filter('woocommerce_coupon_is_valid', array($this, 'maybe_invalidate_regular_coupon'), 10, 2);
        add_filter('woocommerce_coupon_error', array($this, 'invalidated_regular_coupon_error'), 99, 3);
    }

    /**
     * Prepare cart discounts
     *
     * @access public
     * @return void
     */
    public function prepare_cart_discounts()
    {
        // Already applying our own cart discount
        if ($this->applying_cart_discount) {
            return;
        }

        // Check if cart discounts can be applied
        if (!$this->cart_discounts_can_be_applied()) {
            return;
        }

        // Add preparing flag
        $this->preparing_cart_discounts = true;

        // Reset limit
        RP_WCDPD_Limit_Cart_Discounts::reset();

        // Get applicable adjustments
        $adjustments = $this->get_applicable_adjustments();

        // Filter adjustments by rule selection method and exclusivity settings
        $adjustments = RP_WCDPD_Rules::filter_by_exclusivity($this->context, $adjustments);

        // Maybe apply limit to adjustments
        // Note: This is initial limiting to try to hide zero discounts but this is not guaranteed to work in every situation (e.g. when cart discounts are calculated sequentially we can't calculate reference amounts properly)
        $adjustments = RP_WCDPD_Limit_Cart_Discounts::filter_adjustments($adjustments);

        // Store adjustments so that other methods can access them later during this request
        $this->applicable_adjustments = $adjustments;

        // Add flag
        $this->adjustments_prepared = true;

        // Remove preparing flag
        $this->preparing_cart_discounts = false;
    }

    /**
     * Check if cart discounts can be applied
     *
     * @access public
     * @return bool
     */
    public function cart_discounts_can_be_applied()
    {
        global $woocommerce;

        // Individual use coupon settings
        if (!RP_WCDPD_Settings::get('cart_discounts_apply_with_individual_use_coupons')) {

            // Iterate over coupons applied to cart
            if (is_array($woocommerce->cart->applied_coupons)) {
                foreach ($woocommerce->cart->applied_coupons as $coupon_code) {

                    // Skip cart discounts
                    if (RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($coupon_code)) {
                        continue;
                    }

                    // Load coupon
                    try {
                        $coupon = new WC_Coupon($coupon_code);
                    }
                    // Something went wrong
                    catch (Exception $e) {
                        return false;
                    }

                    // Check if coupon is individual use
                    if (!RightPress_Helper::wc_version_gte('3.0') && $coupon->individual_use === 'yes') {
                        return false;
                    }
                    else if (RightPress_Helper::wc_version_gte('3.0') && $coupon->get_individual_use()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Apply rules
     *
     * @access public
     * @param object $cart
     * @return void
     */
    public function apply($cart = null)
    {
        // Already applying our own cart discount
        if ($this->applying_cart_discount) {
            return;
        }

        // Apply now
        parent::apply($cart);
    }

    /**
     * Get fake coupon data
     *
     * @access public
     * @param mixed $coupon_data
     * @param string $coupon_code
     * @return mixed
     */
    public function get_fake_coupon_data($coupon_data, $coupon_code)
    {
        // Not our coupon
        if (!RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($coupon_code)) {
            return $coupon_data;
        }

        // Prepare cart discounts now
        if (!$this->adjustments_prepared) {
            $this->prepare_cart_discounts();
        }

        // Combined simple cart discount handling
        if ($coupon_code === 'rp_wcdpd_combined') {
            return RP_WCDPD_Cart_Discounts::get_coupon_data_array(array(
                'amount' => $this->get_combined_simple_rule_amount(),
            ));
        }

        // Check if current coupon code refers to any of applicable cart discounts
        if (isset($this->applicable_adjustments[$coupon_code])) {

            // Get coupon data
            if ($method = $this->get_method_from_rule($this->applicable_adjustments[$coupon_code]['rule'])) {
                $coupon_data = $method->get_coupon_data($this->applicable_adjustments[$coupon_code]);
            }
        }

        return $coupon_data;
    }

    /**
     * Cart item validation for per item percentage discounts
     *
     * @access public
     * @param bool $is_valid
     * @param object $product
     * @param object $coupon
     * @param array $cart_item
     * @return bool
     */
    public function discount_is_valid_for_cart_item($is_valid, $product, $coupon, $cart_item)
    {
        // Get coupon code
        $coupon_code = RightPress_WC_Legacy::coupon_get_code($coupon);

        // Not our coupon
        if (!RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($coupon_code)) {
            return $is_valid;
        }

        // Discount is not applicable at all
        if (!isset($this->applicable_adjustments[$coupon_code])) {
            return false;
        }

        // Get cart discount conditions
        $conditions = !empty($this->applicable_adjustments[$coupon_code]['rule']['conditions']) ? $this->applicable_adjustments[$coupon_code]['rule']['conditions'] : array();

        // Check if cart item matches product conditions
        return RP_WCDPD_Conditions::cart_item_matches_product_conditions($cart_item, $conditions);
    }

    /**
     * Maybe limit cart discount amount
     *
     * @access public
     * @param float $discount
     * @param float $discounting_amount
     * @param array $cart_item
     * @param bool $single
     * @param object $coupon
     * @return float
     */
    public function maybe_limit_cart_discount_amount($discount, $discounting_amount, $cart_item, $single, $coupon)
    {
        // Get coupon code
        $coupon_code = RightPress_WC_Legacy::coupon_get_code($coupon);

        // Not our coupon
        if (!RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($coupon_code)) {
            return $discount;
        }

        // Discount is not applicable at all
        if (!isset($this->applicable_adjustments[$coupon_code])) {
            return 0.0;
        }

        // Limit discount amount
        return RP_WCDPD_Limit_Cart_Discounts::get_limited_discount_amount_per_coupon_and_cart_item($discount, $coupon_code, $cart_item['key'], $this->applicable_adjustments[$coupon_code]['rule']);
    }

    /**
     * Remove cart discounts
     *
     * @access public
     * @return void
     */
    public function remove_cart_discounts()
    {
        // Already applying our own cart discount
        if ($this->applying_cart_discount) {
            return;
        }

        global $woocommerce;

        $flag_totals_for_refresh = false;

        // Remove coupons that are no longer applicable
        foreach ($woocommerce->cart->applied_coupons as $applied_coupon) {

            // Check if this is our fake coupon that is no longer applicable
            if (RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($applied_coupon)) {

                $remove = false;

                // Cart discount no longer applicable
                if (!isset($this->applicable_adjustments[$applied_coupon])) {
                    $remove = true;
                }
                // Simple cart discount was combined with other simple cart discounts
                else if ($this->get_method_key($this->applicable_adjustments[$applied_coupon]['rule']) === 'simple' && $this->combine_simple() && $applied_coupon !== 'rp_wcdpd_combined') {
                    $remove = true;
                }

                // Check if cart discount needs to be removed
                if ($remove) {

                    // Add filter to temporarily enable coupons just in case they are disabled
                    add_filter('woocommerce_coupons_enabled', array($this, 'woocommerce_enable_coupons'));

                    // Remove coupon
                    $woocommerce->cart->remove_coupon($applied_coupon);
                    $flag_totals_for_refresh = true;

                    // Remove filter
                    remove_filter('woocommerce_coupons_enabled', array($this, 'woocommerce_enable_coupons'));
                }
            }
        }

        // Flag totals for refresh
        if ($flag_totals_for_refresh) {
            WC()->session->set('refresh_totals', true);
        }
    }

    /**
     * Invalidate no longer applicable cart discounts
     *
     * @access public
     * @param bool $is_valid
     * @param object $coupon
     * @return bool
     */
    public function maybe_invalidate_cart_discount($is_valid, $coupon)
    {
        // Get coupon code
        $code = RightPress_WC_Legacy::coupon_get_code($coupon);

        // Check if current coupon is cart discount
        if (RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($code)) {

            // Combined cart discounts
            if ($code === 'rp_wcdpd_combined') {

                // Check if discounts are still combined and at least one discount is applicable
                if (!$this->combine_simple()) {
                    $is_valid = false;
                }
            }
            // Individual cart discount
            else {

                // Check if individual cart discount is still applicable
                if (!isset($this->applicable_adjustments[$code])) {
                    $is_valid = false;
                }
            }
        }

        return $is_valid;
    }

    /**
     * Temporary enable WooCommerce coupons to be able to remove fake coupon
     *
     * @access public
     * @return string
     */
    public function woocommerce_enable_coupons()
    {
        return 'yes';
    }

    /**
     * Change fake coupon label
     *
     * @access public
     * @param string $label
     * @param object $coupon
     * @return string
     */
    public function change_fake_coupon_label($label, $coupon)
    {
        // Get coupon code
        $code = RightPress_WC_Legacy::coupon_get_code($coupon);

        // Combined simple cart discount handling
        if ($code === 'rp_wcdpd_combined') {

            // Get combined cart discount label
            $label = $this->get_combined_simple_cart_discount_label();

            // Maybe add public description
            $label = $this->maybe_add_public_description($label, array_keys($this->applicable_adjustments));
        }
        // Regular cart discount handling
        else if (isset($this->applicable_adjustments[$code])) {

            // Reference adjustment
            $adjustment = $this->applicable_adjustments[$code];

            // Load method
            if ($method = $this->get_method_from_rule($adjustment['rule'])) {

                // Get coupon label
                $label = $method->get_coupon_label($adjustment);

                // Maybe add public description
                $label = $this->maybe_add_public_description($label, array($adjustment['rule']['uid']));
            }
        }

        return $label;
    }

    /**
     * Change fake coupon value html
     *
     * @access public
     * @param string $html
     * @param object $coupon
     * @return string
     */
    public function change_fake_coupon_html($html, $coupon)
    {
        $code = RightPress_WC_Legacy::coupon_get_code($coupon);

        if (isset($this->applicable_adjustments[$code]) || $code === 'rp_wcdpd_combined') {
            return RP_WCDPD_Cart_Discounts::get_cart_discount_coupon_html($coupon);
        }

        return $html;
    }

    /**
     * Check if coupon is cart discount
     *
     * @access public
     * @param string $coupon_code
     * @return bool
     */
    public static function coupon_is_cart_discount($coupon_code)
    {
        return RightPress_Helper::string_begins_with_substring($coupon_code, 'rp_wcdpd_');
    }

    /**
     * Check if cart contains at least one cart discount
     *
     * @access public
     * @return bool
     */
    public static function cart_contains_cart_discount()
    {
        global $woocommerce;

        if (isset($woocommerce->cart) && is_object($woocommerce->cart)) {

            // Get applied coupons
            $applied_coupons = $woocommerce->cart->get_applied_coupons();

            // Iterate over all applied coupons
            if (is_array($applied_coupons) && !empty($applied_coupons)) {
                foreach ($applied_coupons as $applied_coupon) {
                    if (RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($applied_coupon)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if applicable simple cart discounts need to be combined
     *
     * @access public
     * @return bool
     */
    public function combine_simple()
    {
        return RP_WCDPD_Settings::get('cart_discounts_if_multiple_applicable') === 'combined' && count($this->applicable_adjustments) > 1;
    }

    /**
     * Get combined simple rule
     *
     * @access public
     * @return array
     */
    public function get_combined_simple_rule()
    {
        return array(
            'uid'   => 'rp_wcdpd_combined',
            'title' => $this->get_combined_simple_cart_discount_label(),
        );
    }

    /**
     * Get combined simple cart discount label
     *
     * @access public
     * @return string
     */
    public function get_combined_simple_cart_discount_label()
    {
        $label = RP_WCDPD_Settings::get('cart_discounts_combined_title');
        return !RightPress_Helper::is_empty($label) ? $label : __('Discount', 'rp_wcdpd');
    }

    /**
     * Maybe prevent regular coupons from being applied
     *
     * @access public
     * @param bool $is_valid
     * @param object $coupon
     * @return bool
     */
    public function maybe_invalidate_regular_coupon($is_valid, $coupon)
    {
        // Get coupon code
        $coupon_code = RightPress_WC_Legacy::coupon_get_code($coupon);

        // Check if this is a regular coupon
        if (!RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($coupon_code)) {

            // Check if regular coupons are not allowed with cart discounts
            if (!RP_WCDPD_Settings::get('cart_discounts_allow_coupons')) {

                // Check if cart contains cart discounts
                if (RP_WCDPD_Controller_Methods_Cart_Discount::cart_contains_cart_discount()) {

                    // Do not allow regular coupon
                    // Note: using a random error code to make sure we don't match any other error codes accidentally
                    throw new Exception('9450455045');
                }
            }
        }

        return $is_valid;
    }

    /**
     * Invalidated regular coupon error message
     *
     * @access public
     * @param string $error_message
     * @param
     * @return string
     */
    public function invalidated_regular_coupon_error($error_message, $error_code, $coupon)
    {
        // Note: using a random error code to make sure we don't match any other error codes accidentally
        if ($error_code === '9450455045') {
            $error_message = sprintf(__('Sorry, coupon "%s" is not valid when other discounts are applied to the cart.', 'rp_wcdpd'), RightPress_WC_Legacy::coupon_get_code($coupon));
        }

        return $error_message;
    }




}

RP_WCDPD_Controller_Methods_Cart_Discount::get_instance();

}
