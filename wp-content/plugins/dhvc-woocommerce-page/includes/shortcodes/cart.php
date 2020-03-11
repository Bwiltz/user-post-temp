<?php

class DHVC_Woo_Page_Shortcode_Cart {
	
	protected $_shortcode_loaded = false;
	
	public function __construct(){
		add_action('vc_after_set_mode', array($this,'map_shortcodes'));
		add_action('vc_after_set_mode', array($this,'map_page_shortcodes'));
		add_action('vc_load_shortcode', array($this,'add_shortcodes'));
		
		add_action('wp_head', array( $this, 'template_redirect' ) );
		
		add_action( 'wp_head', array($this,'add_custom_css'), 1000 );
	}
	
	public function add_custom_css(){
		if($empty_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_cart_empty')){
			echo dhvc_woo_page_get_template_custom_css($empty_page_template_id);
		}
	}
	
	public function template_redirect(){
		if(vc_is_frontend_editor() || vc_is_inline()){
			return;
		}
		$this->add_shortcodes();
		if(is_cart() && dhvc_woo_page_has_shortcode('dhvc_woo_cart')){
			add_filter('the_content', array(__CLASS__,'page_content'),999999);
		}
	}
	
	
	public function add_shortcodes(){
		if($this->_shortcode_loaded){
			return;
		}
		
		$shortcodes = array(
			'dhvc_woo_cart_item'		=> 'dhvc_woo_cart_item_shortcode',
			'dhvc_woo_cart_cross_sells'	=> 'dhvc_woo_cart_cross_sells_shortcode',
			'dhvc_woo_cart_coupon'		=> 'dhvc_woo_cart_coupon_shortcode',
			'dhvc_woo_cart_totals'		=> 'dhvc_woo_cart_totals_shortcode',
			'dhvc_woo_cart_empty'		=> 'dhvc_woo_cart_empty_shortcode',
		);
		
		foreach ($shortcodes as $tag=>$callback){
			add_shortcode($tag, array($this,$callback));
		}
		$this->_shortcode_loaded = true;
	}
	
	public static function page_content(){
		
		global $post;
		
		// Constants.
		wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
		
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-shipping-calculator-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.
		
		// Update Shipping. Nonce check uses new value and old value (woocommerce-cart). @todo remove in 4.0.
		if ( ! empty( $_POST['calc_shipping'] ) && ( wp_verify_nonce( $nonce_value, 'woocommerce-shipping-calculator' ) || wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) ) { // WPCS: input var ok.
			WC_Shortcode_Cart::calculate_shipping();
		
			// Also calc totals before we check items so subtotals etc are up to date.
			WC()->cart->calculate_totals();
		}
		
		// Check cart items are valid.
		do_action( 'woocommerce_check_cart_items' );
		
		// Calc totals.
		WC()->cart->calculate_totals();
		
		ob_start();
		echo '<div class="dhvc-woocommerce-page-cart">';
		if ( WC()->cart->is_empty() ) {
			$empty_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_cart_empty');
			$empty_page_template_id = dhvc_woo_page_icl_object_id($empty_page_template_id,'dhwc_page_template');
			if($empty_page_template_id && $empty_page_template = get_post($empty_page_template_id))
				echo dhvc_woo_page_template_the_content($empty_page_template->post_content);
			else
				wc_get_template( 'cart/cart-empty.php' );
		} else {
			do_action( 'woocommerce_before_cart' );
			echo dhvc_woo_page_template_the_content($post->post_content);
			do_action( 'woocommerce_after_cart' );
		}
		
		echo '</div>';
		echo ob_get_clean();
		
	}
	
	protected function _form_wrapper($content,$form_class=''){
		ob_start();
		?>
		<form class="<?php echo esc_attr($form_class)?>" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php echo $content?>
		</form>
		<?php 
		return apply_filters('dhvc_woo_page_cart_form_wapper_output', ob_get_clean());
	}
	
	public function dhvc_woo_cart_cross_sells_shortcode($atts){
		extract ( shortcode_atts ( array (
			'posts_per_page'=>4,
			'columns'		=>4,
			'orderby'		=>'date',
			'el_class' 		=> '' ,
			'css'			=> '',
		), $atts ) );
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		ob_start();
		woocommerce_cross_sell_display($posts_per_page,$columns,$orderby);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_cart_item_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		?>
		<?php do_action( 'woocommerce_before_cart_table' ); ?>
		<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
			<thead>
				<tr>
					<th class="product-remove">&nbsp;</th>
					<th class="product-thumbnail">&nbsp;</th>
					<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
					<th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
					<th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
					<th class="product-subtotal"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>
	
				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
	
					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						?>
						<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
	
							<td class="product-remove">
								<?php
									// @codingStandardsIgnoreLine
									echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
										'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										__( 'Remove this item', 'woocommerce' ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									), $cart_item_key );
								?>
							</td>
	
							<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
	
							if ( ! $product_permalink ) {
								echo $thumbnail; // PHPCS: XSS ok.
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
							}
							?>
							</td>
	
							<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
							<?php
							if ( ! $product_permalink ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
							} else {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
							}
	
							do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
	
							// Meta data.
							echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
	
							// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
							}
							?>
							</td>
	
							<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
								<?php
									echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
								?>
							</td>
	
							<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
							<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input( array(
									'input_name'   => "cart[{$cart_item_key}][qty]",
									'input_value'  => $cart_item['quantity'],
									'max_value'    => $_product->get_max_purchase_quantity(),
									'min_value'    => '0',
									'product_name' => $_product->get_name(),
								), $_product, false );
							}
	
							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
							?>
							</td>
	
							<td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
								<?php
									echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
								?>
							</td>
						</tr>
						<?php
					}
				}
				?>
				<?php do_action( 'woocommerce_cart_contents' ); ?>
				<tr>
					<td colspan="6" class="actions">
	
						<button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
	
						<?php do_action( 'woocommerce_cart_actions' ); ?>
	
						<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					</td>
				</tr>
				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</tbody>
		</table>
		<?php do_action( 'woocommerce_after_cart_table' ); ?>
		<?php
		$output = apply_filters('dhvc_woo_page_cart_item_shortcode_output', ob_get_clean());
		$output = $this->_form_wrapper($output,'woocommerce-cart-form');
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function dhvc_woo_cart_coupon_shortcode($atts){
		if(!wc_coupons_enabled())
			return;
		
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		?>
		<h2><?php _e( 'Coupon', 'woocommerce' ); ?></h2>
		<p class="form-row form-row-first">
			<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
		</p>
	
		<p class="form-row form-row-last">
			<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
		</p>
		
		<div class="clear"></div>
		<?php 
		wc_enqueue_js('jQuery(function(n){n(document).on("click",".woocommerce-coupon-form :input[type=submit]",function(o){n(":input[type=submit]",n(o.target).parents("form")).removeAttr("clicked"),n(o.target).attr("clicked","true")}),n(document.body).on("applied_coupon",function(){n(".woocommerce-coupon-form").removeClass("processing").unblock()}),n(".woocommerce-coupon-form").on("submit",function(o){n(document.activeElement),n(":input[type=submit][clicked=true]");var e,t,c=n(o.currentTarget);(t=e=c).is(".processing")||t.parents(".processing").length||e.addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),n(".woocommerce-cart-form").trigger("submit"),o.preventDefault(),o.stopPropagation()})});');
		$output = !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		return $this->_form_wrapper($output,'woocommerce-coupon-form');
		
	}
	public function dhvc_woo_cart_totals_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		woocommerce_cart_totals();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_cart_shipping_calculator_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		ob_start();
		woocommerce_shipping_calculator();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_cart_empty_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		ob_start();
		wc_get_template( 'cart/cart-empty.php' );
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function map_page_shortcodes(){
		$post_id = dhvc_woo_page_get_current_edit_page_id();
		$empty_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_cart_empty');
		if(empty($post_id) || empty($empty_page_template_id) || $post_id !==$empty_page_template_id){
			return;
		}
		
		vc_map ( array (
			"name" 			=> __( "Cart Empty", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_cart_empty",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-cart",
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
	
	public function map_shortcodes(){
		$post_id = dhvc_woo_page_get_current_edit_page_id();
		$cart_page_id = (int) wc_get_page_id('cart');
		if( empty($post_id) || empty($cart_page_id) || $post_id !== $cart_page_id ){
			return;
		}
		
		vc_map ( array (
			"name" 			=> __( "Cart Items", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_cart_item",
			'description' 	=> __("Cart items tables",DHVC_WOO_PAGE),
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-cart",
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
			"name" => __( "Cart Coupon", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_cart_coupon",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-cart",
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
			"name" => __( "Cart Totals", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_cart_totals",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-cart",
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
			"name" => __( "Cart Cross Sells", DHVC_WOO_PAGE ),
			"base" => "dhvc_woo_cart_cross_sells",
			"category" => __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" => "icon-dhvc-woo-cart",
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
		
	}
}
new DHVC_Woo_Page_Shortcode_Cart();