<?php

/**
 * Volume Pricing Table - Modal - Vertical
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<!-- Anchor -->
<div class="rp_wcdpd_product_page">
    <div class="rp_wcdpd_product_page_modal_link"><span><?php echo RP_WCDPD_Settings::get('promo_volume_pricing_table_title'); ?></span></div>
</div>

<!-- Modal -->
<div class="rp_wcdpd_modal" style="min-width: 200px;">
    <?php RightPress_Helper::include_extension_template('promotion-volume-pricing-table', 'vertical', RP_WCDPD_PLUGIN_PATH, RP_WCDPD_PLUGIN_KEY, array('data' => $data)); ?>
</div>
