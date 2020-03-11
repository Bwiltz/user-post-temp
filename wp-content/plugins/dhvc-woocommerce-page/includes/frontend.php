<?php

class DHVC_Woo_Page_Frontend {
	public function __construct(){
		
		add_action( 'template_redirect', array( $this, 'set_single_product_template' ) );
		add_action( 'template_redirect', array( $this, 'register_assets' ),1);
		add_action(	'wp_enqueue_scripts', array($this, 'enqueue_assets' ) );
		//Replace content
		add_filter(	'wc_get_template_part', array( $this,'get_template_part' ),9999,3);
		
		if(apply_filters('dhvc_woocommerce_page_use_custom_single_product_template',false)){
			add_filter( 'template_include', array( $this, 'template_loader' ),999999 );
		}
		
		add_action('wp_head', array($this,'add_custom_css'),100);
	}
	
	public function add_custom_css(){
		global $dhvc_single_product_template;
		
		if(empty($dhvc_single_product_template)){
			return;
		}
		
		if($wpb_custom_css = get_post_meta( $dhvc_single_product_template->ID, '_wpb_post_custom_css', true )){
			echo '<style type="text/css">'.$wpb_custom_css.'</style>';
		}
			
		if($wpb_shortcodes_custom_css = get_post_meta( $dhvc_single_product_template->ID, '_wpb_shortcodes_custom_css', true )){
			echo '<style type="text/css">'.$wpb_shortcodes_custom_css.'</style>';
		}
	}
	
	public function set_single_product_template(){
		global $dhvc_single_product_template;
		
		if(!is_product()){
			return ;
		}
		
		$product_template_id = dhvc_woo_product_page_get_custom_template();
		$product_template_id = dhvc_woo_page_icl_object_id($product_template_id,dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template'));
		if(!empty($product_template_id)){
			$dhvc_single_product_template = get_post($product_template_id);
		}
		do_action('dhvc_woocommerce_page_register_single',$product_template_id);
		
	}
	
	public function template_loader($template){
		global $dhvc_single_product_template;
		if ( is_singular('product') && !empty($dhvc_single_product_template)) {
			$find = array();
			$file 	= 'single-product.php';
			$find[] = 'dhvc-woocommerce-page/'.$file;
			$template       = locate_template( $find );
			if ( ! $template || WC_TEMPLATE_DEBUG_MODE){
				$template = DHVC_WOO_PAGE_DIR . '/templates/' . $file;
			}
			return $template;
		}
		return $template;
	}
	
	public function get_template_part($template, $slug, $name){
		global $wp_query,$post, $dhvc_single_product_template;
		
		if(!$wp_query->is_main_query() && empty($dhvc_single_product_template)){
			foreach ( dhvc_woo_product_page_single_shortcodes() as $shortcode => $function ) {
				//remove old shortcode and add again
				if(shortcode_exists($shortcode)){
					remove_shortcode($shortcode);
				}
			}
			//Is Single Product Shortcode
			$this->set_single_product_template();
			$this->add_custom_css();
			
			do_action('dhvc_woocommerce_page_before_single_product_shortcode_content');
		}
		
		if(!empty( $dhvc_single_product_template ) && $slug === 'content' && $name === apply_filters('dhvc_woocommerce_page_single_product_temp_name', 'single-product')){
			
			do_action('dhvc_woocommerce_page_before_override');
				
			$file 	= 'content-single-product.php';
			$find[] = 'dhvc-woocommerce-page/' . $file;
	
			if(class_exists('Ultimate_VC_Addons')){
				$previous_wp_query = $GLOBALS['post'];
				$GLOBALS['post']  = $dhvc_single_product_template;
				$Ultimate_VC_Addons = new Ultimate_VC_Addons;
				$Ultimate_VC_Addons->aio_front_scripts();
				$post = $previous_wp_query;
			}
			
			$template       = locate_template( $find );
			
			if ( ! $template || WC_TEMPLATE_DEBUG_MODE ){
				$template = DHVC_WOO_PAGE_DIR . '/templates/' . $file;
			}
			return $template;
		}
		return $template;
	}
	
	public function register_assets(){
		wp_register_style('dhvc-woocommerce-page', DHVC_WOO_PAGE_URL.'/assets/css/style.css',array(),DHVC_WOO_PAGE_VERSION);
		wp_register_script('slick',DHVC_WOO_PAGE_URL.'/assets/js/slick/slick.min.js',array('jquery'),DHVC_WOO_PAGE_VERSION,true);
	}
	
	public function enqueue_assets(){
		wp_enqueue_style('js_composer_front');
		wp_enqueue_style('js_composer_custom_css');
		wp_enqueue_style('dhvc-woocommerce-page');
	}
}
new DHVC_Woo_Page_Frontend;