<?php
// I am not sure what this does - BW//
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
if( !defined( 'PROPEL_CSR_ADMIN_EMAIL' ) ) {
  define( 'PROPEL_CSR_ADMIN_EMAIL', 'purchase.orders@scitent.com' );
}

/*// This enqueues cleanstart CSS //*/
function salient_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array('font-awesome'));
    wp_enqueue_style( 'catalog-style', get_stylesheet_directory_uri() . '/css/catalog-style.css');
    wp_enqueue_style( 'my-courses-style', get_stylesheet_directory_uri() . '/css/my-courses-style.css');
    // wp_enqueue_script( 'registration-customization','/wp-content/themes/propel1_child_salient_child/registration-customization.js');
}
add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles');

function propel1_child_salient_child_add_woocommerce_support() {
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'propel1_child_salient_child_add_woocommerce_support' );


//Sku product page redirects //
function redirect_sku_slugs() {
  global $wpdb;
  $uri = explode('/', $_SERVER["REQUEST_URI"]);
  if ($uri[1] == 'sku') {
    error_log("sku slugs loaded: ".$uri[2]);
    $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";
    $product_id = $wpdb->get_var( 
      $wpdb->prepare( 
        $product_query, 
        $uri[2] 
      ) );
    error_log($product_id);
    error_log(get_permalink( $product_id ));
    if ($product_id){
      wp_redirect(get_permalink( $product_id )); 
      exit;
    }
  }
}
add_action( 'init', 'redirect_sku_slugs' );


// Limits use to international sales with Fastspring //
include 'woo_limit_gateway_by_country.php';


/*** Prevent Woo From over-writing user name with billing name ***/
add_action( 'woocommerce_checkout_update_customer', 'custom_checkout_update_customer', 10, 2 );
function custom_checkout_update_customer( $customer, $data ) {
	if ( ! is_user_logged_in() || is_admin() ) {
		return;
	}

	// Get the user ID
	$user_id = $customer->get_id();

	// Get the default WordPress first name and last name (if they exist)
	$user_first_name = get_user_meta( $user_id, 'first_name', true );
	$user_last_name  = get_user_meta( $user_id, 'last_name', true );

	if ( empty( $user_first_name ) || empty( $user_last_name ) ) {
		return;
	}

	// set the values by default worpress ones, before it's saved to DB
	$customer->set_first_name( $user_first_name );
	$customer->set_last_name( $user_last_name );
}

// Modify ACF Form Label for Post Title Field
function wd_post_title_acf_name( $field ) {
     if( is_singular( 'marketplace-submission' ) ) { // if on the marketplace-submission page
          $field['label'] = 'Title';
     } else {
          $field['label'] = 'Program Name';
     }
     return $field;
}
add_filter('acf/load_field/name=_post_title', 'wd_post_title_acf_name');

// Modify ACF Form Label for Post Content Field
function wd_post_content_acf_name( $field ) {
     if( is_singular( 'marketplace-submission' ) ) { // if on the marketplace-submission page
          $field['label'] = 'Content';
     } else {
          $field['label'] = 'Please describe how you are using technology in detail.';
     }
     return $field;
}
add_filter('acf/load_field/name=_post_content', 'wd_post_content_acf_name');


/**
 * Changes the redirect URL for the Return To Shop button in the cart//
 *
 * @return string
 */
function wc_empty_cart_redirect_url() {
	return '/course-catalog/';
}
add_filter( 'woocommerce_return_to_shop_redirect', 'wc_empty_cart_redirect_url' );


/* Function to detect IE */
function my_custom_scripts() {
  wp_enqueue_script( 'ie-detect', get_stylesheet_directory_uri() . '/js/ie-detect.js', array( 'jquery' ),'',true );
}
add_action( 'wp_enqueue_scripts', 'my_custom_scripts' );


// Helps suppress block editor //
add_filter('use_block_editor_for_post', '__return_false');


// Leave this to ensure WOO versions are correct until after DB is clones up through live....then it can go away. Not sure if this is needed - BW //
if (class_exists('WC_Install')){
  WC_Install::update_db_version();
};


