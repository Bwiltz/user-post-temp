<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_BOGO')) {
    require_once('rp-wcdpd-method-product-pricing-quantity-bogo.class.php');
}

/**
 * Product Pricing Method: BOGO XX
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XX
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XX')) {

class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XX extends RP_WCDPD_Method_Product_Pricing_Quantity_BOGO
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get cart items with quantities to adjust
     *
     * @access public
     * @param array $rule
     * @param array $cart_items
     * @return array
     */
    public function get_cart_items_to_adjust($rule, $cart_items = null)
    {
        $adjust = array();

        // Sort cart items by price descending so that we use more expensive items to trigger a rule and leave the cheaper ones to adjust
        $cart_items_desc = RP_WCDPD_WC_Cart::sort_cart_items_by_price($cart_items, 'descending');

        // Group cart item quantities
        $quantity_groups = $this->group_quantities($cart_items_desc, $rule);

        // Track cart item quantities that can no longer be considered (i.e. were either used to trigger rule or adjustment was applied to them)
        $used_quantities = array();

        // Iterate over quantity groups
        foreach ($quantity_groups as $quantity_group_key => $quantity_group) {

            // Start infinite loop to take care of repetition, will break out of it by ourselves
            while (true) {

                // Get quantities to purchase
                if ($quantities_to_purchase = $this->reserve_quantities($quantity_group, $used_quantities, $rule['bogo_purchase_quantity'], true)) {

                    // Mark quantities used temporary until we check if there are any items to be adjusted based this
                    $temporary_used_quantities = $this->merge_cart_item_quantities($used_quantities, $quantities_to_purchase);

                    // Get quantities to receive at adjusted price
                    if ($quantities_to_receive = $this->reserve_quantities($quantity_group, $temporary_used_quantities, $rule['bogo_receive_quantity'])) {

                        // Mark quantities used
                        $used_quantities = $this->merge_cart_item_quantities($temporary_used_quantities, $quantities_to_receive);

                        // Add to main array
                        $adjust = $this->merge_cart_item_quantities($adjust, $quantities_to_receive);

                        // Maybe repeat this again
                        if ($this->repeat) {
                            continue;
                        }
                    }
                }

                // This loop can only be iterated explicitly, break out of it otherwise
                break;
            }
        }

        return $adjust;
    }


}
}
