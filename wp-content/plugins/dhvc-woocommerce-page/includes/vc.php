<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class DHVC_Woo_Page_VisualComposer{
	
	public function __construct(){
		add_action( 'vc_before_init', array($this,'init') );
		
		add_action('vc_load_default_params', array($this,'add_params'));
		
		add_action( 'vc_after_set_mode', array($this,'disableinline') );
		
		add_action( 'vc_after_init', array($this,'editor_init') );
		
		add_action( 'vc_after_set_mode', array($this,'map'), 20);
		
		add_action('vc_backend_editor_enqueue_js_css', array($this,'editor_chosen_css'));
		add_action('vc_frontend_editor_enqueue_js_css', array($this,'editor_chosen_css'));
	}
	
	public function editor_chosen_css(){
		wp_enqueue_style('dhvc-woo-page-chosen', DHVC_WOO_PAGE_URL.'/assets/css/chosen.min.css');
	}
	
	public function init(){
		require_once DHVC_WOO_PAGE_DIR.'/includes/vc-functions.php';
	}
	
	public function disableinline(){
		if(dhvc_woo_product_page_is_page_editable()){
			vc_frontend_editor()->disableInline();
		}
	}
	
	public function editor_init(){
		require_once DHVC_WOO_PAGE_DIR.'/includes/vc-backend-editor.php';
		$backend_editor = new DHVC_Woo_Page_Vc_Backend_Editor();
		$backend_editor->addHooksSettings();
		if(dhvc_woo_product_page_is_page_editable()){
			require_once DHVC_WOO_PAGE_DIR.'/includes/vc-frontend-editor.php';
			$dhvc_woo_page_vc_frontend_editor = new DHVC_Woo_Page_Vc_Frontend_Editor();
			$dhvc_woo_page_vc_frontend_editor->init();
		}
	}
	
	public function add_params(){
		$params_script = DHVC_WOO_PAGE_URL.'/assets/js/params.js';
		vc_add_shortcode_param ( 'dhvc_woo_product_page_field_categories', 'dhvc_woo_product_page_setting_field_categories',$params_script);
		vc_add_shortcode_param ( 'dhvc_woo_product_page_field_products_ajax', 'dhvc_woo_product_page_setting_field_products_ajax',$params_script);	
		vc_add_shortcode_param ( 'dhvc_woo_product_page_field_multiple_select', 'dhvc_woo_product_page_setting_field_multiple_select',$params_script);		
	}
	
	public function map(){
		
		// New shortcode
		vc_map ( array (
			"name" => __( "WC Breadcrumb", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_breadcrumb",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Cart", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_cart",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Checkout", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_checkout",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Order Tracking", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_order_tracking",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC My Account", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_my_account",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Product Category", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_product_category",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "dhvc_woo_product_page_field_categories",
					"class" => "",
					'save_always'=>true,
					"heading" => __( "Categories", DHVC_WOO_PAGE ),
					"param_name" => "category"
				),
				array (
					"type" => "textfield",
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					'save_always'=>true,
					"value" => 12
				),
				array (
					"type" => "textfield",
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					'save_always'=>true,
					"value" => 4
				),
				array (
					"type" => "dropdown",
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					'class' => 'dhwc-woo-product-page-dropdown',
					'save_always'=>true,
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					"heading" => __( "Query type", DHVC_WOO_PAGE ),
					"param_name" => "operator",
					'save_always'=>true,
					"value" => array (
						""=>"",
						__( 'IN', DHVC_WOO_PAGE ) => 'IN',
						__( 'AND', DHVC_WOO_PAGE ) => 'AND',
						__( 'NOT IN', DHVC_WOO_PAGE ) => 'NOT IN'
					)
				),
				array (
					"type" => "textfield",
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'save_always'=>true,
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Product Categories", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_product_categories",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "dhvc_woo_product_page_field_categories",
					"class" => "",
					'save_always'=>true,
					'select_field'=>'id',
					"heading" => __( "Categories", DHVC_WOO_PAGE ),
					"param_name" => "ids"
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Number", DHVC_WOO_PAGE ),
					"param_name" => "number"
				),
				array (
					"type" => "textfield",
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __( "Hide Empty", DHVC_WOO_PAGE ),
					"param_name" => "hide_empty",
					"value" => array (
						""=>"",
						__( 'Yes', DHVC_WOO_PAGE ) => '1',
						__( 'No', DHVC_WOO_PAGE ) => '0'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Parent", DHVC_WOO_PAGE ),
					"param_name" => "parent"
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Recent Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_recent_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					"class" => "",
					'save_always'=>true,
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC',
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "dhvc_woo_product_page_field_products_ajax",
					"heading" => __( "Select products", DHVC_WOO_PAGE ),
					"param_name" => "ids",
					'save_always'=>true,
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Sale Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_sale_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Best Selling Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_best_selling_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Top Rated Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_top_rated_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Featured Products", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_featured_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Shop Messages", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_shop_messages",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		
		vc_map ( array (
			"name" => __( "WC Product Attribute", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_product_attribute",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-shortcode",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "per_page",
					"value" => 12
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Columns", DHVC_WOO_PAGE ),
					"param_name" => "columns",
					"value" => 4
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Products Ordering", DHVC_WOO_PAGE ),
					"param_name" => "orderby",
					"value" => array (
						""=>"",
						__( 'Publish Date', DHVC_WOO_PAGE ) => 'date',
						__( 'Modified Date', DHVC_WOO_PAGE ) => 'modified',
						__( 'Random', DHVC_WOO_PAGE ) => 'rand',
						__( 'Alphabetic', DHVC_WOO_PAGE ) => 'title',
						__( 'Popularity', DHVC_WOO_PAGE ) => 'popularity',
						__( 'Rate', DHVC_WOO_PAGE ) => 'rating',
						__( 'Price', DHVC_WOO_PAGE ) => 'price'
					)
				),
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"class" => "",
					"heading" => __( "Ascending or Descending", DHVC_WOO_PAGE ),
					"param_name" => "order",
					"value" => array (
						""=>"",
						__( 'Ascending', DHVC_WOO_PAGE ) => 'ASC',
						__( 'Descending', DHVC_WOO_PAGE ) => 'DESC'
					)
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __( "Attribute", DHVC_WOO_PAGE ),
					"param_name" => "attribute"
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __( "Filter", DHVC_WOO_PAGE ),
					"param_name" => "filter"
				),
				array (
					'save_always'=>true,
					"type" => "textfield",
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
					'value'=>'',
					"description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", DHVC_WOO_PAGE )
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
	}
}

new DHVC_Woo_Page_VisualComposer;