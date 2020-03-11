<?php

class DHVC_Woo_Page_Shortcode_Archive {
	
	protected $_shortcode_loaded = false;
	
	protected $_custom_css = '';
	
	public function __construct(){
		add_action('vc_after_set_mode', array($this,'map_shortcodes'));
		add_action('vc_load_shortcode', array($this,'add_shortcodes'));
		
		add_filter( 'template_include', array( $this, 'template_loader' ),99999999 );
		add_action('template_redirect', array( $this, 'template_redirect' ) );
		
		add_action( 'wp_head', array($this,'add_custom_css'), 1000 );
		
		//product term form
		add_action('current_screen', array($this,'term_form_field'));
		add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
	}
	
	public function term_form_field(){
		$product_taxonomies = dhvc_woo_page_template_allow_taxonomies();
		foreach ((array) $product_taxonomies as $tax_name=>$tax_label){
			add_action( $tax_name.'_add_form_fields', array( $this, 'add_category_fields' ), 5 );
			add_action( $tax_name.'_edit_form_fields', array( $this, 'edit_category_fields' ), 5, 2 );			
		}
	}
	
	public function add_category_fields(){
		?>
		<div class="form-field">
			<label for="dhvc_woo_page_cat_product"><?php _e( 'Page Template', DHVC_WOO_PAGE ); ?></label>
			<?php 
			$args = array(
				'post_status' => 'publish,private',
				'name'=>'dhvc_woo_page_template_id',
				'show_option_none'=>' ',
				'echo'=>false,
			);
			echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='wc-enhanced-select-nostd' id=", dhvc_woo_product_page_dropdown_custom( $args,'dhwc_page_template' ) );
			?>
		</div>
		<?php
	}
	
	public function edit_category_fields( $term, $taxonomy ) {
		$page_template_id = get_term_meta( $term->term_id, 'dhvc_woo_page_template_id', true );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php _e( 'Page Template', DHVC_WOO_PAGE ); ?></label></th>
			<td>
				<?php 
				$args = array(
						'post_status' => 'publish,private',
						'name'=>'dhvc_woo_page_template_id',
						'show_option_none'=>' ',
						'echo'=>false,
						'selected'=>absint($page_template_id)
				);
				echo str_replace(' id=', " style='width:100%' data-placeholder='" . __( 'Select a template&hellip;',DHVC_WOO_PAGE) .  "' class='wc-enhanced-select-nostd' id=", dhvc_woo_product_page_dropdown_custom( $args,'dhwc_page_template' ) );			
				?>
			</td>
		</tr>
		<?php
	}
	public function save_category_fields( $term_id, $tt_id, $taxonomy ){
		if( isset($_POST['dhvc_woo_page_template_id']) && !empty($_POST['dhvc_woo_page_template_id'])){
			update_term_meta( $term_id, 'dhvc_woo_page_template_id', absint( $_POST['dhvc_woo_page_template_id'] ) );
		}else{
			delete_term_meta($term_id,  'dhvc_woo_page_template_id');
		}
	}
	
	public function template_loader($template){
		global $dhvc_woo_page_template_archive;
		$archive_template = false;
		if((is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) )) && $shop_page = get_post(wc_get_page_id( 'shop' ))){
			if(dhvc_woo_page_has_shortcode('dhvc_woo_archive', $shop_page)){
				$archive_template = $shop_page;	
			}
		}elseif (is_product_taxonomy()){
			$term = get_queried_object();
			$taxonomy = $term->taxonomy;
			$page_template_id = get_term_meta( $term->term_id, 'dhvc_woo_page_template_id', true );
			$page_template_id = dhvc_woo_page_icl_object_id($page_template_id,'dhwc_page_template');
			if(!empty($page_template_id) && $taxonomy_template = get_post($page_template_id)){
				$archive_template = $taxonomy_template;
			}else{
				$taxonomy_template_id =  (int) dhvc_woo_page_get_option('dhvc_woo_page_'.$taxonomy);
				$taxonomy_template_id = dhvc_woo_page_icl_object_id($taxonomy_template_id,'dhwc_page_template');
				if($taxonomy_template_id && $taxonomy_template = get_post($taxonomy_template_id)){
					$archive_template = $taxonomy_template;
				}
			}
		}
		if(!empty($archive_template)){
			$dhvc_woo_page_template_archive = $archive_template;
			$this->_custom_css = dhvc_woo_page_get_template_custom_css($archive_template->ID);
			$find = array();
			$file = 'archive-product.php';
			$find[] = 'dhvc-woocommerce-page/'.$file;
			$template       = locate_template( $find );
			if ( ! $template || WC_TEMPLATE_DEBUG_MODE){
				$template = DHVC_WOO_PAGE_DIR . '/templates/' . $file;
			}
			return $template;
		}
		return $template;
	}
	
	public function add_custom_css(){
		echo $this->_custom_css;
	}
	
	public function template_redirect(){
		$this->add_shortcodes();
	}
	
	public function add_shortcodes(){
		if($this->_shortcode_loaded){
			return;
		}
		
		$shortcodes = array(
			'dhvc_woo_archive_title'		=> 'dhvc_woo_archive_title_shortcode',
			'dhvc_woo_archive_description'	=> 'dhvc_woo_archive_description_shortcode',
			'dhvc_woo_archive_products'		=> 'dhvc_woo_archive_products_shortcode',
		);
		
		foreach ($shortcodes as $tag=>$callback){
			add_shortcode($tag, array($this,$callback));
		}
		
		$this->_shortcode_loaded = true;
	}
	
	public function dhvc_woo_archive_title_shortcode($atts){
		extract ( shortcode_atts ( array (
			'tag'			=> 'h1',
			'el_class' 		=> '' ,
			'css'			=> '',
		), $atts ) );
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		$class_css = !empty($el_class) ? ' class="'.$el_class.'"' : '';
		ob_start();
		echo "<{$tag}{$class_css}>".woocommerce_page_title(false)."</{$tag}>";
		return ob_get_clean();
	}
	
	public function dhvc_woo_archive_description_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' 		=> '' ,
			'css'			=> '',
		), $atts ) );
		ob_start();
		echo '<div class="woocommerce-products-header">';
		woocommerce_taxonomy_archive_description();
		echo '</div>';
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_archive_products_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' 		=> '' ,
			'css'			=> '',
		), $atts ) );
		ob_start();
		if ( woocommerce_product_loop() ) {
		
			/**
			 * Hook: woocommerce_before_shop_loop.
			 *
			 * @hooked woocommerce_output_all_notices - 10
			 * @hooked woocommerce_result_count - 20
			 * @hooked woocommerce_catalog_ordering - 30
			 */
			do_action( 'woocommerce_before_shop_loop' );
		
			woocommerce_product_loop_start();
		
			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();
		
					/**
					 * Hook: woocommerce_shop_loop.
					 *
					 * @hooked WC_Structured_Data::generate_product_data() - 10
					*/
					do_action( 'woocommerce_shop_loop' );
		
					wc_get_template_part( 'content', 'product' );
				}
			}
		
			woocommerce_product_loop_end();
		
			/**
			 * Hook: woocommerce_after_shop_loop.
			 *
			 * @hooked woocommerce_pagination - 10
			*/
			do_action( 'woocommerce_after_shop_loop' );
		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function map_shortcodes(){
		$post_id = dhvc_woo_page_get_current_edit_page_id();
		$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
		$shop_page_id = (int) wc_get_page_id('shop');
		
		if($post_id !== $shop_page_id && 'dhwc_page_template'!==get_post_type($post_id) && 'dhwc_page_template' !== $post_type){
			return;
		}
		vc_map ( array (
			"name" 			=> __( "Archive Title", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_archive_title",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-archive",
			"description" 	=> __('Archive page title',DHVC_WOO_PAGE),
			"params" => array (
				array (
					"type" => "dropdown",
					'save_always'=>true,
					"heading" => __( "Title tag", DHVC_WOO_PAGE ),
					"param_name" => "tag",
					'std'=>'h1',
					'value'=>array(
						'H1'=> 'h1',
						'H2'=> 'h2',
						'H3'=> 'h3',
						'H4'=> 'h4',
						'H5'=> 'h5',
						'H6'=> 'h6',
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
			"name" 			=> __( "Archive Desc", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_archive_description",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-archive",
			"description" 	=> __('Archive taxonomy archive description',DHVC_WOO_PAGE),
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
			"name" 			=> __( "Archive Products", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_archive_products",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-archive",
			"description" 	=> __('Archive products list',DHVC_WOO_PAGE),
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
new DHVC_Woo_Page_Shortcode_Archive();