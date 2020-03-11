<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity')) {
    require_once('rp-wcdpd-method-product-pricing-quantity.class.php');
}

/**
 * Product Pricing Method: BOGO
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_BOGO')) {

abstract class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO extends RP_WCDPD_Method_Product_Pricing_Quantity
{
    protected $group_key        = 'bogo';
    protected $group_position   = 40;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->hook_group();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {
        return __('Buy / Get', 'rp_wcdpd');
    }

    /**
     * Apply adjustment to prices
     *
     * @access public
     * @param array $prices
     * @param array $adjustment
     * @param string $cart_item_key
     * @return array
     */
    public function apply_adjustment_to_prices($prices, $adjustment, $cart_item_key = null)
    {
        // Reference rule
        $rule = $adjustment['rule'];

        // Get receive quantity
        $receive_quantity = !empty($adjustment['receive_quantity']) ? (int) $adjustment['receive_quantity'] : 1;

        // Do not process identical prices multiple times
        $processed = array();

        // Iterate over prices
        foreach ($prices as $index => $price) {

            // Get correct processed price key
            $processed_key = (string) $price['adjusted'];

            // Price not yet processed
            if (!isset($processed[$processed_key])) {

                // Get adjusted amount
                $adjusted = RP_WCDPD_Pricing::adjust_amount($price['adjusted'], $rule['bogo_pricing_method'], $rule['bogo_pricing_value']);

                // Round adjusted amount to get predictable results
                $adjusted = RP_WCDPD_Pricing::round($adjusted);

                // Allow developers to override
                $adjusted = apply_filters('rp_wcdpd_product_pricing_adjusted_unit_price', $adjusted, $price['adjusted'], $adjustment, array('receive_quantity' => $receive_quantity));

                // Add to processed prices array
                $processed[$processed_key] = (float) $adjusted;
            }

            // Set adjusted price
            $this->set_adjusted_price($prices, $processed[$processed_key], $index, $adjustment, $cart_item_key);

            // Decrease quantity
            $receive_quantity--;

            // Nothing left to adjust
            if (!$receive_quantity) {
                break;
            }
        }

        return $prices;
    }


}
}
