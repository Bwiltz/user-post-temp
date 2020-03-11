<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Field_Select')) {
    require_once('rp-wcdpd-condition-field-select.class.php');
}

/**
 * Condition Field Group: Select - Timeframe
 *
 * @class RP_WCDPD_Condition_Field_Select_Timeframe
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field_Select_Timeframe')) {

class RP_WCDPD_Condition_Field_Select_Timeframe extends RP_WCDPD_Condition_Field_Select
{
    protected $is_grouped = true;

    protected $timeframes = null;
    protected $timeframes_for_display = null;

    /**
     * Get options
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        // Prepare timeframes for display
        if ($this->timeframes_for_display === null) {
            foreach ($this->get_timeframes() as $timeframe_group_key => $timeframe_group) {

                // Add timeframe group
                $this->timeframes_for_display[$timeframe_group_key] = array(
                    'label'     => $timeframe_group['label'],
                    'options'   => array(),
                );

                // Add timeframe
                foreach ($timeframe_group['children'] as $timeframe_key => $timeframe) {
                    $this->timeframes_for_display[$timeframe_group_key]['options'][$timeframe_key] = $timeframe['label'];
                }
            }
        }

        // Return timeframes with all_time prepended
        if ($this->key === 'timeframe_span') {
            return array_merge(array(
                'all_time' => array(
                    'label'     => __('All time', 'rp_wcdpd'),
                    'options'   => array(
                        'all_time' => __('all time', 'rp_wcdpd'),
                    ),
                ),
            ), $this->timeframes_for_display);
        }

        // Return timeframes
        return $this->timeframes_for_display;
    }

    /**
     * Validate field value
     *
     * @access public
     * @param array $posted
     * @param object $condition
     * @param string $method_option_key
     * @return bool
     */
    public function validate($posted, $condition, $method_option_key)
    {
        if (isset($posted[$this->key])) {
            foreach ($this->get_options() as $timeframe_group) {
                if (isset($timeframe_group['options'][$posted[$this->key]])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get timeframes
     *
     * @access public
     * @return array
     */
    public function get_timeframes()
    {
        if ($this->timeframes === null) {

            // Define timeframes
            $this->timeframes = array(

                // Current
                'current' => array(
                    'label'     => __('Current', 'rp_wcdpd'),
                    'children'  => array(
                        'current_day'   => array(
                            'label' => __('current day', 'rp_wcdpd'),
                            'value' => 'midnight',
                        ),
                        'current_week'   => array(
                            'label' => __('current week', 'rp_wcdpd'),
                            'value' => $this->get_current_week_value(),
                        ),
                        'current_month'   => array(
                            'label' => __('current month', 'rp_wcdpd'),
                            'value' => 'midnight first day of this month',
                        ),
                        'current_year'   => array(
                            'label' => __('current year', 'rp_wcdpd'),
                            'value' => 'midnight first day of january',
                        ),
                    ),
                ),

                // Days
                'days' => array(
                    'label'     => __('Days', 'rp_wcdpd'),
                    'children'  => array(),
                ),

                // Weeks
                'weeks' => array(
                    'label'     => __('Weeks', 'rp_wcdpd'),
                    'children'  => array(),
                ),

                // Months
                'months' => array(
                    'label'     => __('Months', 'rp_wcdpd'),
                    'children'  => array(),
                ),

                // Years
                'years' => array(
                    'label'     => __('Years', 'rp_wcdpd'),
                    'children'  => array(),
                ),
            );

            // Generate list of days
            for ($i = 1; $i <= 6; $i++) {
                $this->timeframes['days']['children'][$i . '_day'] = array(
                    'label' => $i . ' ' . _n('day', 'days', $i, 'rp_wcdpd'),
                    'value' => '-' . $i . ($i === 1 ? ' day' : ' days'),
                );
            }

            // Generate list of weeks
            for ($i = 1; $i <= 4; $i++) {
                $this->timeframes['weeks']['children'][$i . '_week'] = array(
                    'label' => $i . ' ' . _n('week', 'weeks', $i, 'rp_wcdpd'),
                    'value' => '-' . $i . ($i === 1 ? ' week' : ' weeks'),
                );
            }

            // Generate list of months
            for ($i = 1; $i <= 12; $i++) {
                $this->timeframes['months']['children'][$i . '_month'] = array(
                    'label' => $i . ' ' . _n('month', 'months', $i, 'rp_wcdpd'),
                    'value' => '-' . $i . ($i === 1 ? ' month' : ' months'),
                );
            }

            // Generate list of years
            for ($i = 2; $i <= 10; $i++) {
                $this->timeframes['years']['children'][$i . '_year'] = array(
                    'label' => $i . ' ' . _n('year', 'years', $i, 'rp_wcdpd'),
                    'value' => '-' . $i . ($i === 1 ? ' year' : ' years'),
                );
            }

            // Allow developers to override
            $this->timeframes = apply_filters('rp_wcdpd_timeframes', $this->timeframes);
        }

        return $this->timeframes;
    }

    /**
     * Get start of current week value
     *
     * @access public
     * @return string
     */
    public function get_current_week_value()
    {
        // Today is first day of week
        if ((int) RightPress_Helper::get_adjusted_datetime(null, 'w') === RightPress_Helper::get_start_of_week()) {
            return 'midnight';
        }
        else {
            return 'midnight last ' . RightPress_Helper::get_literal_start_of_week();
        }
    }

    /**
     * Get date from timeframe
     *
     * @access public
     * @param string $option_key
     * @return object
     */
    public function get_date_from_timeframe($option_key)
    {
        $timeframes = $this->get_timeframes();

        foreach ($this->get_timeframes() as $timeframe_group_key => $timeframe_group) {
            if (isset($timeframe_group['children'][$option_key])) {
                return RightPress_Helper::get_datetime_object($timeframe_group['children'][$option_key]['value'], false);
            }
        }

        return null;
    }





}
}
