<?php

class DHVC_Woo_Page_Shortcode_Checkout {
	
	protected static $_current_order = false;
	
	protected $_shortcode_loaded = false;
	
	public function __construct(){
		add_action( 'vc_after_set_mode', array($this,'map_shortcodes'));
		add_action('vc_after_set_mode', array($this,'map_order_receipt_page_shortcodes'));
		add_action('vc_after_set_mode', array($this,'map_thankyou_page_shortcodes'));
		add_action('vc_load_shortcode', array($this,'add_shortcodes'));
		
		add_action( 'wp_head', array( $this, 'template_redirect' ) );
		
		add_action( 'wp_head', array($this,'add_custom_css'), 1000 );
	}
	
	public function add_custom_css(){
		$page_template_keys = array('dhvc_woo_page_checkout_order_receipt','dhvc_woo_page_checkout_thankyou');
		foreach ($page_template_keys as $key){
			$page_template_id = (int) dhvc_woo_page_get_option($key);
			if($page_template_id){
				echo dhvc_woo_page_get_template_custom_css($page_template_id);
			}
		}
	}
	
	public function template_redirect(){
		if(vc_is_frontend_editor() || vc_is_inline()){
			return;
		}
		$this->add_shortcodes();
		if(is_checkout() && dhvc_woo_page_has_shortcode('dhvc_woo_checkout')){
			add_filter('the_content', array(__CLASS__,'page_content'),999999);
		}
	}
	
	public static function page_content(){
		global $wp;
		
		// Check cart class is loaded or abort.
		if ( is_null( WC()->cart ) ) {
			return;
		}
		
		// Backwards compatibility with old pay and thanks link arguments.
		if ( isset( $_GET['order'] ) && isset( $_GET['key'] ) ) { // WPCS: input var ok, CSRF ok.
			wc_deprecated_argument( __CLASS__ . '->' . __FUNCTION__, '2.1', '"order" is no longer used to pass an order ID. Use the order-pay or order-received endpoint instead.' );
		
			// Get the order to work out what we are showing.
			$order_id = absint( $_GET['order'] ); // WPCS: input var ok.
			$order    = wc_get_order( $order_id );
		
			if ( $order && $order->has_status( 'pending' ) ) {
				$wp->query_vars['order-pay'] = absint( $_GET['order'] ); // WPCS: input var ok.
			} else {
				$wp->query_vars['order-received'] = absint( $_GET['order'] ); // WPCS: input var ok.
			}
		}
		ob_start();
		echo '<div class="dhvc-woocommerce-page-checkout">';
		// Handle checkout actions.
		if ( ! empty( $wp->query_vars['order-pay'] ) ) {
		
			self::order_pay( $wp->query_vars['order-pay'] );
		
		} elseif ( isset( $wp->query_vars['order-received'] ) ) {
		
			self::order_received( $wp->query_vars['order-received'] );
		
		} else {
		
			self::checkout();
		
		}
		echo '</div>';
		echo ob_get_clean();
	}
	
	public function add_shortcodes(){
		if($this->_shortcode_loaded){
			return;
		}
		
		$shortcodes = array(
			'dhvc_woo_checkout_coupon'					=> 'dhvc_woo_checkout_coupon_shortcode',
			'dhvc_woo_checkout_billing'					=>'dhvc_woo_checkout_billing_shortcode',
			'dhvc_woo_checkout_shipping'				=>'dhvc_woo_checkout_shipping_shortcode',
			'dhvc_woo_checkout_order'					=>'dhvc_woo_checkout_order_shortcode',
			'dhvc_woo_checkout_payment'					=>'dhvc_woo_checkout_payment_shortcode',
			'dhvc_woo_checkout_thankyou_message'		=> 'dhvc_woo_checkout_thankyou_message_shortcode',
			'dhvc_woo_checkout_thankyou_overview'		=>'dhvc_woo_checkout_thankyou_overview_shortcode',
			'dhvc_woo_checkout_thankyou_order_details'	=> 'dhvc_woo_checkout_thankyou_order_details_shortcode',
			'dhvc_woo_checkout_order_receipt'			=> 'dhvc_woo_checkout_order_receipt_shortcode'
		);
		
		foreach ($shortcodes as $tag=>$callback){
			add_shortcode($tag, array($this,$callback));
		}
		$this->_shortcode_loaded = true;
	}
	
	/**
	 * Show the pay page.
	 *
	 * @throws Exception When validate fails.
	 * @param int $order_id Order ID.
	 */
	private static function order_pay( $order_id ) {
	
		do_action( 'before_woocommerce_pay' );
	
		$order_id = absint( $order_id );
	
		// Pay for existing order.
		if ( isset( $_GET['pay_for_order'], $_GET['key'] ) && $order_id ) { // WPCS: input var ok, CSRF ok.
			try {
				$order_key          = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
				$order              = wc_get_order( $order_id );
				$hold_stock_minutes = (int) dhvc_woo_page_get_option( 'woocommerce_hold_stock_minutes', 0 );
	
				// Order or payment link is invalid.
				if ( ! $order || $order->get_id() !== $order_id || ! hash_equals( $order->get_order_key(), $order_key ) ) {
					throw new Exception( __( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ) );
				}
	
				// Logged out customer does not have permission to pay for this order.
				if ( ! current_user_can( 'pay_for_order', $order_id ) && ! is_user_logged_in() ) {
					echo '<div class="woocommerce-info">' . esc_html__( 'Please log in to your account below to continue to the payment form.', 'woocommerce' ) . '</div>';
					woocommerce_login_form(
						array(
							'redirect' => $order->get_checkout_payment_url(),
						)
					);
					return;
				}
	
				// Logged in customer trying to pay for someone else's order.
				if ( ! current_user_can( 'pay_for_order', $order_id ) ) {
					throw new Exception( __( 'This order cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ) );
				}
	
				// Does not need payment.
				if ( ! $order->needs_payment() ) {
					/* translators: %s: order status */
					throw new Exception( sprintf( __( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ) );
				}
	
				// Ensure order items are still stocked if paying for a failed order. Pending orders do not need this check because stock is held.
				if ( ! $order->has_status( 'pending' ) ) {
					$quantities = array();
	
					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();
	
							if ( ! $product ) {
								continue;
							}
	
							$quantities[ $product->get_stock_managed_by_id() ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $item->get_quantity() : $item->get_quantity();
						}
					}
	
					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();
	
							if ( ! $product ) {
								continue;
							}
	
							if ( ! apply_filters( 'woocommerce_pay_order_product_in_stock', $product->is_in_stock(), $product, $order ) ) {
								/* translators: %s: product name */
								throw new Exception( sprintf( __( 'Sorry, "%s" is no longer in stock so this order cannot be paid for. We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name() ) );
							}
	
							// We only need to check products managing stock, with a limited stock qty.
							if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
								continue;
							}
	
							// Check stock based on all items in the cart and consider any held stock within pending orders.
							$held_stock     = ( $hold_stock_minutes > 0 ) ? wc_get_held_stock_quantity( $product, $order->get_id() ) : 0;
							$required_stock = $quantities[ $product->get_stock_managed_by_id() ];
	
							if ( $product->get_stock_quantity() < ( $held_stock + $required_stock ) ) {
								/* translators: 1: product name 2: quantity in stock */
								throw new Exception( sprintf( __( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) ) );
							}
						}
					}
				}
	
				WC()->customer->set_props(
					array(
						'billing_country'  => $order->get_billing_country() ? $order->get_billing_country() : null,
						'billing_state'    => $order->get_billing_state() ? $order->get_billing_state() : null,
						'billing_postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : null,
					)
				);
				WC()->customer->save();
	
				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
	
				if ( count( $available_gateways ) ) {
					current( $available_gateways )->set_current();
				}
	
				wc_get_template(
					'checkout/form-pay.php', array(
						'order'              => $order,
						'available_gateways' => $available_gateways,
						'order_button_text'  => apply_filters( 'woocommerce_pay_order_button_text', __( 'Pay for order', 'woocommerce' ) ),
					)
				);
	
			} catch ( Exception $e ) {
				wc_print_notice( $e->getMessage(), 'error' );
			}
		} elseif ( $order_id ) {
	
			// Pay for order after checkout step.
			$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
			$order     = wc_get_order( $order_id );
	
			if ( $order && $order->get_id() === $order_id && hash_equals( $order->get_order_key(), $order_key ) ) {
	
				if ( $order->needs_payment() ) {
					//save order
					self::$_current_order = $order;
					
					$order_receipt_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_checkout_order_receipt');
					$order_receipt_page_template_id = dhvc_woo_page_icl_object_id($order_receipt_page_template_id,'dhwc_page_template');
					if($order_receipt_page_template_id && $order_receipt_page_template = get_post($order_receipt_page_template_id))
						echo dhvc_woo_page_template_the_content($order_receipt_page_template->post_content);
					else
						wc_get_template( 'checkout/order-receipt.php', array( 'order' => $order ) );
	
				} else {
					/* translators: %s: order status */
					wc_print_notice( sprintf( __( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ), 'error' );
				}
			} else {
				wc_print_notice( __( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ), 'error' );
			}
		} else {
			wc_print_notice( __( 'Invalid order.', 'woocommerce' ), 'error' );
		}
	
		do_action( 'after_woocommerce_pay' );
	}
	
	/**
	 * Show the thanks page.
	 *
	 * @param int $order_id Order ID.
	 */
	private static function order_received( $order_id = 0 ) {
		$order = false;
	
		// Get the order.
		$order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $order_id ) );
		$order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET['key'] ) ? '' : wc_clean( wp_unslash( $_GET['key'] ) ) ); // WPCS: input var ok, CSRF ok.
	
		if ( $order_id > 0 ) {
			$order = wc_get_order( $order_id );
			if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
				$order = false;
			}
		}
	
		// Empty awaiting payment session.
		unset( WC()->session->order_awaiting_payment );
	
		// In case order is created from admin, but paid by the actual customer, store the ip address of the payer.
		if ( $order ) {
			$order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
			$order->save();
		}
	
		// Empty current cart.
		wc_empty_cart();
		
		//save order
		self::$_current_order = $order;
		
		$thankyou_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_checkout_thankyou');
		$thankyou_page_template_id = dhvc_woo_page_icl_object_id($thankyou_page_template_id,'dhwc_page_template');
		if($thankyou_page_template_id && $thankyou_page_template = get_post($thankyou_page_template_id))
			echo dhvc_woo_page_template_the_content($thankyou_page_template->post_content);
		else
			wc_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
	}
	
	/**
	 * Show the checkout.
	 */
	private static function checkout() {
		global $post;
		// Show non-cart errors.
		do_action( 'woocommerce_before_checkout_form_cart_notices' );
	
		// Check cart has contents.
		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
			return;
		}
	
		// Check cart contents for errors.
		do_action( 'woocommerce_check_cart_items' );
	
		// Calc totals.
		WC()->cart->calculate_totals();
	
		// Get checkout object.
		$checkout = WC()->checkout();
	
		if ( empty( $_POST ) && wc_notice_count( 'error' ) > 0 ) { // WPCS: input var ok, CSRF ok.
	
			wc_get_template( 'checkout/cart-errors.php', array( 'checkout' => $checkout ) );
	
		} else {
	
			$non_js_checkout = ! empty( $_POST['woocommerce_checkout_update_totals'] ); // WPCS: input var ok, CSRF ok.
	
			if ( wc_notice_count( 'error' ) === 0 && $non_js_checkout ) {
				wc_add_notice( __( 'The order totals have been updated. Please confirm your order by pressing the "Place order" button at the bottom of the page.', 'woocommerce' ) );
			}
			
			do_action( 'woocommerce_before_checkout_form', $checkout );
			
			if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
				echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
				return;
			}
			
			?>
			<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
			<?php 
			echo dhvc_woo_page_template_the_content($post->post_content);
			do_action( 'woocommerce_after_checkout_form', $checkout );
			?>
			</form>
			<?php 
		}
	}
	
	public function dhvc_woo_checkout_order_receipt_shortcode(){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		
		if(!empty(self::$_current_order))
			wc_get_template( 'checkout/order-receipt.php', array( 'order' => self::$_current_order ) );
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
		
	}
	
	public function dhvc_woo_checkout_thankyou_message_shortcode($atts){
		extract ( shortcode_atts ( array (
			'failed_message'	=>'',
			'received_message'	=>'',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		$order = self::$_current_order;
		if($order){
			if($order->has_status('failed')){
				echo $failed_message;
				?>
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'woocommerce' ) ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My account', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</p>
				<?php
			}else{
				echo $received_message;
			}
		}else{
			echo $received_message;
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_checkout_thankyou_overview_shortcode($atts){
		extract ( shortcode_atts ( array (
			'failed_message'	=>'',
			'received_message'	=>'',
			'el_class' 			=> '' ,
			'css'				=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		ob_start();
		
		$order = self::$_current_order;
		if(!$order || $order->has_status('failed'))
			return;
		
		?>
		<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

			<li class="woocommerce-order-overview__order order">
				<?php _e( 'Order number:', 'woocommerce' ); ?>
				<strong><?php echo $order->get_order_number(); ?></strong>
			</li>

			<li class="woocommerce-order-overview__date date">
				<?php _e( 'Date:', 'woocommerce' ); ?>
				<strong><?php echo wc_format_datetime( $order->get_date_created() ); ?></strong>
			</li>

			<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
				<li class="woocommerce-order-overview__email email">
					<?php _e( 'Email:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_billing_email(); ?></strong>
				</li>
			<?php endif; ?>

			<li class="woocommerce-order-overview__total total">
				<?php _e( 'Total:', 'woocommerce' ); ?>
				<strong><?php echo $order->get_formatted_order_total(); ?></strong>
			</li>

			<?php if ( $order->get_payment_method_title() ) : ?>
				<li class="woocommerce-order-overview__payment-method method">
					<?php _e( 'Payment method:', 'woocommerce' ); ?>
					<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
				</li>
			<?php endif; ?>

		</ul>

		<?php 
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); 
		
		//It display by shortcode
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		do_action( 'woocommerce_thankyou', $order->get_id() ); 
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_checkout_thankyou_order_details_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		
		if(!empty(self::$_current_order) && !self::$_current_order->has_status('failed'))
			woocommerce_order_details_table(self::$_current_order->get_id());
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_checkout_login_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		woocommerce_checkout_login_form();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_checkout_coupon_shortcode($atts){
		if(!wc_coupons_enabled())
			return;
		extract ( shortcode_atts ( array (
			''=>'',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		ob_start();
		?>
		<style type="text/css">.dhvc-woocommerce-page-checkout .woocommerce-form-coupon-toggle{display: none;}</style>
		<div class="dhvc_woo_checkout_coupon"></div>
		<?php
		wc_enqueue_js('var coupon_form=jQuery(".woocommerce-checkout form.woocommerce-form-coupon"),coupon_info=jQuery("div.woocommerce-form-coupon-toggle > .woocommerce-info");coupon_info.removeClass("woocommerce-info");var coupon_toggle=jQuery(".woocommerce-checkout div.woocommerce-form-coupon-toggle");jQuery(".dhvc_woo_checkout_coupon").append(coupon_toggle),coupon_toggle.show(),coupon_form.insertAfter(coupon_toggle),jQuery(document).on("checkout_error updated_checkout",function(){jQuery("form.woocommerce-checkout").children(".woocommerce-error").prependTo(".dhvc-woocommerce-page-checkout")});');
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_checkout_billing_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		ob_start();
		WC_Checkout::instance()->checkout_form_billing();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();	
	}
	
	public function dhvc_woo_checkout_shipping_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		WC_Checkout::instance()->checkout_form_shipping();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_checkout_order_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		?>
		<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php woocommerce_order_review() ?>
		</div>
		<?php 
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_checkout_payment_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		woocommerce_checkout_payment();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	
	}
	
	public function map_order_receipt_page_shortcodes(){
		$post_id = dhvc_woo_page_get_current_edit_page_id();
		$order_receipt_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_checkout_order_receipt');
		if(empty($post_id) || empty($order_receipt_page_template_id) || $post_id !==$order_receipt_page_template_id)
			return;
		
		vc_map ( array (
			"name" 			=> __( "Checkout order receipt", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_order_receipt",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
	
	public function map_thankyou_page_shortcodes(){
		$post_id = dhvc_woo_page_get_current_edit_page_id();
		$thankyou_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_checkout_thankyou');
		if(empty($post_id) || empty($thankyou_page_template_id) || $post_id !==$thankyou_page_template_id){
			return;
		}
		
		vc_map ( array (
			"name" 			=> __( "Thank you message", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_thankyou_message",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
			"params" => array (
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Order failed message", DHVC_WOO_PAGE ),
					"param_name" => "failed_message",
					'value'=> 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.',
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Order received message", DHVC_WOO_PAGE ),
					"param_name" => "received_message",
					'value'=> 'Thank you. Your order has been received.',
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
			"name" 			=> __( "Order overview", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_thankyou_overview",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
			"name" 			=> __( "Order details", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_thankyou_order_details",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
		$checkout_page_id = (int) wc_get_page_id('checkout');
		if( empty($post_id) || empty($checkout_page_id) || $post_id !== $checkout_page_id ){
			return;
		}
		
		vc_map ( array (
			"name" 			=> __( "Checkout coupon form", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_coupon",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
			"name" 			=> __( "Checkout billing fields", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_billing",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
			"name" 			=> __( "Checkout shipping fields", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_shipping",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
			"name" 			=> __( "Checkout order items", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_order",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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
			"name" 			=> __( "Checkout payment", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_checkout_payment",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-checkout",
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

new DHVC_Woo_Page_Shortcode_Checkout();