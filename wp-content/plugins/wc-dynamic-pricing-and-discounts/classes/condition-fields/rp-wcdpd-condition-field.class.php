<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent condition field class
 *
 * @class RP_WCDPD_Condition_Field
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if (!class_exists('RP_WCDPD_Condition_Field')) {

abstract class RP_WCDPD_Condition_Field extends RP_WCDPD_Item
{
    protected $item_key                 = 'condition_field';
    protected $accepts_multiple         = false;
    protected $supports_hierarchy       = false;

    /**
     * Get field attributes
     *
     * @access public
     * @param string $context
     * @param string $alias
     * @return array
     */
    public function get_field_attributes($context, $alias = 'condition')
    {
        $attributes = array();

        // Define attribute keys and method names to retrieve values
        $attribute_keys = array(
            'id'                        => 'get_id',
            'name'                      => 'get_name',
            'class'                     => 'get_class',
            'options'                   => 'get_options',
            'placeholder'               => 'get_placeholder',
            'disabled'                  => 'get_disabled',
            'readonly'                  => 'get_readonly',
            'data-rp-wcdpd-validation'  => 'get_validation_rules'
        );

        // Iterate over attribute keys
        foreach ($attribute_keys as $attribute_key => $method) {

            // Get attribute value
            $value = $this->$method($context, $alias);

            // Add to main array if it is set
            if ($value !== null) {
                $attributes[$attribute_key] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Get id
     *
     * @access public
     * @param string $context
     * @param string $alias
     * @return string
     */
    public function get_id($context, $alias = 'condition')
    {
        return 'rp_wcdpd_' . $context . '_' . $alias . 's_{i}_' . $this->key . '_{j}';
    }

    /**
     * Get name
     *
     * @access public
     * @param string $context
     * @param string $alias
     * @return string
     */
    public function get_name($context, $alias = 'condition')
    {
        return 'rp_wcdpd_settings[' . $context . '][{i}][' . $alias . 's][{j}][' . $this->key . ']' . ($this->accepts_multiple ? '[]' : '');
    }

    /**
     * Get class
     *
     * @access public
     * @param string $context
     * @param string $alias
     * @return string
     */
    public function get_class($context, $alias = 'condition')
    {
        return 'rp_wcdpd_' . $context . '_' . $alias . '_' . $this->key . ' rp_wcdpd_child_element_field';
    }

    /**
     * Get options
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        return null;
    }

    /**
     * Get placeholder
     *
     * @access public
     * @return string
     */
    public function get_placeholder()
    {
        return null;
    }

    /**
     * Get disabled value
     *
     * @access public
     * @return string
     */
    public function get_disabled()
    {
        return null;
    }

    /**
     * Get readonly value
     *
     * @access public
     * @return string
     */
    public function get_readonly()
    {
        return null;
    }

    /**
     * Get validation rules
     *
     * @access public
     * @return string
     */
    public function get_validation_rules()
    {
        return 'required';
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
        return isset($posted[$this->key]) && !RightPress_Helper::is_empty($posted[$this->key]);
    }

    /**
     * Sanitize field value
     *
     * @access public
     * @param array $posted
     * @param object $condition
     * @param string $method_option_key
     * @return mixed
     */
    public function sanitize($posted, $condition, $method_option_key)
    {
        if (isset($posted[$this->key])) {
            return $posted[$this->key];
        }

        return null;
    }

    /**
     * Get child ids for fields that support hierarchy
     *
     * @access public
     * @param array $values
     * @return array
     */
    public function get_children($values)
    {
        $values_with_children = array();

        foreach ($values as $value) {
            $values_with_children[$value] = array($value);
        }

        return $values_with_children;
    }

    /**
     * Check if condition field supports hierarchy
     *
     * @access public
     * @return bool
     */
    public function supports_hierarchy()
    {
        return $this->supports_hierarchy;
    }



}
}
