<?php
/*
* Plugin Name:DHWCPage - WooCommerce Page Template Builder
* Plugin URI: http://sitesao.com/
* Description: Drag and drop page template builder for WooCommerce with WPBakery Page Builder.
* Version: 5.1.6
* Author: SiteSao Team
* Author URI: http://sitesao.com/
* License: License GNU General Public License version 2 or later;
* Copyright 2013  SiteSao
* WC requires at least: 3.0.0
* WC tested up to: 3.6.2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!defined('DHVC_WOO_PAGE')){
	define('DHVC_WOO_PAGE','dhvc-woocommerce-page');
}

if(!defined('DHVC_WOO_PAGE_VERSION')){
	define('DHVC_WOO_PAGE_VERSION','5.1.6');
}

if(!defined('DHVC_WOO_PAGE_URL')){
	define('DHVC_WOO_PAGE_URL',untrailingslashit( plugins_url( '/', __FILE__ ) ));
}

if(!defined('DHVC_WOO_PAGE_DIR')){
	define('DHVC_WOO_PAGE_DIR',untrailingslashit( plugin_dir_path( __FILE__ ) ));
}

class DHVC_Woo_Page{
	
	public function __construct(){
		add_action( 'plugins_loaded', array($this,'plugins_loaded'), 9 );
	}
	
	public function plugins_loaded(){
		
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // Require plugin.php to use is_plugin_active() below
		}
		
		$editor = false;
		if ( is_plugin_active( 'woocommerce/woocommerce.php' )) {
			if(is_plugin_active('cornerstone/cornerstone.php')){
				$editor = true;
				require_once DHVC_WOO_PAGE_DIR.'/includes/cornerstone.php';
			}
			if(defined('WPB_VC_VERSION')){
				$editor = true;

				require_once DHVC_WOO_PAGE_DIR.'/includes/vc.php';
				require_once DHVC_WOO_PAGE_DIR.'/includes/shortcodes/account.php';
				require_once DHVC_WOO_PAGE_DIR.'/includes/shortcodes/product.php';
				require_once DHVC_WOO_PAGE_DIR.'/includes/shortcodes/cart.php';
				require_once DHVC_WOO_PAGE_DIR.'/includes/shortcodes/checkout.php';

				require_once DHVC_WOO_PAGE_DIR.'/includes/shortcodes/archive.php';
			}
		}else{
			add_action('admin_notices', array($this,'woocommerce_notice'));
			return ;
		}
		
		if(!$editor){
			add_action('admin_notices', array($this,'notice'));
			return;
		}
		
		load_plugin_textdomain( DHVC_WOO_PAGE, false, basename(DHVC_WOO_PAGE_DIR) . '/languages' );
		
		require_once DHVC_WOO_PAGE_DIR.'/includes/functions.php';
		require_once DHVC_WOO_PAGE_DIR.'/includes/post-types.php';
		require_once DHVC_WOO_PAGE_DIR.'/includes/shortcode.php';
		
		if(is_admin()){
			require_once DHVC_WOO_PAGE_DIR.'/includes/admin.php';
		}else{
			require_once DHVC_WOO_PAGE_DIR.'/includes/frontend.php';
		}
		
		add_action('after_setup_theme', array($this,'add_support_woocommerce'),100);
		
		add_action('init',array($this,'init'));
	}
	
	public function add_support_woocommerce(){
		if(!current_theme_supports( 'woocommerce' )){
			add_theme_support('woocommerce');
		}
	}
	
	public function init(){
		add_action( 'admin_bar_menu', array($this,'admin_bar_edit_shop_link'), 1000 );
		add_action( 'admin_bar_menu', array($this,'admin_bar_edit_template_link'), 1000 );
	}
	
	
	public function notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '<div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431?ref=Sitesao" target="_blank">WPBakery Page Builder</a></strong> plugin to be installed and activated on your site.', DHVC_WOO_PAGE), $plugin['Name']) . '</p>
			  </div>';
	}
	
	public function woocommerce_notice(){
		$plugin = get_plugin_data(__FILE__);
		echo '<div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a></strong> plugin to be installed and activated on your site.', DHVC_WOO_PAGE), $plugin['Name']) . '</p>
			  </div>';
	}
	
	public function admin_bar_edit_template_link($wp_admin_bar){
		global $dhvc_single_product_template ;
		if ( is_singular('product') && !empty($dhvc_single_product_template) ) {
			$wp_admin_bar->add_menu( array(
				'id' => 'dhvc_woo_product_page-edit-link',
				'title' => __( 'Edit Product Template', DHVC_WOO_PAGE ),
				'href' => get_edit_post_link($dhvc_single_product_template->ID),
				'meta' => array( 'target' => '_blank' ),
			) );
		}
	}
	
	public function admin_bar_edit_shop_link($wp_admin_bar){
		if ( is_shop()) {
			$wp_admin_bar->add_menu( array(
				'id' => 'wp-admin-bar-edit',
				'title' => __( 'Edit Shop Page', DHVC_WOO_PAGE ),
				'href' => get_edit_post_link(wc_get_page_id('shop'))
			) );
		}
	}
}

new DHVC_Woo_Page();