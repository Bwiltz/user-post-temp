<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display countdown timer manually for specific product
 *
 * Countdown timer displayed manually is not updated dynamically on customer
 * interaction, e.g. variation or quantity change
 *
 * Does not accept variable products (specific variation id must be provided)
 */
if (!function_exists('rp_wcdpd_display_countdown_timer')) {

    function rp_wcdpd_display_countdown_timer($product_id)
    {
        // Load product
        if ($product = wc_get_product($product_id)) {

            // Variable product id passed in
            if ($product->is_type('variable')) {
                return;
            }

            // Maybe print countdown timer
            RP_WCDPD_Promotion_Countdown_Timer::maybe_print_countdown_timer($product);
        }
    }
}
