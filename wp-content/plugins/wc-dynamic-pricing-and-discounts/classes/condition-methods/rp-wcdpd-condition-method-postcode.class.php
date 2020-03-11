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
 * Condition Method: Postcode
 *
 * @class RP_WCDPD_Condition_Method_Postcode
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Method_Postcode')) {

class RP_WCDPD_Condition_Method_Postcode extends RP_WCDPD_Condition_Method
{
    protected $key = 'postcode';

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
            'matches'           => __('matches', 'rp_wcdpd'),
            'does_not_match'    => __('does not match', 'rp_wcdpd'),
        );
    }

    /**
     * Check against condition method
     *
     * @access public
     * @param string $option_key
     * @param mixed $value
     * @param mixed $condition_value
     * @return bool
     */
    public function check($option_key, $value, $condition_value)
    {
        // Check if postcode matches condition
        $postcode_matches = RightPress_Conditions_Helper::check_postcode($value, $condition_value);

        // Matches
        if ($option_key === 'matches' && $postcode_matches) {
            return true;
        }
        // Does not match
        else if ($option_key === 'does_not_match' && !$postcode_matches) {
            return true;
        }

        return false;
    }




}

RP_WCDPD_Condition_Method_Postcode::get_instance();

}
