<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DHVC_Woo_Page_Shortcode {
	
	private $_shortcode_added = false;
	
	private $_single_product_display_attributes = array();
	
	public function __construct() {
		$this->init_hooks();
	}
	
	public function init_hooks(){
		
		if($this->_shortcode_added){
			return;
		}
		add_action( 'vc_load_shortcode', array($this,'add_shortcode' ));
		add_action( 'template_redirect', array($this, 'add_shortcode' ),9);
		add_action( 'dhvc_woocommerce_page_before_single_product_shortcode_content', array($this, 'add_shortcode' ),9);
		$this->_shortcode_added = true;
	}

	public function add_shortcode(){
		global $post;
		foreach ( dhvc_woo_product_page_single_shortcodes() as $shortcode => $function ) {
			if('product'===get_post_type($post)){
				add_shortcode($shortcode , array($this,$function));
			}else{
				add_shortcode($shortcode , array($this,'shortcode_error2'));
			}
		}
		
		if(defined( 'YITH_YWZM_DIR' )){
			remove_shortcode('dhvc_woo_product_page_images');
			add_shortcode ( 'dhvc_woo_product_page_images',array($this,'dhvc_woo_product_page_images_shortcode_custom') );
		}
		
		foreach ( dhvc_woo_product_page_wc_shortcodes() as $shortcode => $function ) {
			add_shortcode( $shortcode , array( $this, $function ) );
		}
		
		do_action('dhvc_woocommerce_page_after_add_shortcode');
	}
	
	public function shortcode_error($atts='',$content='',$tag=''){
		 return '<em style="color:red;display:block">Use shortcode "'.$tag.'" is wrong (Please view Product after assigning Custom Template), to use plugin please see <a target="_blank" href="https://www.youtube.com/watch?v=DhqOQdR7K_8">Video</a><br><br></em>';
	}
	
	public function shortcode_error2($atts='',$content='',$tag=''){
		return '<em style="display: block; color: rgb(51, 51, 51); font-weight: bold; white-space: pre-wrap;">Shortcode "'.ucwords(str_replace(array('dhvc_woo','_'), array('Single ',' '), $tag)).'". <i style="font-size: inherit; color: rgb(255, 0, 0); font-weight: normal;">Please view Product after assigning Custom Template</i></em>';
	}
	
	public function dhvc_woo_product_page_fpd($atts){
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		echo '<style type="text/css">#fpd-start-customizing-button~#fpd-start-customizing-button{display:none}</style>';
		$FPD_Frontend_Product = new FPD_Frontend_Product;
		$FPD_Frontend_Product->add_product_designer();

		return !empty($el_class) ? '<div class="dhvc-woo-product-page-fpd '.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_images_shortcode_custom($atts, $content = null){
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		$wc_get_template = function_exists( 'wc_get_template' ) ? 'wc_get_template' : 'woocommerce_get_template';
		$wc_get_template( 'single-product/product-image-magnifier.php', array(), '', YITH_YWZM_DIR . 'templates/' );
	
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	protected function _shortcode_atts($defaults = array(), $atts){
		return shortcode_atts ( $defaults, $atts );
	}
	
	protected function _get_vc_shortcode_custom_css_class($param_value, $prefix = ' ' ){
		return dhvc_woo_page_get_shortcode_custom_css_class($param_value,$prefix);
	}
	
	public function dhvc_woo_product_page_acf_field_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'field' 	=> '',
			'el_class' 	=> '',
			'css'		=> '',
		), $atts ) );
		if (empty ( $field )) {
			return '';
		}
		
		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		echo '<div class="dhvc_woo_product_page_acf_field ' . $el_class . '">';
		//the_field ( $field );
		$value = get_field($field);
		//filter to custom display
		$value = apply_filters('dhvc_woo_product_page_acf_field', $value, $field);
		if( is_array($value) )
		{
			$value = @implode(', ',$value);
		}
			
		echo do_shortcode($value);
			
		echo '</div>';
		return ob_get_clean ();
	}
	
	
	public function dhvc_woo_product_page_images_shortcode($atts, $content = null) {
		global $product;
		
		extract ( $this->_shortcode_atts ( array (
			'slider_type'		=> 'default',
			'enable_zoom'		=> '',
			'enable_lightbox'	=> '',
			'el_class' 			=> '' ,
			'css'				=> '',
		), $atts ) );
		
		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if('default'!==$slider_type){
			
			wp_enqueue_script('dhvc-woo-product-single',DHVC_WOO_PAGE_URL.'/assets/js/single-product.js',array('jquery','slick'),DHVC_WOO_PAGE_VERSION,true);
			
			if('yes'===$enable_zoom && !current_theme_supports( 'wc-product-gallery-zoom' )){
				wp_enqueue_script( 'zoom', plugins_url( 'assets/js/zoom/jquery.zoom.min.js', WC_PLUGIN_FILE ),array( 'jquery' ),'1.7.21',true);
			}
			if('yes'===$enable_lightbox && !current_theme_supports( 'wc-product-gallery-lightbox' )){
				wp_register_script( 'photoswipe', plugins_url( 'assets/js/photoswipe/photoswipe.min.js', WC_PLUGIN_FILE ),array(),'4.1.1',true);
				wp_enqueue_script( 'photoswipe-ui-default', plugins_url( 'assets/js/photoswipe/photoswipe-ui-default.min.js', WC_PLUGIN_FILE ),array( 'photoswipe' ),'4.1.1',true);
				
				wp_register_style( 'photoswipe', plugins_url( 'assets/css/photoswipe/photoswipe.css', WC_PLUGIN_FILE ),array(),'4.1.1');
				wp_enqueue_style( 'photoswipe-default-skin', plugins_url( 'assets/css/photoswipe/default-skin/default-skin.css', WC_PLUGIN_FILE ),array( 'photoswipe' ),'4.1.1');				
				add_action( 'wp_footer', array($this,'_photoswipe_template'),100);
			}

			$gallery_class 		= 'no-thumbnails';
			$thumbnail_size    	= 'shop_thumbnail';
			$post_thumbnail_id  = $product->get_image_id();
			$gallery_html = $thumbnail_html = $main_thumbnail_html = '';
			
			if ( $product->get_image_id() ) {
				$post_thumbnail_url = wp_get_attachment_image_url( $post_thumbnail_id, $thumbnail_size );
				$main_thumbnail_html .= '<div class="woocommerce-product-gallery__thumbnail"><div class="slick-image--border">'.sprintf( '<img src="%s" data-o_src="%s" />', $post_thumbnail_url, $post_thumbnail_url ).'</div></div>';
				$gallery_html = wc_get_gallery_image_html( $post_thumbnail_id, true );
			} else {
				$gallery_html  = '<div class="woocommerce-product-gallery__image--placeholder">';
				$gallery_html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
				$gallery_html .= '</div>';
			}
			
			$gallery_html = apply_filters( 'woocommerce_single_product_image_thumbnail_html', $gallery_html, $post_thumbnail_id ); 
			$attachment_ids = $product->get_gallery_image_ids();
			
			if ( $attachment_ids && $product->get_image_id() ) {
				$gallery_class = 'with-thumbnails';
				$thumbnail_html .= $main_thumbnail_html;
				foreach ( $attachment_ids as $attachment_id ) {
					$post_thumbnail_url = wp_get_attachment_image_url( $attachment_id, $thumbnail_size );
					$thumbnail_html .= '<div class="woocommerce-product-gallery__thumbnail"><div class="slick-image--border">'.sprintf( '<img src="%s" data-o_src="%s" />', $post_thumbnail_url, $post_thumbnail_url ).'</div></div>';
					
					$gallery_html .= apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id ), $attachment_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
				}
				
				$thumbnail_html = '<div class="dhvc-woo-product-gallery__thumbnails" data-vertical="'.('vertical'===$slider_type ? 'true' : 'false').'" data-arrows="true" data-mobile-arrows="false">'.$thumbnail_html.'</div>';
			}
			
			?>
			<div class="dhvc-woo-product-gallery <?php echo esc_attr($gallery_class)?> is-<?php echo esc_attr($slider_type)?>">
				<div  data-zoom="<?php echo esc_attr($enable_zoom) ?>" data-lightbox="<?php echo esc_attr($enable_lightbox)?>"  class="images dhvc-woo-product-gallery__images">
					<?php echo $gallery_html?>
				</div>
				<?php echo $thumbnail_html?>
			</div>
			<?php 
		}else{
			if(class_exists('JCKWooThumbs')){
				$JCKWooThumbs = new JCKWooThumbs;
				$JCKWooThumbs->show_product_images();
			}else if(class_exists('WC_Product_Gallery_slider')){
				$enabled = dhvc_woo_page_get_option( 'woocommerce_product_gallery_slider_enabled' );
				$enabled_for_post   = get_post_meta( $product->get_id(), '_woocommerce_product_gallery_slider_enabled', true );
			
				if ( ( $enabled == 'yes' && $enabled_for_post !== 'no' ) || ( $enabled == 'no' && $enabled_for_post == 'yes' ) ) {
						WC_Product_Gallery_slider::setup_scripts_styles();
						WC_Product_Gallery_slider::show_product_gallery();
				}
			}else{
				woocommerce_show_product_sale_flash();
				woocommerce_show_product_images ();
			}
		}
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function _photoswipe_template(){
		wc_get_template( 'single-product/photoswipe.php' );
	}
	
	protected function _get_custom_styles($google_fonts, $font_container, $custom_fonts){
		
		$font_container_obj = new Vc_Font_Container();
		$google_fonts_obj = new Vc_Google_Fonts();
		$font_container_field_settings = isset( $font_container_field['settings'], $font_container_field['settings']['fields'] ) ? $font_container_field['settings']['fields'] : array();
		$google_fonts_field_settings = isset( $google_fonts_field['settings'], $google_fonts_field['settings']['fields'] ) ? $google_fonts_field['settings']['fields'] : array();
		$font_container_data = $font_container_obj->_vc_font_container_parse_attributes( $font_container_field_settings, $font_container );
		$google_fonts_data = strlen( $google_fonts ) > 0 ? $google_fonts_obj->_vc_google_fonts_parse_attributes( $google_fonts_field_settings, $google_fonts ) : '';
		
		$styles = array();
		if ( ! empty( $font_container_data ) && isset( $font_container_data['values'] ) ) {
			foreach ( $font_container_data['values'] as $key => $value ) {
				if ( 'tag' !== $key && strlen( $value ) ) {
					if ( preg_match( '/description/', $key ) ) {
						continue;
					}
					if ( 'font_size' === $key || 'line_height' === $key ) {
						$value = preg_replace( '/\s+/', '', $value );
					}
					if ( 'font_size' === $key ) {
						$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
						// allowed metrics: http://www.w3schools.com/cssref/css_units.asp
						$regexr = preg_match( $pattern, $value, $matches );
						$value = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
						$unit = isset( $matches[2] ) ? $matches[2] : 'px';
						$value = $value . $unit;
					}
					if ( strlen( $value ) > 0 ) {
						$styles[] = str_replace( '_', '-', $key ) . ': ' . $value;
					}
				}
			}
		}
		if (!empty($custom_fonts) && ! empty( $google_fonts_data ) && isset( $google_fonts_data['values'], $google_fonts_data['values']['font_family'], $google_fonts_data['values']['font_style'] ) ) {
			$google_fonts_family = explode( ':', $google_fonts_data['values']['font_family'] );
			$styles[] = 'font-family:' . $google_fonts_family[0];
			$google_fonts_styles = explode( ':', $google_fonts_data['values']['font_style'] );
			$styles[] = 'font-weight:' . $google_fonts_styles[1];
			$styles[] = 'font-style:' . $google_fonts_styles[2];
			
			$google_fonts_subsets = get_option( 'wpb_js_google_fonts_subsets' );
			if ( is_array( $google_fonts_subsets ) && ! empty( $google_fonts_subsets ) ) {
				$subsets = '&subset=' . implode( ',', $google_fonts_subsets );
			} else {
				$subsets = '';
			}
			
			wp_enqueue_style( 'vc_google_fonts_' . vc_build_safe_css_class( $google_fonts_data['values']['font_family'] ), 'https://fonts.googleapis.com/css?family=' . $google_fonts_data['values']['font_family'] . $subsets );
				
		}
		
		return $styles;
	}
	
	public function dhvc_woo_product_page_title_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'custom_styles'=>'',
			'custom_fonts'=>'',
			'google_fonts'=>'',
			'font_container'=>'',
			'el_class' => '',
			'css'		=> '',
		), $atts ) );
		
		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if('yes'===$custom_styles){
			$styles=$this->_get_custom_styles($google_fonts, $font_container, $custom_fonts);
			
			if ( ! empty( $styles ) ) {
				$style = 'style="' . esc_attr( implode( ';', $styles ) ) . '"';
			} else {
				$style = '';
			}
			
			?>
			<h1 class="product_title entry-title" <?php echo $style ?>>
				<?php the_title()?>
			</h1>
			<?php 
		}else{
			if(dhvc_woo_product_page_is_jupiter_theme()){
				?>
				<h1 class="single_product_title entry-title"><?php the_title(); ?></h1>
				<?php
			}else{
				woocommerce_template_single_title ();
			}
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_rating_shortcode($atts, $content = null) {
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<?php
			$count   = $product->get_rating_count();
			$average = $product->get_average_rating();
		
			if ( $count > 0 ) : ?>
			
			<div class="woocommerce-product-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
				<div class="star-rating" title="<?php printf( __( 'Rated %s out of 5', 'woocommerce' ), $average ); ?>">
					<span style="width:<?php echo ( ( $average / 5 ) * 100 ); ?>%">
						<strong itemprop="ratingValue" class="rating"><?php echo esc_html( $average ); ?></strong> <?php _e( 'out of 5', 'woocommerce' ); ?>
					</span>
				</div>
				<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $count, 'woocommerce' ), '<span itemprop="ratingCount" class="count">' . $count . '</span>' ); ?>)</a>
			</div>
			<?php
			endif;
		}else{
			woocommerce_template_single_rating();
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_price_shortcode($atts, $content = null) {
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'custom_styles'=>'',
			'custom_fonts'=>'',
			'google_fonts'=>'',
			'font_container'=>'',
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if('yes'===$custom_styles){
			$styles=$this->_get_custom_styles($google_fonts, $font_container, $custom_fonts);
				
			if ( ! empty( $styles ) ) {
				$style = 'style="' . esc_attr( implode( ';', $styles ) ) . '"';
			} else {
				$style = '';
			}
				
			?>
			<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) );?>" <?php echo $style ?>><?php echo $product->get_price_html(); ?></p>		
			<?php 
		}else{
			if(dhvc_woo_product_page_is_jupiter_theme()){
				?>
				<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		
					<div itemprop="price" class="mk-single-price"><?php echo $product->get_price_html(); ?></div>
		
					<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
					<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />
		
				</div>
				<?php
			}else{
				woocommerce_template_single_price ();
			}	
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_sku_shortcode($atts, $content = null){
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'label'		=> '',
			'el_class' 	=> '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
	
		if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) :
		?>
		<div class="product_meta__sku">
			<span class="sku_wrapper">
			<?php echo $label ?> 
			<span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>
		</div>
		<?php
		
		endif;
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	/**
	 * 
	 * @param array $atts
	 * @param string $content
	 * @param WC_Product $product
	 * @return string
	 */
	public function dhvc_woo_product_page_term_shortcode($atts, $content = null){
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'label'		=> '',
			'taxonomy'	=> '',
			'display'	=> 'name',
			'el_class' 	=> '',
			'css'		=> '',
		), $atts ) );
		
		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		$terms = get_the_terms( $product->get_id(), $taxonomy );
		$term_list = array();
		
		$term_key= 'thumbnail_id';
		
		ob_start ();
		?>
		<div class="product-term product-term--display-<?php echo esc_attr($display)?>">
			<?php echo !empty($label) ? '<span class="product-term__label">'.$label.'</span>' : '' ?>
			<?php 
			foreach ($terms as $term):
				$term_html = '<a href="'.esc_url( get_term_link( $term, $taxonomy ) ).'">';
					
					if('name'!==$display && $thumbnail_id = get_term_meta( $term->term_id, $term_key, true )):
						$thumbnail_id = apply_filters('dhvc_woo_product_page_single_term_value', $thumbnail_id, $term, $taxonomy);
						$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'shop_thumbnail' );
						$image        = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size );
						$image        = $image[0];
						$image = str_replace( ' ', '%20', $image );
						$term_html .='<span class="product-term__image"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $term->name ) . '"/></span>';
					endif;
					
					if('thumbnail'!==$display):
						$term_html .= '<span class="product-term__name">'.$term->name.'</span>';
					endif;
			
				$term_html .= '</a>';
				$term_list[] = $term_html;
			endforeach;
			if('name'===$display)
				echo implode(', ', $term_list);
			else 
				echo '<div class="product-term__content">'.implode('', $term_list).'</div>';
			?>
		</div>
		<?php 
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_excerpt_shortcode($atts, $content = null) {
		global $post;
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if(dhvc_woo_product_page_is_jupiter_theme()){
			
			?>
			<div itemprop="description">
				<?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
			</div>
			<?php
		}else{
			woocommerce_template_single_excerpt();
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_description_shortcode($atts, $content = null){
		global $post;
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		
		if(defined('DHVC_WOO_PRODUCT_PAGE_IS_FRONTEND_EDITOR')){
			$content = $post->post_content;
			
			/**
			 * Filters the post content.
			 *
			 * @since 0.71
			 *
			 * @param string $content Content of the current post.
			*/
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			echo $content;
		}else{
			the_content();
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_additional_information($atts, $content = null){
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'attributes' => '',
			'el_class' 	 => '',
			'css'		 => '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		if(!empty($attributes)){
			$this->_single_product_display_attributes = array_map('trim',explode(',', $attributes));
			add_filter('woocommerce_display_product_attributes', array($this,'_filter_single_product_display_attributes'),11,2);
		}
		if ( $product && ( $product->has_attributes() || apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() ) ) ) {
			wc_get_template( 'single-product/tabs/additional-information.php' );
		}
		
		remove_filter('woocommerce_display_product_attributes', array($this,'_filter_single_product_display_attributes'),11);

		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function _filter_single_product_display_attributes( $product_attributes, $product  ){
		$new_attributes = array();
		foreach ($product_attributes as $attribute=>$product_attribute){
			$attribute_key = 'weight'=== $attribute || 'dimensions' === $attribute ? $attribute: str_replace('attribute_pa_', '', $attribute);
			if(in_array($attribute_key, $this->_single_product_display_attributes)){
				$new_attributes[$attribute] = $product_attribute;
			}
		}
		return $new_attributes;
	}
	
	public function dhvc_woo_product_page_add_to_cart_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		woocommerce_template_single_add_to_cart ();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_meta_shortcode($atts, $content = null) {
		global $product;
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '' ,
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<div class="mk_product_meta">
	
				<?php do_action( 'woocommerce_product_meta_start' ); ?>
	
				<?php
				
				 if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
	
					<span class="sku_wrapper"><?php _e( 'SKU:', 'woocommerce' ); ?> <span class="sku" itemprop="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : __( 'N/A', 'woocommerce' ); ?></span>.</span>
	
				<?php endif; ?>
	
				<?php echo $product->get_categories( ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '.</span>' ); ?>
	
				<?php echo $product->get_tags( ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '.</span>' ); ?>
	
				<?php do_action( 'woocommerce_product_meta_end' ); ?>
	
			</div>
			<?php
		}else{
			woocommerce_template_single_meta ();
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_sharing_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if(dhvc_woo_product_page_is_jupiter_theme()){
			?>
			<ul class="woocommerce-social-share">
				<li><a class="facebook-share" data-title="<?php the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-jupiter-icon-simple-facebook"></i></a></li>
				<li><a class="twitter-share" data-title="<?php the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-moon-twitter"></i></a></li>
				<li><a class="googleplus-share" data-title="<?php the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-jupiter-icon-simple-googleplus"></i></a></li>
				<li><a class="pinterest-share" data-image="<?php echo $image_src_array[0]; ?>" data-title="<?php echo get_the_title();?>" data-url="<?php echo get_permalink(); ?>" href="#"><i class="mk-jupiter-icon-simple-pinterest"></i></a></li>
			</ul>
			<?php
		}else{
			woocommerce_template_single_sharing ();
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_data_tabs_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		woocommerce_output_product_data_tabs ();
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_reviews_shortcode($atts, $content = null){
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		if(comments_open() ){
			comments_template();
		}
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	public function dhvc_woo_product_page_related_products_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'posts_per_page'=>4,
			'columns'=>4,
			'orderby'=>'date',
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		woocommerce_related_products($atts);
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_custom_field_shortcode($atts, $content = null){
		global $post;
		extract ( $this->_shortcode_atts ( array (
			'key'=>'',
			'label'=>'',
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		

		$css_class = 'dhvc_woo_product-meta-field-' . $key. ( strlen( $el_class ) ? ' ' . $el_class : '' );
		$label_html = '';
		if ( strlen( $label ) ) {
			$label_html = '<span class="dhvc_woo_product-meta-label">' . esc_html( $label ) . '</span>';
		}
		ob_start ();
		if ( !empty( $key ) && $value = get_post_meta($post->ID,$key,true) ) :  
		?>
			<div class="dhvc_woo_product_page_custom_field <?php echo esc_attr( $css_class ) ?>">
				<?php echo $label_html ?>
				<?php echo apply_filters('dhvc_woo_product_page_custom_field_value',$value,$key,$post);?>
			</div>
		<?php 
		endif;
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_upsell_products_shortcode($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'posts_per_page'=>4,
			'columns'=>4,
			'orderby'=>'date',
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		ob_start ();
		
		woocommerce_upsell_display ( $posts_per_page, $columns, $orderby);
		
		return !empty($el_class) ? '<div class="'.$el_class.'">'.ob_get_clean().'</div>' : ob_get_clean();
	}
	
	public function dhvc_woo_product_page_wishlist_shortcode($atts, $content = null){
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = '';
		$output .= '<div class="dhvc-woocommerce-page-wishlist ' . $el_class . '">';
		$output .= do_shortcode('[yith_wcwl_add_to_wishlist]');
		$output .= '</div>';
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	
	public function product_category($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::product_category($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function product_categories($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::product_categories($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function products($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::products($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function recent_products($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);

		$output = WC_Shortcodes::recent_products($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function sale_products($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::sale_products($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function best_selling_products($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::best_selling_products($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function top_rated_products($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::top_rated_products($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function featured_products($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::featured_products($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function product_attribute($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::product_attribute($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function shop_messages($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::shop_messages($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function order_tracking($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::order_tracking($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function cart($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = WC_Shortcodes::cart($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	
	public function breadcrumb($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		
		$output = woocommerce_breadcrumb();
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function checkout($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		$output = WC_Shortcodes::checkout($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
	public function my_account($atts, $content = null) {
		extract ( $this->_shortcode_atts ( array (
			'el_class' => '',
			'css'		=> '',
		), $atts ) );

		$el_class .= $this->_get_vc_shortcode_custom_css_class($css);
		$output = WC_Shortcodes::my_account($atts);
		return !empty($el_class) ? '<div class="'.$el_class.'">'.$output.'</div>' : $output;
	}
}
new DHVC_Woo_Page_Shortcode;