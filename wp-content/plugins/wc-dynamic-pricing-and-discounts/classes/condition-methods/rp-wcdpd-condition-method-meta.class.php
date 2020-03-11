<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Condition_Method')) {
    require_once('rp-wcdpd-condition-method.class.php');
}

/**
 * Condition Method: Meta
 *
 * @class RP_WCDPD_Condition_Method_Meta
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Meta')) {

class RP_WCDPD_Condition_Method_Meta extends RP_WCDPD_Condition_Method
{
    protected $key = 'meta';

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
        $this->hook();
    }

    /**
     * Get method options
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        return array(
            'is_empty'          => __('is empty', 'rp_wcdpd'),
            'is_not_empty'      => __('is not empty', 'rp_wcdpd'),
            'contains'          => __('contains', 'rp_wcdpd'),
            'does_not_contain'  => __('does not contain', 'rp_wcdpd'),
            'begins_with'       => __('begins with', 'rp_wcdpd'),
            'ends_with'         => __('ends with', 'rp_wcdpd'),
            'equals'            => __('equals', 'rp_wcdpd'),
            'does_not_equal'    => __('does not equal', 'rp_wcdpd'),
            'less_than'         => __('less than', 'rp_wcdpd'),
            'less_or_equal_to'  => __('less or equal to', 'rp_wcdpd'),
            'more_than'         => __('more than', 'rp_wcdpd'),
            'more_or_equal'     => __('more or equal to', 'rp_wcdpd'),
            'is_checked'        => __('is checked', 'rp_wcdpd'),
            'is_not_checked'    => __('is not checked', 'rp_wcdpd'),
        );
    }

    /**
     * Check against condition method
     *
     * Note: This is designed to work with unique meta entries, not with lists of data
     * stored under the same key, e.g. if "contains" option is selected and multiple
     * meta entries are found, it will check each individual entry to check if it "contains"
     * specific text, as oposed to checking if the whole list "contains" an equal entry
     *
     * @access public
     * @param string $option_key
     * @param mixed $value
     * @param mixed $condition_value
     * @return bool
     */
    public function check($option_key, $value, $condition_value)
    {
        // Check if at least one meta entry was found
        if (!empty($value)) {

            // Iterate over meta entries - all entries must match for the result to be true
            foreach ($value as $single) {

                // Is Empty
                if ($option_key === 'is_empty') {
                    if (!RightPress_Helper::is_empty($single)) {
                        return false;
                    }
                }
                // Is Not Empty
                else if ($option_key === 'is_not_empty') {
                    if (RightPress_Helper::is_empty($single)) {
                        return false;
                    }
                }
                // Contains
                else if ($option_key === 'contains') {
                    if (!RightPress_Helper::contains($single, $condition_value)) {
                        return false;
                    }
                }
                // Does Not Contain
                else if ($option_key === 'does_not_contain') {
                    if (RightPress_Helper::contains($single, $condition_value)) {
                        return false;
                    }
                }
                // Begins with
                else if ($option_key === 'begins_with') {
                    if (!RightPress_Helper::begins_with($single, $condition_value)) {
                        return false;
                    }
                }
                // Ends with
                else if ($option_key === 'ends_with') {
                    if (!RightPress_Helper::ends_with($single, $condition_value)) {
                        return false;
                    }
                }
                // Equals
                else if ($option_key === 'equals') {
                    if (!RightPress_Helper::equals($single, $condition_value)) {
                        return false;
                    }
                }
                // Does Not Equal
                else if ($option_key === 'does_not_equal') {
                    if (RightPress_Helper::equals($single, $condition_value)) {
                        return false;
                    }
                }
                // Less Than
                else if ($option_key === 'less_than') {
                    if (!RightPress_Helper::less_than($single, $condition_value)) {
                        return false;
                    }
                }
                // Less Or Equal To
                else if ($option_key === 'less_or_equal_to') {
                    if (RightPress_Helper::more_than($single, $condition_value)) {
                        return false;
                    }
                }
                // More Than
                else if ($option_key === 'more_than') {
                    if (!RightPress_Helper::more_than($single, $condition_value)) {
                        return false;
                    }
                }
                // More Or Equal
                else if ($option_key === 'more_or_equal') {
                    if (RightPress_Helper::less_than($single, $condition_value)) {
                        return false;
                    }
                }
                // Is Checked
                else if ($option_key === 'is_checked') {
                    if (!RightPress_Helper::is_checked($single)) {
                        return false;
                    }
                }
                // Is Not Checked
                else if ($option_key === 'is_not_checked') {
                    if (RightPress_Helper::is_checked($single)) {
                        return false;
                    }
                }
            }

            // Condition is matched if we didn't return false from the block above
            return true;
        }

        // If we reached this point, proceed depending on condition method option
        return in_array($option_key, array('is_empty', 'does_not_contain', 'does_not_equal', 'is_not_checked'), true);
    }




}

RP_WCDPD_Condition_Method_Meta::get_instance();

}
