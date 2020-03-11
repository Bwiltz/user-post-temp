(function ( $ ) {
	"use strict"; // jshint ;_;
	var DHVCWooProductGallery = function($target){
		this.$target = $target;
		this.$scope = $('.dhvc-woocommerce-page');
		this.$images = $( '.woocommerce-product-gallery__image', $target );
		this.gallery_images = $('.dhvc-woo-product-gallery__images');
		this.gallery_thumbnails = $('.dhvc-woo-product-gallery__thumbnails');
		
		this.zoom_enabled       = 'yes'===this.gallery_images.data('zoom') && $.isFunction( $.fn.zoom );
		this.photoswipe_enabled = 'yes'===this.gallery_images.data('lightbox') && typeof PhotoSwipe !== 'undefined';

		this.initSlick 			= this.initSlick.bind(this);
		this.initZoom           = this.initZoom.bind( this );
		this.initZoomForTarget  = this.initZoomForTarget.bind( this );
		this.initPhotoswipe     = this.initPhotoswipe.bind( this );
		this.getGalleryItems    = this.getGalleryItems.bind( this );
		this.openPhotoswipe     = this.openPhotoswipe.bind( this );

		this.initSlick();
	}
	
	DHVCWooProductGallery.prototype.initSlick = function(){
		var self = this,
			$form = $('.variations_form',this.$scope);

		if(this.photoswipe_enabled){
			self.initPhotoswipe();
		}
		if(this.zoom_enabled){
			self.initZoom();
			this.$target.on( 'woocommerce_gallery_init_zoom', self.initZoom );
		}
		
		if(this.$target.hasClass('with-thumbnails')){
			this.gallery_images.slick({
				arrows: true,
	            dots: false,
	            draggable: true,
	            fade: true,
	            slidesToShow: 1,
	            slidesToScroll: 1,
	            infinite: false,
	            speed: 300,
	            adaptiveHeight: false,
	            asNavFor: '.dhvc-woo-product-gallery__thumbnails',
	            responsive: [{
	                breakpoint: 768,
	                settings: {
	                    fade: false
	                }
	            }]
			});
		
			if(this.zoom_enabled){
				this.gallery_images.on('afterChange', function(event, slick, currentSlide, nextSlide) {
	                var $image = $('.woocommerce-product-gallery__image', this.gallery_images);
	                self.initZoomForTarget($image.eq(currentSlide));
	            });
			}
		
		
			this.gallery_thumbnails.slick({
				arrows: this.gallery_thumbnails.data('arrows'),
	            prevArrow: '<span class="slick-arrow__prev"><i class="slick-arrow__prev__icon" aria-hidden="true"></i></span>',
	            nextArrow: '<span class="slick-arrow__next"><i class="slick-arrow__next__icon" aria-hidden="true"></i></span>',
	            dots: false,
	            centerMode: false,
	            centerPadding: 0,
	            customPaging: 5,
	            draggable: false,
	            swipeToSlide: false,
	            slidesToShow: 4,
	            slidesToScroll: 1,
	            infinite: false,
	            speed: 300,
	            vertical: this.gallery_thumbnails.data('vertical'),
	            focusOnSelect: true,
	            verticalSwiping: true,
	            asNavFor: '.dhvc-woo-product-gallery__images',
	            responsive: [{
	                breakpoint: 768,
	                slidesToShow:4,
	                settings: {
	                    vertical: false,
	                    arrows: this.gallery_thumbnails.data('mobile-arrows')
	                }
	            }]
			});
			
		    var $gallery_img = this.gallery_thumbnails.find('[data-slick-index=0] img'),
		    	$gallery_img_src = $gallery_img.attr('src');
		    
			if($form.length){
				$form.on('show_variation', function(event, variation, purchasable) {
		             if (variation && variation.image && variation.image.src && variation.image.src.length > 1) {
		                 $form.wc_variations_image_update( variation )
		            	 self.gallery_thumbnails.find('img[src="' + $gallery_img_src + '"]').each(function() {
		                     $(this).wc_set_variation_attr('src', variation.image.gallery_thumbnail_src);
		                     $(this).attr('data-changesrc', true);
		                 });
		                 $gallery_img.wc_set_variation_attr('src', variation.image.gallery_thumbnail_src);
		             }
		        }).on('reset_image', function() {
		             $form.wc_variations_image_update( false );
		             $gallery_img.wc_reset_variation_attr('src');
		             self.gallery_thumbnails.find('img[data-changesrc]').each(function() {
		                 $(this).wc_reset_variation_attr('src');
		                 $(this).attr('data-changesrc', false);
		             });
		        });
			}
			this.$target.on('woocommerce_gallery_reset_slide_position', function() {
				self.gallery_images.slick('slickGoTo', 0);
            });
		}else{
			if($form.length){
				$form.on('show_variation', function(event, variation, purchasable) {
		             if (variation && variation.image && variation.image.src && variation.image.src.length > 1) {
		            	 $form.wc_variations_image_update( variation )
		             }
		        }).on('reset_image', function() {
		             $form.wc_variations_image_update( false );
		        });
			}
		}
	}
	
	DHVCWooProductGallery.prototype.initZoom = function() {
		this.initZoomForTarget( this.$images.first() );
	};
	
	DHVCWooProductGallery.prototype.initZoomForTarget = function( zoomTarget ) {
		if ( ! this.zoom_enabled ) {
			return false;
		}

		var galleryWidth = this.$target.width(),
			zoomEnabled  = false;

		$( zoomTarget ).each( function( index, target ) {
			var image = $( target ).find( 'img' );

			if ( image.data( 'large_image_width' ) > galleryWidth ) {
				zoomEnabled = true;
				return false;
			}
		} );

		// But only zoom if the img is larger than its container.
		if ( zoomEnabled ) {
			var zoom_options = $.extend( {
				touch: false
			}, wc_single_product_params.zoom_options );

			if ( 'ontouchstart' in document.documentElement ) {
				zoom_options.on = 'click';
			}

			zoomTarget.trigger( 'zoom.destroy' );
			zoomTarget.zoom( zoom_options );

			setTimeout( function() {
				if ( zoomTarget.find(':hover').length ) {
					zoomTarget.trigger( 'mouseover' );
				}
			}, 100 );
		}
	};
	
	DHVCWooProductGallery.prototype.initPhotoswipe = function() {
		if ( this.zoom_enabled && this.$images.length > 0 ) {
			this.$target.prepend( '<a href="#" class="woocommerce-product-gallery__trigger"></a>' );
			this.$target.on( 'click', '.woocommerce-product-gallery__trigger', this.openPhotoswipe );
			this.$target.on( 'click', '.woocommerce-product-gallery__image a', function( e ) {
				e.preventDefault();
			});
		} else {
			this.$target.on( 'click', '.woocommerce-product-gallery__image a', this.openPhotoswipe );
		}
	}
	
	DHVCWooProductGallery.prototype.getGalleryItems = function() {
		var $slides = this.$images,
			items   = [];

		if ( $slides.length > 0 ) {
			$slides.each( function( i, el ) {
				var img = $( el ).find( 'img' );

				if ( img.length ) {
					var large_image_src = img.attr( 'data-large_image' ),
						large_image_w   = img.attr( 'data-large_image_width' ),
						large_image_h   = img.attr( 'data-large_image_height' ),
						item            = {
							src  : large_image_src,
							w    : large_image_w,
							h    : large_image_h,
							title: img.attr( 'data-caption' ) ? img.attr( 'data-caption' ) : img.attr( 'title' )
						};
					items.push( item );
				}
			} );
		}

		return items;
	};
	
	/**
	 * Open photoswipe modal.
	 */
	DHVCWooProductGallery.prototype.openPhotoswipe = function( e ) {
		e.preventDefault();

		var pswpElement = $( '.pswp' )[0],
			items       = this.getGalleryItems(),
			eventTarget = $( e.target ),
			clicked;

		if ( eventTarget.is( '.woocommerce-product-gallery__trigger' ) || eventTarget.is( '.woocommerce-product-gallery__trigger img' ) ) {
			clicked = this.$target.find( '.slick-active' );
		} else {
			clicked = eventTarget.closest( '.woocommerce-product-gallery__image' );
		}
		var options = $.extend( {
			index: $( clicked ).index(),
			addCaptionHTMLFn: function( item, captionEl ) {
				if ( ! item.title ) {
					captionEl.children[0].textContent = '';
					return false;
				}
				captionEl.children[0].textContent = item.title;
				return true;
			}
		}, wc_single_product_params.photoswipe_options );

		// Initializes and opens PhotoSwipe.
		var photoswipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options );
		photoswipe.init();
	};
	
	$.fn.dhvc_woo_product_gallery = function() {
		new DHVCWooProductGallery(this);
		return this;
	};
	
	$(document).ready(function(){
		$('.dhvc-woo-product-gallery').dhvc_woo_product_gallery();
	})
})( window.jQuery );