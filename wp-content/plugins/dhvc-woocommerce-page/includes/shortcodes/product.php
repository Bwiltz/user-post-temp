<?php

class DHVC_Woo_Page_Shortcode_Product {
	public function __construct(){
		add_action( 'vc_after_set_mode', array($this,'map_shortcodes'));
	}
	
	public function map_shortcodes(){
		vc_map ( array (
			"name" => __( "WC Single Product Images", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_images",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "dropdown",
					"heading" => __( "Slider type", DHVC_WOO_PAGE ),
					"param_name" => "slider_type",
					'admin_label'=>true,
					'save_always'=>true,
					'value'=>array(
						__('Deafult by theme')			=> 'default',
						__('Thumbnails vertical')		=> 'vertical',
						__('Thumbnails horizontal')		=> 'horizontal',
						__('Thumbnails overlay')		=> 'overlay',
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Enable Zoom', DHVC_WOO_PAGE ),
					'param_name' => 'enable_zoom',
					'value' => array( __( 'Yes', DHVC_WOO_PAGE ) => 'yes' ),
					'dependency' => array(
						'element' => 'slider_type',
						'value_not_equal_to' => 'default',
					),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Enable Lightbox', DHVC_WOO_PAGE ),
					'param_name' => 'enable_lightbox',
					'value' => array( __( 'Yes', DHVC_WOO_PAGE ) => 'yes' ),
					'dependency' => array(
						'element' => 'slider_type',
						'value_not_equal_to' => 'default',
					),
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
			"name" => __( "WC Single Product Title", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_title",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array(
					'save_always'=>true,
					'type' => 'checkbox',
					'heading' => __( 'Custom title style?', DHVC_WOO_PAGE ),
					'param_name' => 'custom_styles',
					'value' => array( __( 'Yes', DHVC_WOO_PAGE ) => 'yes' ),
				),
				array(
					'save_always'=>true,
					'type' => 'font_container',
					'param_name' => 'font_container',
					'value' => 'text_align:left',
					'settings' => array(
						'fields' => array(
							'text_align',
							'font_size',
							'line_height',
							'color',
							'text_align_description' => __( 'Select text alignment.', DHVC_WOO_PAGE ),
							'font_size_description' => __( 'Enter font size.', DHVC_WOO_PAGE ),
							'line_height_description' => __( 'Enter line height.', DHVC_WOO_PAGE ),
							'color_description' => __( 'Select heading color.', DHVC_WOO_PAGE),
						),
					),
					'dependency' => array(
						'element' => 'custom_styles',
						'value' => array( 'yes' ),
					),
				),
				array(
					'save_always'=>true,
					'type' => 'checkbox',
					'heading' => __( 'Custom font family?', DHVC_WOO_PAGE ),
					'param_name' => 'custom_fonts',
					'value' => array( __( 'Yes', DHVC_WOO_PAGE ) => 'yes' ),
					'dependency' => array(
						'element' => 'custom_styles',
						'value' => array( 'yes' ),
					),
				),
				array(
					'save_always'=>true,
					'type' => 'google_fonts',
					'param_name' => 'google_fonts',
					'settings' => array(
						'fields' => array(
							'font_family_description' => __( 'Select font family.', 'js_composer' ),
							'font_style_description' => __( 'Select font styling.', 'js_composer' ),
						),
					),
					'dependency' => array(
						'element' => 'custom_fonts',
						'value' => array( 'yes' ),
					),
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
			"name" => __( "WC Single Product Rating", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_rating",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
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
			"name" => __( "WC Single Product Price", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_price",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array(
					'save_always'=>true,
					'type' => 'checkbox',
					'heading' => __( 'Custom title style?', DHVC_WOO_PAGE ),
					'param_name' => 'custom_styles',
					'value' => array( __( 'Yes', DHVC_WOO_PAGE ) => 'yes' ),
				),
				array(
					'save_always'=>true,
					'type' => 'font_container',
					'param_name' => 'font_container',
					'value' => 'text_align:left',
					'settings' => array(
						'fields' => array(
							'text_align',
							'font_size',
							'line_height',
							'color',
							'text_align_description' => __( 'Select text alignment.', DHVC_WOO_PAGE ),
							'font_size_description' => __( 'Enter font size.', DHVC_WOO_PAGE ),
							'line_height_description' => __( 'Enter line height.', DHVC_WOO_PAGE ),
							'color_description' => __( 'Select heading color.', DHVC_WOO_PAGE),
						),
					),
					'dependency' => array(
						'element' => 'custom_styles',
						'value' => array( 'yes' ),
					),
				),
				array(
					'save_always'=>true,
					'type' => 'checkbox',
					'heading' => __( 'Custom font family?', DHVC_WOO_PAGE ),
					'param_name' => 'custom_fonts',
					'value' => array( __( 'Yes', DHVC_WOO_PAGE ) => 'yes' ),
					'dependency' => array(
						'element' => 'custom_styles',
						'value' => array( 'yes' ),
					),
				),
				array(
					'save_always'=>true,
					'type' => 'google_fonts',
					'param_name' => 'google_fonts',
					'settings' => array(
						'fields' => array(
							'font_family_description' => __( 'Select font family.', 'js_composer' ),
							'font_style_description' => __( 'Select font styling.', 'js_composer' ),
						),
					),
					'dependency' => array(
						'element' => 'custom_fonts',
						'value' => array( 'yes' ),
					),
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
			"name" => __( "WC Single Product SKU", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_sku",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Label", DHVC_WOO_PAGE ),
					"param_name" => "label",
					'value'=>'',
					"description" => __( "Enter SKU label.", DHVC_WOO_PAGE )
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
		$product_taxonomies = get_taxonomies( array(
			'public'		=> true,
			'object_type'	=> array('product'),
		),'objects');
		$taxonomy_options = array();
		
		foreach ( (array) $product_taxonomies as $taxonomy ) {
			$taxonomy_options[$taxonomy->label] = $taxonomy->name;
		}
		vc_map ( array (
			"name" => __( "WC Single Product Term", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_term",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			'description'=>__('Display product term list',DHVC_WOO_PAGE),
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Label", DHVC_WOO_PAGE ),
					"param_name" => "label",
					'value'=>'',
					"description" => __( "Enter Label.", DHVC_WOO_PAGE )
				),
				array (
					"type" => "dropdown",
					"heading" => __( "Taxonomy", DHVC_WOO_PAGE ),
					"param_name" => "taxonomy",
					'save_always'=>true,
					'admin_label'	=> true,
					'class' => 'dhwc-woo-product-page-dropdown',
					"value" => $taxonomy_options
				),
				array (
					"type" => "dropdown",
					"heading" => __( "Display", DHVC_WOO_PAGE ),
					"param_name" => "display",
					'save_always'=>true,
					'class' => 'dhwc-woo-product-page-dropdown',
					"value" => array(
						__('Name',DHVC_WOO_PAGE)				=>'name',
						__('Thumbnail',DHVC_WOO_PAGE) 			=> 'thumbnail',
						__('Name & Thumbnail',DHVC_WOO_PAGE) 	=> 'name_thumbnail'
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
			"name" => __( "WC Single Product Excerpt", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_excerpt",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			'description'=>__('Display product excerpt',DHVC_WOO_PAGE),
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
			"name" => __( "WC Single Product Description", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_description",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Extra class name", DHVC_WOO_PAGE ),
					"param_name" => "el_class",
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
			"name" => __( "WC Single Product Additional Information", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_additional_information",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Display attributes", DHVC_WOO_PAGE ),
					'admin_label'=>true,
					"param_name" => "attributes",
					'value'=>'',
					"description" => __( "Enter some attributes by ',' separating values, Ex: weight,dimensions,color,size... Empty to show all attributes.", DHVC_WOO_PAGE )
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
			"name" => __( "WC Single Product Add to Cart", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_add_to_cart",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
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
			"name" => __( "WC Single Product Meta", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_meta",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
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
			"name" => __( "WC Single Product Sharing", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_sharing",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
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
			"name" => __( "WC Single Product Data Tabs", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_data_tabs",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
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
			"name" => __( "WC Single Product Reviews", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_reviews",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
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
			"name" => __( "WC Single Product Upsell", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_upsell_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "posts_per_page",
					"value" => 4
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
					'save_always'=>true,
					'class' => 'dhwc-woo-product-page-dropdown',
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
			"name" => __( "WC Single Product Related", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_related_products",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Product Per Page", DHVC_WOO_PAGE ),
					"param_name" => "posts_per_page",
					"value" => 4
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
					'save_always'=>true,
					'class' => 'dhwc-woo-product-page-dropdown',
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
			"name" => __( "WC Single Product Custom Field", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_product_page_custom_field",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-product-page",
			'description' => __( 'Custom fields data from meta values of the product.', DHVC_WOO_PAGE ),
			"params" => array (
				array(
					'type' => 'textfield',
					'heading' => __( 'Field key name', DHVC_WOO_PAGE ),
					'param_name' => 'key',
					'save_always'=>true,
					'admin_label'=>true,
					'description' => __( 'Enter custom field name to retrieve meta data value.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Label', DHVC_WOO_PAGE ),
					'param_name' => 'label',
					'save_always'=>true,
					'admin_label'=>true,
					'description' => __( 'Enter label to display before key value.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', DHVC_WOO_PAGE ),
					'param_name' => 'el_class',
					'save_always'=>true,
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', DHVC_WOO_PAGE ),
					'param_name' => 'css',
					'group' => __( 'Design Options', DHVC_WOO_PAGE ),
				),
			)
		) );
		if(function_exists('fpd_get_option')){
			vc_map ( array (
				"name" => __( "WC Single FPD Designer", DHVC_WOO_PAGE ),
				"base" => "dhvc_woo_product_page_fpd",
				"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
				"icon" => "icon-dhvc-woo-product-page",
				"params" => array (
					array (
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
		if (class_exists ( 'acf' )) {
			$custom_fields = array ();
			$custom_fields[] = '';
			if(function_exists('acf_get_field_groups')){
				$field_groups = acf_get_field_groups();
			}else{
				$field_groups = apply_filters ( 'acf/get_field_groups', array () );
			}
		
			foreach ( $field_groups as $field_group ) {
				if (is_array ( $field_group )) {
					if(function_exists('acf_get_fields')){
						$fields = acf_get_fields($field_group);
						if (! empty ( $fields )) {
							foreach ( $fields as $field ) {
								$custom_fields [$field ['label']] = $field ['name'];
							}
						}
		
					}else{
						$fields = apply_filters ( 'acf/field_group/get_fields', array (), $field_group ['id'] );
						if (! empty ( $fields )) {
							foreach ( $fields as $field ) {
								$custom_fields [$field ['label']] = $field ['name'];
							}
						}
					}
				}
			}
			if (! empty ( $custom_fields )) {
				vc_map ( array (
					"name" => __( "WC ACF Custom Fields", DHVC_WOO_PAGE ),
					"base" => "dhvc_woo_product_page_acf_field",
					"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
					'description' => __( 'Advanced Custom Fields (ACF Plugin).', DHVC_WOO_PAGE ),
					"icon" => "icon-dhvc-woo-product-page",
					"params" => array (
						array (
							"type" => "dropdown",
							"heading" => __( "Field Name", DHVC_WOO_PAGE ),
							"param_name" => "field",
							"admin_label" => true,
							'save_always'=>true,
							"value" => $custom_fields
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
			}
		}
		
		if (defined ( 'YITH_WCWL' )) {
			vc_map ( array (
				"name" => __( "WC Single Product Wishlist", DHVC_WOO_PAGE ),
				"base" => "dhvc_woo_product_page_wishlist",
				"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
				"icon" => "icon-dhvc-woo-product-page",
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
		}
	}
}

new DHVC_Woo_Page_Shortcode_Product();

