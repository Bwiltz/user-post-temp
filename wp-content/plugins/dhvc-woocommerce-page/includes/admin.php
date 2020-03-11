<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DHVC_Woo_Page_Admin {
	
	public function __construct(){
		
		add_filter( 'woocommerce_get_settings_pages', array($this,'add_settings') );
		
		add_action('admin_enqueue_scripts',array($this,'admin_enqueue_styles'));
		
		add_action( 'admin_print_scripts-post.php', array( $this, 'admin_enqueue_scripts' ),100 );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'admin_enqueue_scripts' ),100 );

		
		//product meta data
		add_action('add_meta_boxes', array($this,'add_meta_boxes'));
		add_action( 'save_post', array($this,'save_product_meta_data'),1,2 );
		
		//product category form
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ), 5 );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_fields' ), 5, 2 );
		add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
		
	}
	
	public function add_settings($settings){
		$settings[] = include DHVC_WOO_PAGE_DIR.'/includes/admin-setting.php';
		return $settings;
	}
	
	
	public function admin_enqueue_scripts(){
		global $post;
		$post_type =  dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template');
		$url = '';
		if($post_type===get_post_type($post) && $product_id  = dhvc_woo_product_page_find_product_by_template($post->ID)){
			$url = dhvc_woo_product_page_get_preview_editor_url($post->ID,'',$product_id);
		}
		if('product'===get_post_type($post) && $product_template_id = dhvc_woo_product_page_get_custom_template($post)){
			$url = dhvc_woo_product_page_get_preview_editor_url($product_template_id);
		}
		if(!empty($url)){
			wp_register_script('dhvc_woo_page_admin',DHVC_WOO_PAGE_URL.'/assets/js/admin.js',array('jquery'),DHVC_WOO_PAGE_VERSION,true);
			wp_localize_script('dhvc_woo_page_admin', 'dhvc_woo_page_admin', array(
				'preview_builder'=>__("Preview Editor",DHVC_WOO_PAGE),
				'url'=>$url
			));
			wp_enqueue_script('dhvc_woo_page_admin');
		}

	}
	
	public function admin_enqueue_styles(){
		wp_enqueue_style('dhvc-woo-page-admin', DHVC_WOO_PAGE_URL.'/assets/css/admin.css');
	}
	
	public function add_meta_boxes(){
		add_meta_box('dhvc-woo-page-bulder-products-meta-box', __('Product Custom Template',DHVC_WOO_PAGE), array($this,'add_product_meta_box'), 'product','side');
	}
	
	public function add_product_meta_box(){
		global $post;
		$product_id = get_the_ID();
		$page_id = get_post_meta($product_id,'dhvc_woo_page_product',true);
		
		$selected_post_type =  dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template');
		if(get_post_type($page_id)!=$selected_post_type){
			delete_post_meta($product_id,'dhvc_woo_page_product');
		}
		$args = array(
			'post_status' => 'publish,private',
			'name'=>'dhvc_woo_page_product',
			'show_option_none'=>' ',
			'echo'=>false,
			'selected'=>absint($page_id)
		);
		wp_nonce_field ('dhvc_woocommerce_page_nonce', 'dhvc_woocommerce_page_nonce',false);
		if(empty($page_id)){
			list($product_template_id,$product_term,$is_custom_by_type) = dhvc_woo_product_page_get_custom_template($post,true);
			
			if(false!==$is_custom_by_type){
				echo '<span style="display: block;margin-bottom: 10px;margin-top: 20px;">';
				echo __('Current use custom template by type:',DHVC_WOO_PAGE).' <strong>'.ucfirst($is_custom_by_type).'</strong>';
				echo '</span>';
			}elseif(!empty($product_term)){
				echo '<span style="display: block;margin-bottom: 10px;margin-top: 20px;">';
				echo __('Current use custom template by category:',DHVC_WOO_PAGE).' <strong>'.$product_term.'</strong>';
				echo '</span>';
			}
		}
		echo '<span style="font-weight: bold;margin-bottom: 5px;display: block;">'.__("Select template for only product:",DHVC_WOO_PAGE).'</span>';
		echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='wc-enhanced-select-nostd' id=", dhvc_woo_product_page_dropdown_custom( $args ) );
		echo '<span class="description" style="margin-top: 5px;display: block;opacity: 0.8;font-size: 0.9em;">'.__('You can change template type in setting "Product Template Type" in "WooCommerce &rarr; Settings &rarr; Page Templates"',DHVC_WOO_PAGE).'</span>';
	}
	
	public function save_product_meta_data($post_id,$post){
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		
		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		// Check the nonce
		if (empty ( $_POST ['dhvc_woocommerce_page_nonce'] ) || ! wp_verify_nonce ( $_POST ['dhvc_woocommerce_page_nonce'], 'dhvc_woocommerce_page_nonce' )) {
			return;
		}
		
		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}
		
		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if(isset($_POST['dhvc_woo_page_product']) && !empty($_POST['dhvc_woo_page_product'])){
			update_post_meta( $post_id, 'dhvc_woo_page_product', absint($_POST['dhvc_woo_page_product']) );
		}else{
			delete_post_meta( $post_id, 'dhvc_woo_page_product');
		}
		
	}
	
	public function add_category_fields(){
		?>
		<div class="form-field">
			<label for="dhvc_woo_page_cat_product"><?php _e( 'Single Product Page Template', DHVC_WOO_PAGE ); ?></label>
			<?php 
			$args = array(
					'post_status' => 'publish,private',
					'name'=>'dhvc_woo_page_cat_product',
					'show_option_none'=>' ',
					'echo'=>false,
			);
			echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='wc-enhanced-select-nostd' id=", dhvc_woo_product_page_dropdown_custom( $args ) );
			
			?>
			<span class="description" style="margin-top: 5px;display: block;opacity: 0.8;"><?php _e('You can change template type in setting "Product Template Type" in "WooCommerce &rarr; Settings &rarr; Page Templates"',DHVC_WOO_PAGE)?></span>
		</div>
		<?php
	}
	
	public function edit_category_fields( $term, $taxonomy ) {
		$dhvc_woo_page_cat_product = get_term_meta( $term->term_id, 'dhvc_woo_page_cat_product', true );
		$selected_post_type =  dhvc_woo_page_get_option('dhvc_woo_page_template_type','dhwc_template');
		if(get_post_type($dhvc_woo_page_cat_product)!=$selected_post_type){
			delete_term_meta($term->term_id,'dhvc_woo_page_cat_product');
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php _e( 'Single Product Page', DHVC_WOO_PAGE ); ?></label></th>
			<td>
				<?php 
				$args = array(
						'post_status' => 'publish,private',
						'name'=>'dhvc_woo_page_cat_product',
						'show_option_none'=>' ',
						'echo'=>false,
						'selected'=>absint($dhvc_woo_page_cat_product)
				);
				echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='wc-enhanced-select-nostd' id=", dhvc_woo_product_page_dropdown_custom( $args ) );
				
				?>
				<span class="description" style="margin-top: 5px;display: block;opacity: 0.8;"><?php _e('You can change template type in setting "Product Template Type" in "WooCommerce &rarr; Settings &rarr; Page Templates"',DHVC_WOO_PAGE)?></span>
			</td>
		</tr>
		<?php
	}
	
	public function save_category_fields( $term_id, $tt_id, $taxonomy ) {
		
		if( isset($_POST['dhvc_woo_page_cat_product']) && !empty($_POST['dhvc_woo_page_cat_product'])){
			update_term_meta( $term_id, 'dhvc_woo_page_cat_product', absint( $_POST['dhvc_woo_page_cat_product'] ) );
		}else{
			delete_term_meta($term_id,  'dhvc_woo_page_cat_product');
		}
	}
	
	
}
new DHVC_Woo_Page_Admin();