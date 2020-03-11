<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Order
 *
 * @class RP_WCDPD_WC_Order
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_WC_Order')) {

class RP_WCDPD_WC_Order
{
    protected $get_coupon_code_times_called = 0;

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
        // Override coupon code with cart discount title in order view
        add_filter('woocommerce_order_item_get_code', array($this, 'get_coupon_code'), 99);

        // Redirect to cart discount rule when admin clicks on a "coupon" link in order view
        if (!empty($_REQUEST['post_type']) && $_REQUEST['post_type'] === 'shop_coupon' && !empty($_REQUEST['s']) && RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($_REQUEST['s'])) {
            add_action('admin_init', array($this, 'redirect_coupon_request_to_cart_discount'));
        }
    }

    /**
     * Override coupon code with cart discount title in order view
     *
     * @access public
     * @param string $code
     * @return string
     */
    public function get_coupon_code($code)
    {
        // Check if coupon is our cart discount
        if (RP_WCDPD_Controller_Methods_Cart_Discount::coupon_is_cart_discount($code)) {

            // Do this only in admin order view
            if (is_admin() && did_action('woocommerce_admin_order_item_bulk_actions') && !did_action('woocommerce_admin_order_totals_after_discount')) {

                $position = RightPress_Helper::wc_version_gte('3.2') ? 1 : 2;

                if ($this->get_coupon_code_times_called == $position) {

                    // Get rules
                    $rules = RP_WCDPD_Rules::get('cart_discounts', array('uids' => array($code)), true);

                    // Rule was found
                    if (!empty($rules) && is_array($rules)) {

                        // Get rule title
                        $rule = array_pop($rules);
                        $rule_title = $rule['title'];
                    }
                    // Rule was not found
                    else {
                        $rule_title = __('Cart Discount (deleted)', 'rp_wcdpd');
                    }

                    // Starting from WooCommerce 3.2 fix coupon codes using Javascript
                    // Note: this may be improved by incorporating some filters in view html-order-items.php in WooCommerce core
                    if (RightPress_Helper::wc_version_gte('3.2')) {
                        $rule_link = '<a href="' . admin_url('admin.php?page=rp_wcdpd_settings&tab=cart_discounts&open_rule_uid=' . $code) . '" class="tips"><span>' . $rule_title . '</span></a>';
                        include RP_WCDPD_PLUGIN_PATH . 'views/order/coupon-fix-script.php';
                    }
                    // Pre WooCommerce 3.2
                    else {
                        $code = $rule_title;
                    }

                    // Reset times called
                    $this->get_coupon_code_times_called = 0;
                }
                else {
                    $this->get_coupon_code_times_called++;
                }
            }
        }

        return $code;
    }

    /**
     * Redirect to cart discount rule when admin clicks on a "coupon" link in order view
     *
     * @access public
     * @return void
     */
    public function redirect_coupon_request_to_cart_discount()
    {
        wp_redirect(admin_url('admin.php?page=rp_wcdpd_settings&tab=cart_discounts&open_rule_uid=' . $_REQUEST['s']));
        exit;
    }




}

RP_WCDPD_WC_Order::get_instance();

}
