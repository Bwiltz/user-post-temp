<?php
if ( class_exists( 'DHVC_Woo_Page_Setting', false ) ) {
	return new DHVC_Woo_Page_Setting();
}
class DHVC_Woo_Page_Setting extends WC_Settings_Page {
	
	public function __construct(){
		$this->id = 'dhvc_woo_page';
		$this->label = __('Page Templates',DHVC_WOO_PAGE);
		parent::__construct();
	}
	
	public function get_sections(){
		$sections = array(
			'' => __( 'Product templates', DHVC_WOO_PAGE ),
			'dhvc_woo_page'    => __( 'Page templates', DHVC_WOO_PAGE ),
		);
		
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}
	
	public function get_settings( $current_section = '' ) {
		
		if('dhvc_woo_page'===$current_section){

			$page_templates = get_posts(array(
				'post_type'=>'dhwc_page_template',
				'posts_per_page'=>-1
			));
			$page_template_options = array();
			$page_template_options[''] = __('Select page template&hellip;',DHVC_WOO_PAGE);
			foreach ((array)$page_templates as $page_template){
				$page_template_options[$page_template->ID] = $page_template->post_title;
			}
				
			$settings = array(
				array(
					'title' => __( 'Page Templates', DHVC_WOO_PAGE ),
					'type'  => 'title',
					'id'    => 'dhvc_woo_page_template',
				),
					
				array(
					'title'    => __( 'Account login page', DHVC_WOO_PAGE ),
					'id'       => 'dhvc_woo_page_account_login',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'type'     => 'select',
					'default'  => '',
					'desc'     => __( 'Create page template in menu: WooCommerce &rarr; Page Templates.', DHVC_WOO_PAGE ),
					'desc_tip' => true,
					'options'  => $page_template_options,
				),
				array(
					'title'    => __( 'Cart empty page', DHVC_WOO_PAGE ),
					'id'       => 'dhvc_woo_page_cart_empty',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'type'     => 'select',
					'default'  => '',
					'desc'     => __( 'Create page template in menu: WooCommerce &rarr; Page Templates.', DHVC_WOO_PAGE ),
					'desc_tip' => true,
					'options'  => $page_template_options,
				),
				array(
					'title'    => __( 'Thankyou page', DHVC_WOO_PAGE ),
					'id'       => 'dhvc_woo_page_checkout_thankyou',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'type'     => 'select',
					'default'  => '',
					'desc'     => __( 'Create page template in menu: WooCommerce &rarr; Page Templates.', DHVC_WOO_PAGE ),
					'desc_tip' => true,
					'options'  => $page_template_options,
				),
				array(
					'title'    => __( 'Checkout order receipt page', DHVC_WOO_PAGE ),
					'id'       => 'dhvc_woo_page_checkout_order_receipt',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'type'     => 'select',
					'default'  => '',
					'desc'     => __( 'Create page template in menu: WooCommerce &rarr; Page Templates.', DHVC_WOO_PAGE ),
					'desc_tip' => true,
					'options'  => $page_template_options,
				)
			);
			
			$product_taxonomies = dhvc_woo_page_template_allow_taxonomies();
			
			foreach ((array) $product_taxonomies as $tax_name=>$tax_label){
				$settings[] = array(
					'title'    => $tax_label,
					'id'       => 'dhvc_woo_page_'.$tax_name,
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'type'     => 'select',
					'default'  => '',
					'desc'     => __( 'Create page template in menu: WooCommerce &rarr; Page Templates.', DHVC_WOO_PAGE ),
					'desc_tip' => true,
					'options'  => $page_template_options,
				);
			}
				
			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'dhvc_woo_page_template',
			);
		}else{
			$custom_post_types = apply_filters('dhvc_woo_page_product_template_types', array(
				'dhwc_template' => __('Product Templates',DHVC_WOO_PAGE)
			));
			
			foreach ($custom_post_types as $type=>$label){
				$options[$type] = $label;
			}
			
			$custom_page_options = array();
			$custom_page_options[''] = __('Select default template&hellip;',DHVC_WOO_PAGE);
			
			$selected_post_type =  dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template');
			$selected_default_template =  dhvc_woo_page_get_option('dhvc_woo_page_template_default','');
			
			if(get_post_type($selected_default_template) !== $selected_post_type)
				update_option('dhvc_woo_page_template_default', '');
			
			$pages = get_posts(array(
				'post_type'=>$selected_post_type,
				'posts_per_page'=>-1
			));
			
			if(is_array($pages) && !empty($pages)){
				foreach ($pages as $p){
					$custom_page_options[$p->ID] = $p->post_title;
				}
			}
			
			$settings = array(
				array(
					'title'    => __( 'Single Product Template', DHVC_WOO_PAGE ),
					'type'     => 'title',
					'id' => 'dhvc_woo_page_product'
				),
				array(
					'title'    => __( 'Product Template Type', DHVC_WOO_PAGE ),
					'desc'     => __( 'This controls what is template post type for product.', DHVC_WOO_PAGE ),
					'id'       => 'dhvc_woo_page_template_type',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'dhwc_template',
					'type'     => 'select',
					'options'  => $options,
					'desc_tip' => true,
				),
				array(
					'title'    => __( 'Default custom template', DHVC_WOO_PAGE ),
					'desc'     => __( 'This controls what is custom template default.', DHVC_WOO_PAGE ),
					'id'       => 'dhvc_woo_page_template_default',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => '',
					'type'     => 'select',
					'options'  => $custom_page_options,
					'desc_tip' => true,
				),
				array(
					'type' => 'sectionend',
					'id' => 'dhvc_woo_page_product'
				)
			);
			$settings[] = array(
				'title' => __( 'Product Types', DHVC_WOO_PAGE ),
				'type'  => 'title',
				'id'    => 'dhvc_woo_page_product_type',
			);
			$product_types = dhvc_woo_page_get_product_types();
			foreach ((array) $product_types as $product_type=>$product_type_label){
				$settings[] = array(
					'title'    => sprintf(__( 'Template for: %s', DHVC_WOO_PAGE ),$product_type_label),
					'desc'     => __( 'This controls what is custom template for product type.', DHVC_WOO_PAGE ),
					'id'       => '_dhvc_woo_page_template_for_'.$product_type,
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => '',
					'type'     => 'select',
					'options'  => $custom_page_options,
					'desc_tip' => true,
				);
			}
				
			$settings[] = array(
				'type' => 'sectionend',
				'id' => 'dhvc_woo_page_product_type'
			);
		}
		
		return apply_filters( 'dhvc_woo_page_settings_' . $this->id, $settings, $current_section );
	}
	
	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;
		
		$settings = $this->get_settings( $current_section );
	
		WC_Admin_Settings::output_fields( $settings );
	}
	
	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section; 
		$settings = $this->get_settings($current_section);
	
		WC_Admin_Settings::save_fields( $settings );
	}
	
}
return new DHVC_Woo_Page_Setting();