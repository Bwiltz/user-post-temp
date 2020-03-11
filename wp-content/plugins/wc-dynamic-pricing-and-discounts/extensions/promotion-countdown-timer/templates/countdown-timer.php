<?php

/**
 * Promotion - Countdown Timer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="rp_wcdpd_promotion_countdown_timer" data-seconds="<?php echo $seconds_remaining; ?>">

    <div class="rp_wcdpd_promotion_countdown_timer_label">
        <span>
            <?php echo $label; ?>
        </span>
    </div>

    <div class="rp_wcdpd_promotion_countdown_timer_value">

        <div class="rp_wcdpd_promotion_countdown_timer_days">
            <span class="rp_wcdpd_promotion_countdown_timer_days_value"><?php echo $days; ?></span>
            <span class="rp_wcdpd_promotion_countdown_timer_days_label"><?php _e('DAYS', 'rp_wcdpd') ?></span>
        </div>

        <div class="rp_wcdpd_promotion_countdown_timer_hours">
            <span class="rp_wcdpd_promotion_countdown_timer_hours_value"><?php echo $hours; ?></span>
            <span class="rp_wcdpd_promotion_countdown_timer_hours_label"><?php _e('HOURS', 'rp_wcdpd') ?></span>
        </div>

        <div class="rp_wcdpd_promotion_countdown_timer_minutes">
            <span class="rp_wcdpd_promotion_countdown_timer_minutes_value"><?php echo $minutes; ?></span>
            <span class="rp_wcdpd_promotion_countdown_timer_minutes_label"><?php _e('MINUTES', 'rp_wcdpd') ?></span>
        </div>

        <div class="rp_wcdpd_promotion_countdown_timer_seconds">
            <span class="rp_wcdpd_promotion_countdown_timer_seconds_value"><?php echo $seconds; ?></span>
            <span class="rp_wcdpd_promotion_countdown_timer_seconds_label"><?php _e('SECONDS', 'rp_wcdpd') ?></span>
        </div>

    </div>

</div>
