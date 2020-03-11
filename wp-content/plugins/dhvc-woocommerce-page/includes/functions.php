<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function dhvc_woo_product_page_is_jupiter_theme(){
	$result =  apply_filters('dhvc_woo_product_page_is_jupiter_theme',function_exists('mk_woocommerce_assets'));
	return $result;
}

function dhvc_woo_page_get_current_edit_page_id(){
	if( isset( $_GET['post'] ) ) {
		$post_id  = (int) $_GET['post'];
	}elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = (int) $_POST['post_ID'];
	}elseif (isset($_REQUEST['post_id'])){
		$post_id = (int) $_REQUEST['post_id'];
	}elseif (isset($_REQUEST['vc_post_id'])){
		$post_id = (int) $_REQUEST['vc_post_id'];
	}else{
		$post_id = 0;
	}
	return apply_filters('dhvc_woo_page_current_edit_page_id', $post_id);
}

function dhvc_woo_page_template_allow_taxonomies(){
	return apply_filters('dhvc_woo_page_template_allow_taxonomies', array(
		'product_tag' => __('Product Tag page', DHVC_WOO_PAGE),
		'product_cat' => __('Product Category page', DHVC_WOO_PAGE),
		'product_brand' => __('Product Brand page', DHVC_WOO_PAGE)
	));
}

function dhvc_woo_page_get_option($option, $default = false, $icl = false){
	$value = get_option($option, $default);
	return apply_filters("dhvc_woo_page_get_option_{$option}", $value);
}

function dhvc_woo_page_icl_object_id($id,$type){
	if(function_exists('icl_object_id')){
		$id = icl_object_id($id, $type, true); 
	}
	$id = apply_filters('dhvc_woo_page_icl_object_id', $id, $type);
	return intval($id);
}

function dhvc_woo_page_get_template_custom_css($template_id){
	$post_custom_css = get_metadata( 'post', $template_id, '_wpb_post_custom_css', true );
	$output = '';
	if ( ! empty( $post_custom_css ) ) {
		$post_custom_css = strip_tags( $post_custom_css );
		$output .= '<style type="text/css">';
		$output .= $post_custom_css;
		$output .= '</style>';
	}
	$shortcodes_custom_css = get_metadata( 'post', $template_id, '_wpb_shortcodes_custom_css', true );
	if ( ! empty( $shortcodes_custom_css ) ) {
		$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
		$output .= '<style type="text/css">';
		$output .= $shortcodes_custom_css;
		$output .= '</style>';
	}
	return $output;
}

function dhvc_woo_page_get_shortcode_custom_css_class($param_value, $prefix = ' ' ){
	$css_class = preg_match( '/\s*\.([^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', $param_value ) ? $prefix . preg_replace( '/\s*\.([^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', '$1', $param_value ) : '';
	return $css_class;
}

function dhvc_woo_product_page_get_preview_editor_url( $template_id = '',$url = '', $id = '' ) {
	if(!defined( 'WPB_VC_VERSION' ) )
		return '';
	$the_ID = ( strlen( $id ) > 0 ? $id : get_the_ID() );
	return apply_filters( 'dhvc_woo_product_page_get_preview_editor_url', admin_url() .
		'edit.php?dhvc_woo_product_page_editor=frontend&post_id=' .
		$the_ID . '&post_type=' . get_post_type( $the_ID ) .
		(strlen($template_id) > 0 ? '&template_id='.$template_id:'').
		( strlen( $url ) > 0 ? '&url=' . rawurlencode( $url ) : '' ) );
}


function dhvc_woo_product_page_wc_shortcodes(){
	return array(
			'dhvc_woo_product_page_product_category'           			=> 'product_category',
			'dhvc_woo_product_page_product_categories'        			=> 'product_categories',
			'dhvc_woo_product_page_products'                   			=> 'products',
			'dhvc_woo_product_page_recent_products'            			=> 'recent_products',
			'dhvc_woo_product_page_sale_products'              			=> 'sale_products',
			'dhvc_woo_product_page_best_selling_products'      			=> 'best_selling_products',
			'dhvc_woo_product_page_top_rated_products'         			=> 'top_rated_products',
			'dhvc_woo_product_page_featured_products'          			=> 'featured_products',
			'dhvc_woo_product_page_product_attribute'          			=> 'product_attribute',
			'dhvc_woo_product_page_shop_messages'              			=> 'shop_messages',
			'dhvc_woo_product_page_order_tracking' 						=> 'order_tracking',
			'dhvc_woo_product_page_cart'           						=> 'cart',
			'dhvc_woo_product_page_checkout'      						=> 'checkout',
			'dhvc_woo_product_page_my_account'     						=> 'my_account',
			'dhvc_woo_product_page_breadcrumb'							=> 'breadcrumb'
	);
}

function dhvc_woo_product_page_single_shortcodes(){
	$shortcodes = array(
		'dhvc_woo_product_page_images'								=>'dhvc_woo_product_page_images_shortcode',
		'dhvc_woo_product_page_title'								=>'dhvc_woo_product_page_title_shortcode',
		'dhvc_woo_product_page_rating'								=>'dhvc_woo_product_page_rating_shortcode',
		'dhvc_woo_product_page_price'								=>'dhvc_woo_product_page_price_shortcode',
		'dhvc_woo_product_page_sku'									=>'dhvc_woo_product_page_sku_shortcode',
		'dhvc_woo_product_page_term'								=>'dhvc_woo_product_page_term_shortcode',
		'dhvc_woo_product_page_excerpt'								=>'dhvc_woo_product_page_excerpt_shortcode',
		'dhvc_woo_product_page_description'							=>'dhvc_woo_product_page_description_shortcode',
		'dhvc_woo_product_page_additional_information'				=>'dhvc_woo_product_page_additional_information',
		'dhvc_woo_product_page_add_to_cart'							=>'dhvc_woo_product_page_add_to_cart_shortcode',
		'dhvc_woo_product_page_meta'								=>'dhvc_woo_product_page_meta_shortcode',
		'dhvc_woo_product_page_sharing'								=>'dhvc_woo_product_page_sharing_shortcode',
		'dhvc_woo_product_page_data_tabs'							=>'dhvc_woo_product_page_data_tabs_shortcode',
		'dhvc_woo_product_page_reviews'								=>'dhvc_woo_product_page_reviews_shortcode',
		'dhvc_woo_product_page_upsell_products'						=>'dhvc_woo_product_page_upsell_products_shortcode',
		'dhvc_woo_product_page_related_products'					=>'dhvc_woo_product_page_related_products_shortcode',
		'dhvc_woo_product_page_wishlist'							=>'dhvc_woo_product_page_wishlist_shortcode',
		'dhvc_woo_product_page_custom_field'						=>'dhvc_woo_product_page_custom_field_shortcode',
	);
	
	if (class_exists ( 'acf' ))
		$shortcodes['dhvc_woo_product_page_acf_field'] = 'dhvc_woo_product_page_acf_field_shortcode'; 
	
	if(function_exists('fpd_get_option'))
		$shortcodes['dhvc_woo_product_page_fpd'] = 'dhvc_woo_product_page_fpd';
	
	
	return $shortcodes;
}

function dhvc_woo_page_get_product_types(){
	$product_types = wc_get_product_types();
	$product_types['downloadable'] = __( 'Downloadable product', DHVC_WOO_PAGE );
	$product_types['virtual'] = __( 'Virtual product', DHVC_WOO_PAGE );
	return $product_types;
}

function _dhvc_woo_page_is_template_for_post_type($template_id){
	$product_types = dhvc_woo_page_get_product_types();
	foreach ($product_types as $product_type=>$label){
		if((int)$template_id === (int)dhvc_woo_page_get_option('_dhvc_woo_page_template_for_'.$product_type)){
			return $product_type;
		}
	}
	return false;
}

function dhvc_woo_product_page_find_product_by_template($template_id){
	global $wpdb;
	$product_id = 0;
	$args = array(
		'posts_per_page'      => 1,
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'meta_query' => array(
			array(
				'key' => 'dhvc_woo_page_product',
				'value' => $template_id
			)
		)
	);
	
	$products = get_posts($args);

	if(!empty($products)){
		$product_id = current($products)->ID;
	}elseif ($template_of_post_type = _dhvc_woo_page_is_template_for_post_type($template_id)){
		$product_visibility_term_ids = wc_get_product_visibility_term_ids();
		if ( $product_visibility_term_ids['exclude-from-catalog'] ) {
			$exclude_term_ids[] = $product_visibility_term_ids['exclude-from-catalog'];
		}
		
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && $product_visibility_term_ids['outofstock'] ) {
			$exclude_term_ids[] = $product_visibility_term_ids['outofstock'];
		}
		//
		$query = array(
			'fields' => "
				SELECT DISTINCT ID FROM {$wpdb->posts} AS p
			",
			'join'   => '',
			'where'  => "
				WHERE 1=1
				AND p.post_status = 'publish'
				AND p.post_type = 'product'

			",
			'limits' => '
				LIMIT 1
			',
		);
		if ( count( $exclude_term_ids ) ) {
			$query['join']  .= " LEFT JOIN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ( " . implode( ',', array_map( 'absint', $exclude_term_ids ) ) . ' ) ) AS exclude_join ON exclude_join.object_id = p.ID';
			$query['where'] .= ' AND exclude_join.object_id IS NULL';
		}
		if(in_array($template_of_post_type, array('virtual','downloadable'))){
			$query['join'] .=  " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON p.ID = wc_product_meta_lookup.product_id ";
			$query['where'] .=  " AND wc_product_meta_lookup.{$template_of_post_type}=1 ";
		}else{
			$product_type_term = get_term_by('slug', $template_of_post_type,'product_type');
			if(!empty($product_type_term))
				$query['join'] .= " INNER JOIN ( SELECT object_id FROM {$wpdb->term_relationships} INNER JOIN {$wpdb->term_taxonomy} using( term_taxonomy_id ) WHERE term_id IN ( " . $product_type_term->term_id . ' ) ) AS include_join ON include_join.object_id = p.ID';
		}
		$product_in_type = $wpdb->get_col( implode( ' ', $query ) );
		
		if(!empty($product_in_type))
			$product_id = current($product_in_type);
	}else{
		$term_args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'meta_query' => array(
				array(
					'key'       => 'dhvc_woo_page_cat_product',
					'value'     => $template_id
				)
			)
		);
		$terms = get_terms($term_args);
		if(!empty($terms)){
			$term = $terms[0];
			$args = array(
				'posts_per_page'      => 1,
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => $term->term_id
					)
				)
			);
			$products = get_posts($args);
			if(!empty($products))
				$product_id = current($products)->ID;
		}
	}
	$product_id = apply_filters('dhvc_woo_product_page_find_product_by_template', $product_id, $template_id);
	return (int) $product_id;
}

function dhvc_woo_product_page_get_custom_template( $post = null ,$need_term=false ){
	if(empty($post))
		$post = get_post();
	
	$product = wc_get_product($post);
	
	$default_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_template_default','');
	
	$product_template_id = apply_filters('dhvc_woocommerce_page_default_template_id', $default_template_id);
	
	$product_term = '';
	
	$is_custom_by_type = false;
	
	//single template
	if($single_product_template_id = (int) get_post_meta($post->ID,'dhvc_woo_page_product',true)){
		$product_template_id = $single_product_template_id;
	}elseif (!empty($product)){
		$product_type = $product->get_type();
		$product_type_template_id = 0;
		
		if('simple'===$product_type){
			//get template for sub product type
			if($product->is_downloadable()){
				$is_custom_by_type = __('Downloadable product',DHVC_WOO_PAGE);
				$product_type_template_id = (int) dhvc_woo_page_get_option('_dhvc_woo_page_template_for_downloadable','');
			}elseif ($product->is_virtual()){
				$is_custom_by_type = __('Virtual product',DHVC_WOO_PAGE);
				$product_type_template_id = (int) dhvc_woo_page_get_option('_dhvc_woo_page_template_for_virtual','');
			}
			//use product type template if sub type is null
			if(empty($product_type_template_id)){
				$product_type_template_id = (int) dhvc_woo_page_get_option('_dhvc_woo_page_template_for_'.$product_type,'');
			}
		}else{
			$product_type_template_id = (int) dhvc_woo_page_get_option('_dhvc_woo_page_template_for_'.$product_type,'');
		}
		
		if(!empty($product_type_template_id)){
			if(false===$is_custom_by_type)
				$is_custom_by_type = $product_type.' '.__('product',DHVC_WOO_PAGE);
			$product_template_id = $product_type_template_id;
		}
	}else{
		//template by cat
		$terms = wp_get_post_terms( $post->ID, 'product_cat' );
		foreach ( $terms as $term ):
			if($cat_product_template_id = (int) get_term_meta($term->term_id,'dhvc_woo_page_cat_product',true)){
				$product_term = $term->name;
				$product_template_id = $cat_product_template_id;
				break;
			}
		endforeach;
	}
	
	$product_template_id = apply_filters('dhvc_woocommerce_page_template_id', $product_template_id);
	$post_type =  dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template');
	
	if( $post_type !== get_post_type($product_template_id) ){
		$product_template_id = $default_template_id;
		$product_term=0;
	}
	
	if($need_term)
		return array($product_template_id,$product_term,$is_custom_by_type);
	
	return $product_template_id;
}

function the_product_page_content(){
	global $dhvc_single_product_template;
	$content = $dhvc_single_product_template->post_content;
	if(!class_exists('easyTestimonials')){
		if (function_exists('CS')  && false !== strpos( $content, '[cs_content]' ) && false !== strpos( $content, '[/cs_content]' )){
			$content = CS()->component('Element_Front_End')->shortcode_output( array(
				'_p' => $dhvc_single_product_template->ID
			), $content, 'cs_content' );
		}else{
			$content = apply_filters( 'the_content', $content );
		}
	}else{
		add_filter( 'dhvc_woo_product_page_the_content', 'do_blocks', 9 );
		add_filter( 'dhvc_woo_product_page_the_content', 'wptexturize' );
		add_filter( 'dhvc_woo_product_page_the_content', 'convert_smilies', 20 );
		add_filter( 'dhvc_woo_product_page_the_content', 'shortcode_unautop' );
		add_filter( 'dhvc_woo_product_page_the_content', 'prepend_attachment' );
		add_filter( 'dhvc_woo_product_page_the_content', 'wp_make_content_images_responsive' );
		add_filter( 'dhvc_woo_page_template_the_content', 'do_shortcode', 11 ); 
		// Format WordPress
		add_filter( 'dhvc_woo_page_template_the_content', 'capital_P_dangit', 11 );
	}
	$content = apply_filters('dhvc_woo_product_page_the_content',$content);
	$content = str_replace( ']]>', ']]&gt;', $content );
	
	if(class_exists('WC_Structured_Data'))
		WC()->structured_data->generate_product_data();
	
	echo $content;
}

function dhvc_woo_page_has_shortcode($shortcode_tag,$post = false){
	if(false === $post)
		$post = $GLOBALS['post'];
	
	$has = false;
	if(strpos( $post->post_content, $shortcode_tag ) !== false )
		$has = true;
	return apply_filters('dhvc_woo_page_has_shortcode', $has, $shortcode_tag);
}

function dhvc_woo_page_template_the_content($content){
	add_filter( 'dhvc_woo_page_template_the_content', 'do_blocks', 9 );
	add_filter( 'dhvc_woo_page_template_the_content', 'wptexturize' );
	add_filter( 'dhvc_woo_page_template_the_content', 'convert_smilies', 20 );
	add_filter( 'dhvc_woo_page_template_the_content', 'shortcode_unautop' );
	add_filter( 'dhvc_woo_page_template_the_content', 'prepend_attachment' );
	add_filter( 'dhvc_woo_page_template_the_content', 'wp_make_content_images_responsive' );
	add_filter( 'dhvc_woo_page_template_the_content', 'do_shortcode',11); 
	// Format WordPress
	add_filter( 'dhvc_woo_page_template_the_content', 'capital_P_dangit', 11 );
	
	$content = apply_filters('dhvc_woo_page_template_the_content', $content);
	$content = str_replace( ']]>', ']]&gt;', $content );
	
	echo $content;
}

function dhvc_woo_page_template_archive_product_content(){
	global $dhvc_woo_page_template_archive;
	echo '<div class="dhvc-woocommerce-page-archive">';
	dhvc_woo_page_template_the_content($dhvc_woo_page_template_archive->post_content);
	echo '</div>';
}

function dhvc_woo_product_page_dropdown_custom($args='', $post_type=false){
	if(empty($post_type))
		$post_type = dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template');
	
	if(post_type_exists($post_type)){
		$defaults = array(
			'depth' => 0,
			'child_of' => 0,
			'selected' => 0,
			'echo' => 1,
			'name' => 'page_id',
			'id' => '',
			'class' => '',
			'show_option_none' => '',
			'show_option_no_change' => '',
			'option_none_value' => '',
			'post_type'=>$post_type,
			'suppress_filters' => false,
			'posts_per_page'=>-1
		);
	
		$r = wp_parse_args( $args, $defaults );
		
		$get_args = $r;
		
		if(isset($get_args['name']))
			unset($get_args['name']);
		
		$pages = get_posts( $get_args );
		$output = '';
		// Back-compat with old system where both id and name were based on $name argument
		if ( empty( $r['id'] ) ) {
			$r['id'] = $r['name'];
		}
	
		if ( ! empty( $pages ) ) {
			$class = '';
			if ( ! empty( $r['class'] ) ) {
				$class = " class='" . esc_attr( $r['class'] ) . "'";
			}
	
			$output = "<select name='" . esc_attr( $r['name'] ) . "'" . $class . " id='" . esc_attr( $r['id'] ) . "'>\n";
			if ( $r['show_option_no_change'] ) {
				$output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
			}
			if ( $r['show_option_none'] ) {
				$output .= "\t<option value=\"" . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . "</option>\n";
			}
			$output .= walk_page_dropdown_tree( $pages, $r['depth'], $r );
			$output .= "</select>\n";
		}
	
		$html = apply_filters( 'dhvc_woo_dropdown_custom', $output, $r, $pages );
	
		if ( $r['echo'] )
			echo $html;
		return $html;
	}
}



function dhvc_woo_product_page_setting_field_categories($settings, $value){
	$category_slugs = explode(',',$value);
	$args = array(
			'orderby' => 'name',
			'hide_empty' => 0,
	);
	
	$categories = get_terms( 'product_cat', $args );
	$output = '<select id= "'.$settings['param_name'].'" multiple="multiple" class="dhvc-woo-product-page-select chosen_select_nostd '.$settings['param_name'].' '.$settings['type'].'">';
	if( ! empty($categories)){
		foreach ($categories as $cat):
			$s = isset( $settings['select_field'] ) ? $cat->term_id : $cat->slug;
			$output .= '<option value="' . esc_attr( $s ) . '"' . selected( in_array( $s, $category_slugs ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
		endforeach;
	}
	$output .= '</select>';
	$output .='<input id= "'.$settings['param_name'].'" type="hidden" class="wpb_vc_param_value wpb-textinput" name="'.$settings['param_name'].'" value="'.$value.'" />';
	return $output;
}

function dhvc_woo_product_page_setting_field_multiple_select($settings, $value){
	$value_arr = explode(',', $value);
	$output = '<select id= "'.$settings['param_name'].'" multiple="multiple" class="dhvc-woo-product-page-select chosen_select_nostd '.$settings['param_name'].' '.$settings['type'].'">';
	if ( ! empty( $settings['value'] ) ) {
		foreach ($settings['value'] as $option_label => $option_value):
			$output .= '<option value="' . esc_attr( $option_value ) . '"' . selected( in_array( $option_value, $value_arr ), true, false ) . '>' . esc_html( $option_label ) . '</option>';
		endforeach;
	}
	$output .= '</select>';
	$output .='<input id= "'.$settings['param_name'].'" type="hidden" class="wpb_vc_param_value wpb-textinput" name="'.$settings['param_name'].'" value="'.$value.'" />';
	return $output;
}

function dhvc_woo_product_page_setting_field_products_ajax($settings, $value){
	$product_ids = array();

	if(!empty($value))
		$product_ids = array_map( 'absint', explode( ',', $value ) );

	$output = '<select id= "'.$settings['param_name'].'" multiple="multiple" class="dhvc-woo-product-page-select dhvc-woo-product-page-ajax-products '.$settings['param_name'].' '.$settings['type'].'">';
	if(!empty($product_ids)){
		foreach ( $product_ids as $product_id ) {
			$product = get_product( $product_id );
			$output .= '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . dhvc_woo_product_page_formatted_name($product) . '</option>';

		}
	}
	$output .= '</select>';
	$output .='<input id= "'.$settings['param_name'].'" type="hidden" class="wpb_vc_param_value wpb-textinput" name="'.$settings['param_name'].'" value="'.$value.'" />';
	return $output;
}

function dhvc_woo_product_page_formatted_name(WC_Product $product){
	if ( $product->get_sku() ) {
		$identifier = $product->get_sku() ;
	} else {
		$identifier = '#' . $product->get_id();
	}

	return sprintf( __( '%s &ndash; %s', DHVC_WOO_PAGE ), $identifier, $product->get_title() );
}

function dhvc_woo_product_page_search_products (){
	header( 'Content-Type: application/json; charset=utf-8' );
	
	$term = (string) sanitize_text_field( stripslashes( $_GET['term'] ) );


	if (empty($term)) die();

	$post_types = array('product', 'product_variation');

	if ( is_numeric( $term ) ) {

		$args = array(
				'post_type'			=> $post_types ,
				'post_status'	 	=> 'publish',
				'posts_per_page' 	=> -1,
				'post__in' 			=> array(0, $term),
				'fields'			=> 'ids'
		);

		$args2 = array(
				'post_type'			=> $post_types,
				'post_status'	 	=> 'publish',
				'posts_per_page' 	=> -1,
				'post_parent' 		=> $term,
				'fields'			=> 'ids'
		);

		$args3 = array(
				'post_type'			=> $post_types,
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_query' 		=> array(
						array(
								'key' 	=> '_sku',
								'value' => $term,
								'compare' => 'LIKE'
						)
				),
				'fields'			=> 'ids'
		);

		$posts = array_unique(array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ));

	} else {

		$args = array(
				'post_type'			=> $post_types,
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				's' 				=> $term,
				'fields'			=> 'ids'
		);

		$args2 = array(
				'post_type'			=> $post_types,
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				'meta_query' 		=> array(
						array(
								'key' 	=> '_sku',
								'value' => $term,
								'compare' => 'LIKE'
						)
				),
				'fields'			=> 'ids'
		);

		$posts = array_unique(array_merge( get_posts( $args ), get_posts( $args2 ) ));

	}

	$found_products = array();

	if ( $posts ) foreach ( $posts as $post ) {

		$product = get_product( $post );

		$found_products[ $post ] = dhvc_woo_product_page_formatted_name($product);

	}

	echo json_encode( $found_products );

	die();
}

add_action('wp_ajax_dhvc_woo_product_page_search_products', 'dhvc_woo_product_page_search_products');

function get_the_product_page_content( $more_link_text = null, $strip_teaser = false){
	global $page, $more, $preview, $pages, $multipage,$product_page;
	
	$post = $product_page;
	if ( null === $more_link_text )
		$more_link_text = __( '(more&hellip;)' );

	$output = '';
	$has_teaser = false;

	// If post password required and it doesn't match the cookie.
	if ( post_password_required( $post ) )
		return get_the_password_form( $post );
	
	if ( $page > count( $pages ) ) // if the requested page doesn't exist
		$page = count( $pages ); // give them the highest numbered page that DOES exist

	$content = $pages[$page - 1];
	if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
		$content = explode( $matches[0], $content, 2 );
		if ( ! empty( $matches[1] ) && ! empty( $more_link_text ) )
			$more_link_text = strip_tags( wp_kses_no_null( trim( $matches[1] ) ) );

		$has_teaser = true;
	} else {
		$content = array( $content );
	}

	if ( false !== strpos( $post->post_content, '<!--noteaser-->' ) && ( ! $multipage || $page == 1 ) )
		$strip_teaser = true;

	$teaser = $content[0];
	
	if ( $more && $strip_teaser && $has_teaser )
		$teaser = '';

	$output .= $teaser;

	if ( count( $content ) > 1 ) {
		if ( $more ) {
			$output .= '<span id="more-' . $post->ID . '"></span>' . $content[1];
		} else {
			if ( ! empty( $more_link_text ) )

				$output .= apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );
			$output = force_balance_tags( $output );
		}
	}

	if ( $preview ) 
		$output =	preg_replace_callback( '/\%u([0-9A-F]{4})/', '_convert_urlencoded_to_entities', $output );

	return $output;
}