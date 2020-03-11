<?php

class DHVC_Woo_Page_Shortcode_Account {
	
	protected $_shortcode_loaded = false;
	
	public function __construct(){
		add_action('vc_after_set_mode', array($this,'map_shortcodes'));
		add_action('vc_after_set_mode', array($this,'map_page_shortcodes'));
		add_action('vc_load_shortcode', array($this,'add_shortcodes'));
		
		add_action( 'wp_head', array( $this, 'template_redirect' ) );
		
		add_action( 'wp_head', array($this,'add_custom_css'), 1000 );
	}
	
	public function add_custom_css(){
		if($account_login_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_account_login')){
			echo dhvc_woo_page_get_template_custom_css($account_login_page_template_id);
		}
	}
	
	public function template_redirect(){
		if(vc_is_frontend_editor() || vc_is_inline()){
			return;
		}
		$this->add_shortcodes();
		if(is_account_page() && dhvc_woo_page_has_shortcode('dhvc_woo_account')){	
			add_filter('the_content', array(__CLASS__,'page_content'),999999);
		}
	}
	
	
	public static function page_content(){
		global $wp, $post;
		// Check cart class is loaded or abort.
		if ( is_null( WC()->cart ) ) {
			return;
		}
		ob_start();
		echo '<div class="dhvc-woocommerce-page-account">';
		
		
		if ( ! is_user_logged_in() ) {


			$message = apply_filters( 'woocommerce_my_account_message', '' );
				
			if ( ! empty( $message ) ) {
				wc_add_notice( $message );
			}
				
			// After password reset, add confirmation message.
			if ( ! empty( $_GET['password-reset'] ) ) { // WPCS: input var ok, CSRF ok.
				wc_add_notice( __( 'Your password has been reset successfully.', 'woocommerce' ) );
			}
				
			if ( isset( $wp->query_vars['lost-password'] ) ) {
				WC_Shortcode_My_Account::lost_password();
			}else{
				$account_login_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_account_login');
				
				$account_login_page_template_id = dhvc_woo_page_icl_object_id($account_login_page_template_id,'dhwc_page_template');
				
				if($account_login_page_template_id && $account_login_page_template = get_post($account_login_page_template_id)){
					//Prints all notices
					woocommerce_output_all_notices();
					echo dhvc_woo_page_template_the_content($account_login_page_template->post_content);
				}else{
					echo dhvc_woo_page_template_the_content('[woocommerce_my_account]');
				}
			}
		}else{
			//Prints all notices
			woocommerce_output_all_notices();
			echo dhvc_woo_page_template_the_content($post->post_content);
		}
		echo '</div>';
		echo ob_get_clean();
	}
	
	public function add_shortcodes(){
		if($this->_shortcode_loaded)
			return;
		
		$shortcodes = array(
			'dhvc_woo_account_navigation'			=> 'dhvc_woo_account_navigation_shortcode',
			'dhvc_woo_account_dashboard_container'	=> 'dhvc_woo_account_dashboard_container_shortcode',
			'dhvc_woo_account_dashboard'			=> 'dhvc_woo_account_dashboard_shortcode',
			'dhvc_woo_account_orders'				=> 'dhvc_woo_account_orders_shortcode',
			'dhvc_woo_account_downloads'			=> 'dhvc_woo_account_downloads_shortcode',
			'dhvc_woo_account_address'				=> 'dhvc_woo_account_address_shortcode',
			'dhvc_woo_account_payment_methods' 		=> 'dhvc_woo_account_payment_methods_shortcode',
			'dhvc_woo_account_details'				=> 'dhvc_woo_account_details_shortcode',
			'dhvc_woo_account_login'				=> 'dhvc_woo_account_login_shortcode',
			'dhvc_woo_account_register'				=> 'dhvc_woo_account_register_shortcode'
		);
		
		foreach ($shortcodes as $tag=>$callback){
			add_shortcode($tag, array($this,$callback));
		}
		$this->_shortcode_loaded = true;
	}
	
	public function dhvc_woo_account_login_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		
		?>
		<div class="woocommerce">
			<form class="woocommerce-form woocommerce-form-login login" method="post">
	
				<?php do_action( 'woocommerce_login_form_start' ); ?>
	
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
				</p>
	
				<?php do_action( 'woocommerce_login_form' ); ?>
	
				<p class="form-row">
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="woocommerce-Button button" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
					</label>
				</p>
				<p class="woocommerce-LostPassword lost_password">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
				</p>
	
				<?php do_action( 'woocommerce_login_form_end' ); ?>
	
			</form>
		</div>
		<?php 
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_account_register_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		if(get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ):
		?>
		<div class="woocommerce">
			<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
	
				<?php do_action( 'woocommerce_register_form_start' ); ?>
	
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
	
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
					</p>
	
				<?php endif; ?>
	
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
				</p>
	
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
	
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
					</p>
	
				<?php endif; ?>
	
				<?php do_action( 'woocommerce_register_form' ); ?>
	
				<p class="woocommerce-FormRow form-row">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
				</p>
	
				<?php do_action( 'woocommerce_register_form_end' ); ?>
	
			</form>
		</div>
		<?php 
		endif;
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_account_navigation_shortcode($atts){
		extract ( shortcode_atts ( array (
			'custom_style'      => '',
			'responsive'		=>'',
			'responsive_label'	=>'My Account Menu',
			'el_class' 			=> '' ,
			'css' 				=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		if(!empty($custom_style)){
			$el_class .=' is-custom-style';	
		}
		ob_start();
		if(!empty($responsive)){
			$el_class .= ' woocommerce-MyAccount-responsive-navigation';
			if(!defined('DHVC_WOO_MYACCOUNT_RESPONSIVE_NAVIGATION_JS')){
				define('DHVC_WOO_MYACCOUNT_RESPONSIVE_NAVIGATION_JS', 1);
				wc_enqueue_js("
					var account_nav = jQuery('.woocommerce-MyAccount-navigation');
					jQuery(document.body).on('click','.woocommerce-MyAccount-navigation-toggle',function(e){
						e.stopPropagation();
	            		e.preventDefault();
						if(account_nav.hasClass('is-js-showing')){
							jQuery('.woocommerce-MyAccount-navigation').slideUp(500);
							account_nav.removeClass('is-js-showing');
						}else{
							jQuery('.woocommerce-MyAccount-navigation').slideDown(500);
							account_nav.addClass('is-js-showing');
						}
					});
				");
			}
			echo '<a href="#" class="woocommerce-MyAccount-navigation-toggle">'.esc_html($responsive_label).'</a>';
		}
		woocommerce_account_navigation();
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_account_dashboard_container_shortcode($atts,$content=null){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		global $wp;
		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key) {
					continue;
				}
				if ( has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
					return;
				}
			}
		}
		
		ob_start();
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		echo wpb_js_remove_wpautop($content);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_account_dashboard_shortcode($atts){
		extract ( shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		global $wp;
		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key) {
					continue;
				}
				if ( has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
					return;
				}
			}
		}
		
		ob_start();
		
		wc_get_template( 'myaccount/dashboard.php', array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_account_orders_shortcode($atts){
		extract ( shortcode_atts ( array (
			'show_in_tab' => '',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		global $wp;
		
		ob_start();
		
		$alow_enpoint = array('orders','view-order');
		
		if(!empty($show_in_tab) && !isset($wp->query_vars['view-order'])){
			$current_page = 1;
			if(!empty($wp->query_vars['orders'])){
				$current_page = $wp->query_vars['orders'];
			}
			woocommerce_account_orders($current_page);
		}else{
			foreach ( (array) $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key || !in_array($key, $alow_enpoint)) {
					continue;
				}
				if ( has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
					do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
				}
			}	
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();	
	}
	
	public function dhvc_woo_account_downloads_shortcode($atts){
		extract ( shortcode_atts ( array (
			'show_in_tab'=>'',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		global $wp;
		
		$key = 'downloads';
		$value = '';
		
		ob_start();
		
		if(!empty($show_in_tab)){
			do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
		}elseif (isset($wp->query_vars[$key]) && has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
			do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();		
	}
	
	public function dhvc_woo_account_address_shortcode($atts){
		extract ( shortcode_atts ( array (
			'show_in_tab'=>'',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		global $wp;
		
		ob_start();
		
		$alow_enpoint = array('edit-address');
		
		if(!empty($show_in_tab) && empty($wp->query_vars['edit-address'])){
			do_action( 'woocommerce_account_edit-address_endpoint','' );
		}else{
			foreach ( (array) $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key || !in_array($key, $alow_enpoint)) {
					continue;
				}
				if ( has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
					do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
				}
			}	
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
		
	}
	
	public function dhvc_woo_account_payment_methods_shortcode($atts){
		extract ( shortcode_atts ( array (
			'show_in_tab'=>'',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		global $wp;
		
		ob_start();
		
		$alow_enpoint = array('payment-methods','add-payment-method');
		
		if(!empty($show_in_tab) && !isset($wp->query_vars['add-payment-method'])){
			do_action( 'woocommerce_account_add-payment-method_endpoint','' );
		}else{
			foreach ( (array) $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key || !in_array($key, $alow_enpoint)) {
					continue;
				}
				if ( has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
					do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
				}
			}	
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_account_details_shortcode($atts){
		extract ( shortcode_atts ( array (
			'show_in_tab'=>'',
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );
		
		$el_class .= dhvc_woo_page_get_shortcode_custom_css_class($css);
		
		ob_start();
		
		global $wp;
		
		$key = 'edit-account';
		$value = '';
	
		if(!empty($show_in_tab)){
			do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
		}elseif (isset($wp->query_vars[$key]) && has_action( 'woocommerce_account_' . $key . '_endpoint' ) ) {
			do_action( 'woocommerce_account_' . $key . '_endpoint', $value );
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function map_page_shortcodes(){
		$post_id = dhvc_woo_page_get_current_edit_page_id();
		$account_login_page_template_id = (int) dhvc_woo_page_get_option('dhvc_woo_page_account_login');
		if(empty($post_id) || empty($account_login_page_template_id) || $post_id !==$account_login_page_template_id)
			return;
		
		vc_map ( array (
			"name" 			=> __( "Account login", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_login",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
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
			"name" 			=> __( "Account register", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_register",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
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
		$account_page_id = (int) wc_get_page_id('myaccount');
		if( empty($post_id) || empty($account_page_id) || $post_id !== $account_page_id ){
			return;
		}
		
		vc_map ( array (
			"name" 			=> __( "Account navigation", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_navigation",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
			"params" => array (
				array (
					"type" 			=> "checkbox",
					'save_always'	=> true,
					"heading" 		=> __( "Use Custom style ?", DHVC_WOO_PAGE ),
					"param_name" 	=> "custom_style",
					'value'			=> 'yes',
				),
				array (
					"type" 			=> "checkbox",
					'save_always'	=>true,
					"heading" 		=> __( "Use responsive navigation", DHVC_WOO_PAGE ),
					"param_name" 	=> "responsive",
					'value'			=>'yes',
				),
				array (
					"type" => "textfield",
					'save_always'=>true,
					"heading" => __( "Responsive navigation label", DHVC_WOO_PAGE ),
					"param_name" => "responsive_label",
					'value'=>'My Account Menu',
					'dependency' => array(
						'element' => 'responsive',
						'not_empty' => true,
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
		
		vc_map(array(
			'base' 						=> 'dhvc_woo_account_dashboard_container',
			"category" 					=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			'name' 						=> __('Account dashboard container', DHVC_WOO_PAGE),
			'controls' 					=> 'full',
			'as_child' => array(
				'only' => 'vc_row,vc_column,vc_section',
			),
			'content_element' 			=> true,
			'is_container' 				=> true,
			'icon' 						=> 'icon-dhvc-woo-account',
			'js_view' 					=> 'VcColumnView',
			'show_settings_on_create' 	=> false,
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', DHVC_WOO_PAGE ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', DHVC_WOO_PAGE ),
				),
				array(
					'type' => 'css_editor',
					'heading' => __('Css', DHVC_WOO_PAGE),
					'param_name' => 'css',
					'group' => __('Design options', DHVC_WOO_PAGE)
				)
			)
		));
		
		vc_map ( array (
			"name" 			=> __( "Account dashboard", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_dashboard",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
			'description' 	=> __('Account dashboard content.', DHVC_WOO_PAGE),
			"params" => array (
				array (
					"type" 			=> "checkbox",
					'save_always'	=>true,
					"heading" 		=> __( "Show in Tab content", DHVC_WOO_PAGE ),
					"param_name" 	=> "show_in_tab",
					'value'			=>'yes',
					"description" 	=> __( "Check if you in Tab content.", DHVC_WOO_PAGE )
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
			"name" 			=> __( "Account orders", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_orders",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
			"params" => array (
				array (
					"type" 			=> "checkbox",
					'save_always'	=>true,
					"heading" 		=> __( "Show in Tab content", DHVC_WOO_PAGE ),
					"param_name" 	=> "show_in_tab",
					'value'			=>'yes',
					"description" 	=> __( "Check if you in Tab content.", DHVC_WOO_PAGE )
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
			"name" 			=> __( "Account downloads", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_downloads",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
			"params" => array (
				array (
					"type" 			=> "checkbox",
					'save_always'	=>true,
					"heading" 		=> __( "Show in Tab content", DHVC_WOO_PAGE ),
					"param_name" 	=> "show_in_tab",
					'value'			=>'yes',
					"description" 	=> __( "Check if you in Tab content.", DHVC_WOO_PAGE )
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
			"name" 			=> __( "Account address", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_address",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
			"params" => array (
				array (
					"type" 			=> "checkbox",
					'save_always'	=>true,
					"heading" 		=> __( "Show in Tab content", DHVC_WOO_PAGE ),
					"param_name" 	=> "show_in_tab",
					'value'			=>'yes',
					"description" 	=> __( "Check if you in Tab content.", DHVC_WOO_PAGE )
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
		$support_payment_methods = false;
		foreach ( WC()->payment_gateways->get_available_payment_gateways() as $gateway ) {
			if ( $gateway->supports( 'add_payment_method' ) || $gateway->supports( 'tokenization' ) ) {
				$support_payment_methods = true;
				break;
			}
		}
		if($support_payment_methods){
			vc_map ( array (
				"name" 			=> __( "Account payment methods", DHVC_WOO_PAGE ),
				"base" 			=> "dhvc_woo_account_payment_methods",
				"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
				"icon" 			=> "icon-dhvc-woo-account",
				"params" => array (
					array (
						"type" 			=> "checkbox",
						'save_always'	=>true,
						"heading" 		=> __( "Show in Tab content", DHVC_WOO_PAGE ),
						"param_name" 	=> "show_in_tab",
						'value'			=>'yes',
						"description" 	=> __( "Check if you in Tab content.", DHVC_WOO_PAGE )
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
		
		vc_map ( array (
			"name" 			=> __( "Account details", DHVC_WOO_PAGE ),
			"base" 			=> "dhvc_woo_account_details",
			"category" 		=> __( "WC Page Templates", DHVC_WOO_PAGE ),
			"icon" 			=> "icon-dhvc-woo-account",
			"params" => array (
				array (
					"type" 			=> "checkbox",
					'save_always'	=>true,
					"heading" 		=> __( "Show in Tab content", DHVC_WOO_PAGE ),
					"param_name" 	=> "show_in_tab",
					'value'			=>'yes',
					"description" 	=> __( "Check if you in Tab content.", DHVC_WOO_PAGE )
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

new DHVC_Woo_Page_Shortcode_Account();

if (class_exists('WPBakeryShortCodesContainer')) {
	class WPBakeryShortCode_dhvc_woo_account_dashboard_container extends WPBakeryShortCodesContainer{}
}