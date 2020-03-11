<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent class for various configuration items - pricing rules, conditions, condition methods etc.
 *
 * @class RP_WCDPD_Item
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Item')) {

abstract class RP_WCDPD_Item
{
    protected $context          = null;
    protected $group_key        = null;
    protected $group_position   = 10;
    protected $position         = 10;

    /**
     * Set up item groups hook
     *
     * Hook name examples:
     * rp_wcdpd_product_pricing_method_groups
     * rp_wcdpd_condition_groups
     *
     * @access public
     * @return void
     */
    public function hook_group()
    {
        add_filter(RP_WCDPD::get_items_filter_prefix($this->context) . $this->item_key . '_groups', array($this, 'register_group'), $this->group_position);
    }

    /**
     * Set up items hook
     *
     * Hook name examples:
     * rp_wcdpd_product_pricing_simple_method_items
     * rp_wcdpd_cart_condition_items
     *
     * @access public
     * @return void
     */
    public function hook()
    {
        add_filter(RP_WCDPD::get_items_filter_prefix($this->context, $this->group_key) . $this->item_key . '_items', array($this, 'register'), $this->position);
    }

    /**
     * Register item group
     *
     * @access public
     * @param array $item_groups
     * @return array
     */
    public function register_group($item_groups)
    {
        // Add group
        if (!isset($item_groups[$this->group_key])) {
            $item_groups[$this->group_key] = array(
                'label' => $this->get_group_label(),
            );
        }

        return $item_groups;
    }

    /**
     * Register item
     *
     * @access public
     * @param array $items
     * @return array
     */
    public function register($items)
    {
        // Check if item by this key exists
        if (!isset($items[$this->key])) {

            // Add item
            $items[$this->key] = $this;
        }

        return $items;
    }

    /**
     * Get contexts
     *
     * @access public
     * @return array|null
     */
    public function get_contexts()
    {
        return isset($this->contexts) ? $this->contexts : null;
    }

    /**
     * Check if item is enabled
     *
     * @access public
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }



}
}
