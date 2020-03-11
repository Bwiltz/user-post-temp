<?php

/**
 * Volume Pricing Table - Inline - Vertical
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="rp_wcdpd_product_page">
    <?php RightPress_Helper::include_extension_template('promotion-volume-pricing-table', 'vertical', RP_WCDPD_PLUGIN_PATH, RP_WCDPD_PLUGIN_KEY, array('data' => $data)); ?>
</div>
