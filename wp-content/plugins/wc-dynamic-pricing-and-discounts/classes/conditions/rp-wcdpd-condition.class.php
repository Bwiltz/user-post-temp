<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent condition class
 *
 * @class RP_WCDPD_Condition
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition')) {

abstract class RP_WCDPD_Condition extends RP_WCDPD_Item
{
    protected $item_key     = 'condition';
    protected $fields       = array();
    protected $main_field   = null;
    protected $is_cart      = false;
    protected $is_customer  = false;

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get main field key to compare value against
     *
     * @access public
     * @return string
     */
    public function get_main_field()
    {
        // Main field defined
        if (isset($this->main_field)) {
            return $this->main_field;
        }

        // At least one field defined
        if (is_array($this->fields)) {
            foreach (array('after', 'before') as $position) {
                if (!empty($this->fields[$position])) {
                    return $this->fields[$position][0];
                }
            }
        }

        return null;
    }

    /**
     * Check against condition
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public function check($params)
    {
        // Load condition method
        if ($method = RP_WCDPD_Controller_Condition_Methods::get_item($this->method)) {

            // Get values to compare
            $value = $this->get_value($params);
            $condition_value = $this->get_condition_value($params);

            // Compare values if value is set
            if ($value !== null) {
                return $method->check($params['condition']['method_option'], $value, $condition_value);
            }
        }

        return false;
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_value($params)
    {
        return null;
    }

    /**
     * Get condition value
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_condition_value($params)
    {
        // Load field
        if ($field_key = $this->get_main_field()) {
            if ($field = RP_WCDPD_Controller_Condition_Fields::get_item($field_key)) {
                if (isset($params['condition'][$field_key])) {

                    // Reference value
                    $value = $params['condition'][$field_key];

                    // Field supports hierarchy
                    if ($field->supports_hierarchy()) {
                        return $field->get_children($value);
                    }
                    // Field does not support hierarchy
                    else {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get combined key
     *
     * @access public
     * @return string
     */
    public function get_combined_key()
    {
        return $this->group_key . '__' . $this->key;
    }

    /**
     * Check if condition is cart condition
     *
     * @access public
     * @return bool
     */
    public function is_cart()
    {
        return $this->is_cart;
    }

    /**
     * Check if condition is customer condition
     *
     * @access public
     * @return bool
     */
    public function is_customer()
    {
        return $this->is_customer;
    }

    /**
     * Get condition fields
     *
     * @access public
     * @return array
     */
    public function get_fields()
    {
        return $this->fields;
    }

    /**
     * Get condition method
     *
     * @access public
     * @return string
     */
    public function get_method()
    {
        return $this->method;
    }

    /**
     * Get cart items
     *
     * @access public
     * @param array $params
     * @return array
     */
    public function get_cart_items($params)
    {
        global $woocommerce;

        // Get cart items
        $cart_items = isset($params['cart_items']) ? $params['cart_items'] : $woocommerce->cart->get_cart();

        // Filter out bundle cart items
        $cart_items = RP_WCDPD_Helper::filter_out_bundle_cart_items($cart_items);

        // Return remaining items
        return $cart_items;
    }

    /**
     * Get order ids by timeframe
     *
     * @access public
     * @param string $timeframe_key
     * @return array
     */
    public function get_order_ids_by_timeframe($timeframe_key)
    {
        $config = array();

        // Since specific date
        if ($timeframe_key !== 'all_time') {

            // Get timeframe field
            $timeframe_field = RP_WCDPD_Controller_Condition_Fields::get_item('timeframe_span');

            // Get date from timeframe
            $config['date'] = $timeframe_field->get_date_from_timeframe($timeframe_key);
        }

        // Return matching order ids
        return RightPress_Conditions_Helper::get_order_ids($config);
    }



}
}
