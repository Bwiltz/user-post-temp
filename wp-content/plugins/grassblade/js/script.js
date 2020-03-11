var GB = [];
	function showHideOptional(id) {
		var table = document.getElementById(id);
		var display_status = table.style.display;

		
		if(display_status == "none")
		{
			table.style.display = "block";
		}
		else
			table.style.display = "none";
	}
	function grassblade_show_lightbox(id, src, completion_data, width, height, aspect) {

		if(document.getElementById("grassblade_lightbox") == null)
			jQuery("body").append("<div id='grassblade_lightbox'></div>");
		
		window.grassblade_lightbox = {};
		window.grassblade_lightbox[id] = {id: id, src:src, width:width, height:height, aspect:aspect};

		var sizes = grassblade_lightbox_get_sizes(window.grassblade_lightbox[id]);
		src += (src.search(/[?]/) < 0)? "?":"&";
		src += "h=" + encodeURI(sizes.inner_height) + "&w=" + encodeURI(sizes.inner_width);
		html = "<div class='grassblade_lightbox_overlay' onClick='return grassblade_hide_lightbox("+id+");'></div><div id='" + id + "' class='grassblade_lightbox'  style='top:" + sizes.top + ";bottom:" + sizes.top + ";left:" + sizes.left + ";right:" + sizes.left + ";width:" + sizes.width + "; height:" + sizes.height + ";'>" + 
					"<div class='grassblade_close'><a class='grassblade_close_btn' href='#' onClick='return grassblade_hide_lightbox("+completion_data+");'>X</a></div>" +
					"<iframe class='grassblade_lightbox_iframe' data-completion='" + completion_data + "' frameBorder='0' src='" + src + "' style='height: 100%; width: 100%;position: absolute; left: 0;top: 0;border: 0;' webkitallowfullscreen mozallowfullscreen allowfullscreen allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' onLoad='grassblade_get_lightbox_iframe();'></iframe>" +
				"</div>";
		
		jQuery("#grassblade_lightbox").html(html);
		jQuery("#grassblade_lightbox").show();
	}
	function grassblade_lightbox_get_sizes(options) {
		var width = options.width;
		var height = options.height;
		var aspect = options.aspect;

        var window_width = Math.ceil(jQuery(window).width() * 1) - 22; //Available width and height is less by 22px, because there is 11px border.  onClick='return grassblade_hide_lightbox();'
        var window_height = Math.ceil(jQuery(window).height() * 1) - 22;


        if(aspect > 0) 
        {
	        if(width.indexOf("%") > 0)
	        var width_number = Math.ceil(window_width * parseFloat(width)/100);
	    	else
	    	var width_number = Math.ceil(parseFloat(width));

	    	var height_number = Math.ceil(parseFloat(width_number / aspect));

	    	if(width_number > window_width) {
	    		height_number = Math.ceil(window_width  * height_number / width_number);
	    		width_number = window_width;
	    	}

	    	if(height_number > window_height) {
	    		width_number = Math.ceil( window_height * width_number / height_number );
	    		height_number = window_height;
	    	}
        }
        else
        {
	        if(width.indexOf("%") > 0)
	        var width_number = Math.ceil(window_width * parseFloat(width)/100);
	    	else
	    	var width_number = Math.ceil(parseFloat(width));

	        if(height.indexOf("%") > 0)
	        var height_number = Math.ceil(window_height * parseFloat(height)/100);
	        else
	        var height_number = Math.ceil(parseFloat(height));
        }

        //console.log({window_width:window_width, window_height:window_height, width_number:width_number, height_number:height_number, width:width, height:height, aspect:aspect});

    	var top = ((window_height - height_number) / 2 );
    	var left = ((window_width - width_number) / 2 );
    	var top = top < 0? 0:top + "px";
    	var left = left < 0? 0:left + "px";
    	var h = (height_number + 22) + "px"; //Increase width and height by 22 for the border.
    	var w = (width_number + 22) + "px";
        //console.log({top:top, height:h, width:w});

    	return {top:top,left:left, height:h, width:w, inner_height: height_number, inner_width: width_number};
	}
	function grassblade_hide_lightbox(completion_data) {
		if(!jQuery("body").hasClass("gb-fullscreen")) {
			jQuery("#grassblade_lightbox").hide();
			jQuery("#grassblade_lightbox").html('');
		}
		if ( typeof gb_data != 'undefined' && gb_data.completion_tracking_enabled && gb_data.completion_type != 'hide_button' && gb_data.is_guest == '' && gb_data.lrs_exists && (typeof(completion_data) === 'object') && (completion_data.completion_tracking != false) && (completion_data.completion_type != 'hide_button')) {
			grassblade_content_completion_request(completion_data.content_id,completion_data.registration,2);
		}
		return false;
	} 

	function show_xapi_content_meta_box_change() {
		var show_xapi_content = jQuery("#show_xapi_content");
		if(show_xapi_content.length == 0)
			return;

		edit_link = jQuery('#grassblade_add_to_content_edit_link'); 
		if(show_xapi_content.val() > 0) {
			edit_link.show(); 
			jQuery("body").addClass("has_xapi_content");
		}
		else {
			jQuery("body").removeClass("has_xapi_content");
			edit_link.hide();
		}
			
		jQuery("#completion_tracking_enabled").hide();
		jQuery("#completion_tracking_disabled").hide();		

		if(jQuery("#show_xapi_content option:selected").attr("completion-tracking") == "1") {
			jQuery("#completion_tracking_enabled").show();
		}
		else if(jQuery("#show_xapi_content option:selected").attr("completion-tracking") == "")
		{
			jQuery("#completion_tracking_disabled").show();			
		}
	}

	jQuery(window).load(function() {

		if(jQuery("#show_xapi_content").length > 0) {
			jQuery("#show_xapi_content").change(function() {
				show_xapi_content_meta_box_change();
			});
			show_xapi_content_meta_box_change();
		}
		if(jQuery("#grassblade_xapi_content_form").length > 0)
			grassblade_xapi_content_edit_script();
		jQuery(".grassblade_field_group > div.grassblade_field_group_label").click(function() {
			//console.log(jQuery(this).parent().children("div.grassblade_field_group_fields").css("display"));
			if(jQuery(this).parent().children("div.grassblade_field_group_fields").css("display") != "none") {
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").addClass("dashicons-arrow-right-alt2");
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").removeClass("dashicons-arrow-down-alt2");
			}
			else
			{
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").removeClass("dashicons-arrow-right-alt2");
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").addClass("dashicons-arrow-down-alt2");	
			}
			jQuery(this).parent().children("div.grassblade_field_group_fields").slideToggle();
		});
		jQuery(".grassblade_field_group > div.grassblade_field_group_label").click();
		jQuery(".grassblade_field_group.default_open > div.grassblade_field_group_label").click();

		grassblade_xapi_content_autosize_content();

		jQuery(window).resize(function() {
	        if (document.fullscreenElement || 
	            document.mozFullScreenElement || 
	            document.webkitFullscreenElement || 
	            document.msFullscreenElement ) {
	          //Full Screen. ignore change
	        }
	        else
	        {
				grassblade_xapi_content_autosize_content();
		        var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);

				if(!iOS && window.grassblade_lightbox != undefined)
				jQuery.each(window.grassblade_lightbox, function(id,options) {
					//console.log(options);
					var sizes = grassblade_lightbox_get_sizes(options);
					//console.log(sizes);
					jQuery("#" + id).css("height", sizes.height);
					jQuery("#" + id).css("width", sizes.width);
					jQuery("#" + id).css("top", sizes.top);
					jQuery("#" + id).css("bottom", sizes.top);
					jQuery("#" + id).css("left", sizes.left);
					jQuery("#" + id).css("right", sizes.left);
				});
			}
		});

		jQuery(window).click(function(e) {
				//Added to fix: When closing full screen from Vimeo Video on Android. 
				//The touching the fullscreen(close) button also clicks the links below it in the main page. 
				//Preventing clicks during fullscreen will disable all click actions on main page but will allow clicks inside the iframe.
				if(jQuery('body').hasClass('gb-fullscreen')) {
	          		e.preventDefault();
	          		return;
				}
		});
	});
	
	function grassblade_xapi_content_autosize_content() {
		jQuery(".grassblade_iframe").each(function(i, element) {
			var width = parseInt(jQuery(element).width());
			var width_parent = jQuery(element).parent().width();

			if(jQuery(element).attr("height").indexOf("%") > 0) {
				var configured_height = parseInt(jQuery(element).attr("height"));
				var configured_width = parseInt(jQuery(element).attr("width"));
				var height = Math.ceil(width * configured_height / configured_width) + 1;
				jQuery(element).height(height);
				jQuery(element).attr("height", height);
			}

			var aspect = jQuery(element).data('aspect');
			if(aspect != undefined && aspect > 0) {
				var height = Math.ceil(width / aspect);
				jQuery(element).height(height);
				jQuery(element).attr("height", height);
			}

			/* Center width */
			var left_diff = (width_parent - width) * 1;
			if(left_diff > 4)
			{
				var left = Math.ceil(left_diff/2);
				jQuery(element).css('left', left);
			}

			var src = jQuery(element).attr('src');
			if(src == undefined) {
				if(height == undefined)
					height = jQuery(element).height();
				
				var src = jQuery(element).data('src');
				src += (src.search(/[?]/) < 0)? "?":"&";
				src += "h=" + height + "&w=" + width;
				jQuery(element).attr("src", src);
			}
			//console.log({height:height, width:width, left:left, width_parent:width_parent});
			//grassblade_script_to_iframe('grassblade_iframe');
		});
	}
	function grassblade_xapi_content_edit_script() {
		grassblade_enable_button_selector();

		jQuery("h2.gb-content-selector a").click(function() {
			jQuery("h2.gb-content-selector a").removeClass("nav-tab-active");
			jQuery(this).addClass("nav-tab-active");
			if(jQuery(this).hasClass("nav-tab-content-url")) {
				jQuery("#field-src").show();
				jQuery("#field-activity_id").show();
				jQuery("#field-xapi_content").hide();
				jQuery("#field-video, #field-video_hide_controls, #field-video_autoplay").hide();
				jQuery("#field-dropbox").hide();
				jQuery("#field-h5p_content").hide();
			}
			else if(jQuery(this).hasClass("nav-tab-video")) {
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").hide();
				jQuery("#field-xapi_content").show();
				jQuery("#field-video, #field-video_hide_controls, #field-video_autoplay").show();
				jQuery("#field-dropbox").hide();
				jQuery("#field-h5p_content").hide();
			}
			else if(jQuery(this).hasClass("nav-tab-h5p")) { 
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").hide();
				jQuery("#field-xapi_content").hide();
				jQuery("#field-video, #field-video_hide_controls, #field-video_autoplay").hide();
				jQuery("#field-dropbox").hide();
				jQuery("#field-h5p_content").show();				
			}
			else if(jQuery(this).hasClass("nav-tab-upload")) {
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").show();
				jQuery("#field-xapi_content").show();
				jQuery("#field-video, #field-video_hide_controls, #field-video_autoplay").hide();
				jQuery("#field-dropbox").hide();
				jQuery("#field-h5p_content").hide();
			}
			else if(jQuery(this).hasClass("nav-tab-dropbox")) {
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").show();
				jQuery("#field-xapi_content").hide();
				jQuery("#field-video, #field-video_hide_controls, #field-video_autoplay").hide();
				jQuery("#field-dropbox").show();
				jQuery("#field-h5p_content").hide();
			}			
			return false;
		});

		if(jQuery("#xapi_content[type=file]").length > 0 && jQuery("#xapi_content[type=file]").is(":visible"))
			gb_xapi_content_uploader('xapi_content');

		if(jQuery("#field-dropbox").length > 0 && jQuery("#field-dropbox").is(":visible"))
			grassblade_dropbox_init();

		if(jQuery("input#video").val().trim() != "")
			jQuery("a.nav-tab-video").click();
		else if(jQuery("input#src").val().trim() != "")
			jQuery("a.nav-tab-content-url").click();
		else
			jQuery("a.nav-tab-upload").click();

		jQuery("select#button_type").change(function() {
			if(jQuery(this).val() == "0")
			{
				jQuery("#field-text").show();
				jQuery("#field-link_button_image").hide();
			}
			else if(jQuery(this).val() == "1"){
				jQuery("#field-text").hide();
				jQuery("#field-link_button_image").show();
			}
		});		
		jQuery("select#button_type").change();

		jQuery("select#target").change(function() {
			if(jQuery(this).val() == "")
			{
                jQuery("#field-button_type").hide();
				jQuery("#field-text").hide();
				jQuery("#field-link_button_image").hide();
			}
			else
			{
				jQuery("#field-button_type").show();
				jQuery("select#button_type").change();
			}
			
		});
		jQuery("select#target").change();

		jQuery("#completion_tracking").change(function() {
			if (jQuery("#completion_tracking").is(":checked")) { 
                jQuery("#field-completion_type").show();
            } else { 
                jQuery("#field-completion_type").hide();
            } 
		});

		jQuery("#completion_tracking").change();

		/* Add aspect ratio options */
		var aspect = (parseFloat(jQuery("#field-width #width").val()) / parseFloat(jQuery("#field-height #height").val())).toFixed(4);
		aspect = (aspect == "NaN")? 0:aspect;
		jQuery(".grassblade_aspect_ratios").html("<span class='grassblade_aspect_ratio' data-aspect='1.7777' onClick='grassblade_set_aspect(this)'>16:9</span> | <span class='grassblade_aspect_ratio'  data-aspect='1.3333' onClick='grassblade_set_aspect(this)'>4:3</span> | <span class='grassblade_aspect_ratio'  data-aspect='1' onClick='grassblade_set_aspect(this)'>1:1</span> | <span id='aspect_slider_span'><input type='range' min='0' max='6' value='" + aspect + "' id='aspect_slider'  step='0.001'  onChange='grassblade_set_aspect(this)'/> <input type='text' id='aspect_slider_value' value='" + aspect + "'  maxlength='5' onChange='grassblade_set_aspect(this)' onkeyup='grassblade_set_aspect(this)' /></span>");
		jQuery("#field-width #width, #field-height #height").keyup(function(event) {
			if(jQuery("#aspect_lock").is(":checked")) {
				grassblade_size_setting_changed(event.target);
			}
		});

		grassblade_add_content_change();
		//grassblade_form_submit_refresh();
	}
	function grassblade_size_setting_changed(el) {
		if(jQuery("#aspect_lock").is(":checked")) {
			var param = jQuery(el).attr("id");
			var width = jQuery("#field-width #width").val();
			var height = jQuery("#field-height #height").val();
			var aspect = jQuery("#aspect_slider_value").val();
			aspect = aspect? aspect:1.7777;

			if(param == "width") {
				var unit = width.replace(parseFloat(width), '');

				h = parseFloat( parseFloat(width) / aspect ).toFixed(2);
				h = h + unit;
				jQuery("#field-height #height").val(h);

				if(unit == "%" && parseFloat(h) > 100)
					jQuery("#field-height #height").css('background', 'red');
				else
				{
					jQuery("#field-height #height").css('background', 'yellow');
					setTimeout(function() {
						jQuery("#field-height #height").css('background', 'none');
					}, 200);						
				}
			}
			else
			{
				var unit = height.replace(parseFloat(height), '');

				w = parseFloat( parseFloat(height) * aspect ).toFixed(2);
				w = w + unit;
				jQuery("#field-width #width").val(w);

				if(unit == "%" && parseFloat(w) > 100)
					jQuery("#field-width #width").css('background', 'red');
				else
				{
					jQuery("#field-width #width").css('background', 'yellow');
					setTimeout(function() {
						jQuery("#field-width #width").css('background', 'none');
					}, 200);						
				}		
			}
		}
	}
	function grassblade_set_aspect(el) {
		if(typeof el == "number" || jQuery(el).attr("class") == "grassblade_aspect_ratio") { 			//Ratio click
			if(typeof el == "number")
			var aspect = el;
			else
			var aspect = jQuery(el).data("aspect") * 1;
			
			jQuery("#aspect_slider").val(aspect);
			jQuery("#aspect_slider_value").val(aspect);

			jQuery("#aspect_slider_value").css('background', 'yellow');
			setTimeout(function() {
				jQuery("#aspect_slider_value").css('background', 'none');
			}, 200);

			jQuery("#aspect_slider_span").css('background', 'yellow');
			setTimeout(function() {
				jQuery("#aspect_slider_span").css('background', 'none');
			}, 200);
		}
		if(jQuery(el).attr("id") == "aspect_slider") {	// Slider Change
			var aspect = jQuery(el).val() * 1;
			jQuery("#aspect_slider_value").val(aspect);

			jQuery("#aspect_slider_value").css('background', 'yellow');
			setTimeout(function() {
				jQuery("#aspect_slider_value").css('background', 'none');
			}, 200);
		}
		else
		if(jQuery(el).attr("id") == "aspect_slider_value") { // Input Change
			var aspect = jQuery(el).val() * 1;
			jQuery("#aspect_slider").val(aspect);

			jQuery("#aspect_slider_span").css('background', 'yellow');
			setTimeout(function() {
				jQuery("#aspect_slider_span").css('background', 'none');
			}, 200);
		}
		
		var width = jQuery("#field-width #width").val();
		var height = jQuery("#field-height #height").val();

		var w, unit;

		if(jQuery("#aspect_lock").is(":checked")) 
		{	//Aspect Locked

			if(width == "" ||  parseFloat(width) > 100  ||  parseFloat(width) == 0 )
				w = "100%";
			else
				w = parseFloat(width) + "%";

			h = parseFloat( parseFloat(w) / aspect ).toFixed(2);

			if(h > 100)
			{
				h = 100;
				w = 100 * aspect + "%";
			}
			h = h + "%";
		}
		else
		{ //Aspect Not Locked.
			if(width == "" || width == "0" || width == "0%")
				w = "100%";
			else
				w = width;

			var unit = w.replace(parseFloat(w), '');
			h = parseFloat( parseFloat(w) / aspect ).toFixed(2);
			if(unit == "%" && h > 100) {
				h = 100;
				w = 100 * aspect + "%";
			}
			h = h + unit;
		}

		if(width != w)
		{
			jQuery("#field-width #width").val(w);	
			jQuery("#field-width #width").css('background', 'yellow');
			setTimeout(function() {
				jQuery("#field-width #width").css('background', 'none');
			}, 200);		
		}
		if(height != h)
		{
			jQuery("#field-height #height").val(h);
			jQuery("#field-height #height").css('background', 'yellow');
			setTimeout(function() {
				jQuery("#field-height #height").css('background', 'none');
			}, 200);			
		}
	}

	/* Add gb-fullscreen class when video is in fullscreen */
    function gb_fullscreen_class() {
        if (document.fullscreenElement || 
            document.mozFullScreenElement || 
            document.webkitFullscreenElement || 
            document.msFullscreenElement ) {
          if(!jQuery("body").hasClass("gb-fullscreen"))
            jQuery("body").addClass("gb-fullscreen");
        }
        else
          jQuery("body").removeClass("gb-fullscreen");   
    }

    /* Standard syntax */
    document.addEventListener("fullscreenchange", function() {
      gb_fullscreen_class();
    });

    /* Firefox */
    document.addEventListener("mozfullscreenchange", function() {
      gb_fullscreen_class();
    });

    /* Chrome, Safari and Opera */
    document.addEventListener("webkitfullscreenchange", function() {
      gb_fullscreen_class();
    });

    /* IE / Edge */
    document.addEventListener("msfullscreenchange", function() {
      gb_fullscreen_class();
    });
	/* Add gb-fullscreen class when video is in fullscreen */


	function grassblade_enable_button_selector() {
	  var _custom_media = true,
	      _orig_send_attachment = wp.media.editor.send.attachment;

	  jQuery('.gb_upload_button').click(function(e) {
	    var send_attachment_bkp = wp.media.editor.send.attachment;
	    var button = jQuery(this);
	    var id = button.attr('id');
	    _custom_media = true;
	    wp.media.editor.send.attachment = function(props, attachment){
	      if ( _custom_media ) {
	        jQuery("#"+id+"-url").val(attachment.url);
	        jQuery("#"+id+"-src").attr("src", attachment.url);
	      } else {
	        return _orig_send_attachment.apply( this, [props, attachment] );
	      };
	    }

	    wp.media.editor.open(button);
	    return false;
	  });

	  jQuery('.add_media').on('click', function(){
	    _custom_media = false;
	  });
	}

    if(typeof String.prototype.trim !== 'function') {
      String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
      }
    }
    function grassblade_update() {
    	if(jQuery("#src").val().length > 0 || jQuery("#video").val().length > 0 )
    	{
	    	jQuery("#gb_upload_message").addClass("has_content");
			jQuery("#gb_preview_message").addClass("has_content");
		}
    	jQuery(".is-primary").click();
    	if(jQuery(".is-primary.is-large").length)
    	var interval = setInterval(function() {
    		if(!jQuery(".is-primary.is-large").hasClass("is-busy")) {
	    		clearInterval(interval);
	    		jQuery(".is-primary.is-large").click();
	    		jQuery("#gb_progress_text").html('');
	    		jQuery("#gb_progress").removeClass();
	    	}
    	}, 200);
    }

function grassblade_add_content_change() {

    jQuery("select#h5p_content").change(function() {
		if(jQuery("select#h5p_content").val() != 0)
		{
			var h5p_content_id = jQuery("select#h5p_content").val();
			var url = content_data.ajax_url + "?action=h5p_embed&id=" + parseInt(h5p_content_id);
			jQuery("#activity_id").val(url);
			jQuery("#src").val(url);
			jQuery("#video").val('');
			grassblade_update();
		}
	});	

	jQuery("input#video").change(function() {
		jQuery("input#activity_id").val(jQuery("input#video").val());

		if(jQuery("input#video").val().length > 0)
		{
			var url = jQuery("input#video").val();
			jQuery("#activity_id").val(url);
			jQuery("#video").val(url);
			jQuery("#src").val('');
			jQuery("#h5p_content").val(0);
			grassblade_update();
		}

		/* Default value for MP3 player height */
		if(jQuery("input#height").val() == "" && jQuery("input#video").val().split(".").pop().split("?")[0] == "mp3") {
			jQuery("input#height").val("50px");
		}

	});
}

function gb_xapi_content_uploader(id) {

	var upload_msg = "";
	const gb_uploader = new plupload.Uploader({
			runtimes: 'html5,flash,silverlight,html4',
			'browse_button': id,
			url: content_data.ajax_url + "?action=gb_upload_content_file&gb_nonce=" + jQuery("[name=gb_xapi_content_box_content_nonce]").val(),
			'max_retries': content_data.plupload.max_retries,
			'dragdrop': true,
			'drop_element': id,
			'multi_selection': false,
			'file_data_name': 'xapi_content',
			filters: {
				'max_file_size': content_data.uploadSize,
				'mime_types': [
					{
						title: 'Zip files',
						extensions: 'zip'
					},
					{
						title: 'MP4 files',
						extensions: 'mp4'
					},
					{
						title: 'MP3 files',
						extensions: 'mp3'
					}
				]
			},
			multipart_params : {
		        "post_id" :  gb_data.post_id,
		    },
			init: {
				PostInit: function() {},

				UploadProgress: function( up, file ) {
					if(file.percent < 100)
					upload_msg = content_data.uploading;
					else
					upload_msg = content_data.processing;
					
					upload_msg = upload_msg.replace("[file_name]", file.name +' ('+ ( ( file.size / 1024 ) / 1024 ).toFixed( 1 ) +' mb) ').replace( "[percent]", file.percent+'%');

					document.getElementById( 'gb_progress_text' ).innerHTML = upload_msg;
					jQuery("#gb_progress_bar").css("background-color" , "#62A21D");
					var percent = (file.percent > 95)? 95:file.percent;
					jQuery("#gb_progress_bar").width(percent+'%');
				},

				FileUploaded: function( upldr, file, object ) {

					const info = jQuery.parseJSON( object.response );

					if ( info.response == 'success' ) {
						upload_msg = content_data.processed;
						upload_msg = upload_msg.replace("[file_name]", file.name);
						document.getElementById( 'gb_progress_text' ).innerHTML = upload_msg;

						jQuery("#gb_progress_bar").width('100%');
						grassblade_content_success_handling(info);
					}

					if ( info.response == 'error' ) {
						grassblade_content_error_handling(info);
					}
				},

				FilesAdded: function( up, files ) {
					if ( 1 < gb_uploader.files.length ) {
						gb_uploader.removeFile( gb_uploader.files[0]);
					}

					jQuery('#gb_progress').addClass('upload_progress');
					document.getElementById( 'gb_progress_text' ).innerHTML = gb_uploader.files[0].name +'('+( ( gb_uploader.files[0].size / 1024 ) / 1024 ).toFixed( 1 )+'mb)';
					gb_uploader.start();
				},

				Error: function( up, err ) {
					jQuery('#gb_progress').addClass('upload_progress');
					document.getElementById( 'gb_progress_text' ).innerHTML = err.message;
					jQuery("#gb_progress_bar").css("background-color" , "red");
					jQuery("#gb_progress_text").css("color" , "white");
					//console.log( err );
				}
			}
		});

	gb_uploader.init();
}

function grassblade_dropbox_init() {
	if (typeof Dropbox != 'undefined') {
		options = {
	        success: function(files){
	         	grassblade_upload_dropbox(files);
	        },
	        linkType: "direct",
		};
		var button = Dropbox.createChooseButton(options);
		document.getElementById("dropbox").appendChild(button);
	}
}

function grassblade_upload_dropbox(files){
    var file = files[0].name;
    var link = files[0].link;
    var nonce = jQuery("[name=gb_xapi_content_box_content_nonce]").val();

	jQuery('#gb_progress').addClass('upload_progress');

	jQuery('#gb_progress_text').text(content_data.dropbox_uploading.replace("[file_name]", file));

	jQuery("#gb_progress_bar").css("background-color" , "#62A21D");
	jQuery("#gb_progress_bar").width('95%');

	var form_data = new FormData();

    form_data.append("file", file);
    form_data.append("link", link);
    form_data.append("post_id", gb_data.post_id);
    form_data.append('action', 'dropbox_upload_file');  
    form_data.append('gb_nonce', nonce);  

    jQuery.ajax({
        type: 'POST',
        url: content_data.ajax_url,
        data: form_data,
        contentType: false,
        processData: false,
        success: function(response){
        	const info = JSON.parse(response);

        	if ( info.response == 'success' ) {
	        	jQuery('#gb_progress_text').text(content_data.processed.replace("[file_name]", file));

	        	jQuery("#gb_progress_bar").width('100%');

	        	grassblade_content_success_handling(info);
	        }
	        if ( info.response == 'error' ) {
				grassblade_content_error_handling(info);
			}
        }
    });
}

function grassblade_content_success_handling(info) {
	if (info.data.src) {
		jQuery('#src').val(info.data.src);
	} else
		jQuery('#src').val('');

	if (info.data.video) {
		jQuery('#video').val(info.data.video);
	} else
		jQuery('#video').val('');

	if (info.data.version) {
		jQuery('#version').val(info.data.version);
	} else
		jQuery('#version').val('');

	if (typeof(info.data.h5p_content_id) == "undefined" || !(info.data.h5p_content_id > 0) )
		jQuery('select#h5p_content').val(0);

	jQuery('#activity_id').val(info.data.activity_id);
	
	if( typeof  info.switch_tab == "string")
		jQuery(info.switch_tab).click();

	jQuery("#gb_upload_message").addClass("has_content");
	jQuery("#gb_preview_message").addClass("has_content");
}

function grassblade_content_error_handling(info){
	document.getElementById( 'gb_progress_text' ).innerHTML = info.info;
	jQuery("#gb_progress_bar").css("background-color" , "red");
	jQuery("#gb_progress_text").css("color" , "white");
}

function grassblade_launch_link_click(event) {
	window.open(event.href, "_blank");
	console_log('Window Launch');
	var completion_data = JSON.parse(event.getAttribute('data-completion'));
	if (typeof gb_data != 'undefined' && gb_data.completion_tracking_enabled && gb_data.lrs_exists && gb_data.is_guest == '' && gb_data.completion_type != 'hide_button' && (completion_data.completion_tracking != false) && (completion_data.completion_type != 'hide_button')){
		setTimeout(function () {
			grassblade_content_completion_request( completion_data.content_id , completion_data.registration, 0 );
	    }, 3000);
	}
	return false;
}

jQuery(window).on('load', function () {
	if (gb_data != 'undefined' && !gb_data.is_admin && gb_data.completion_tracking_enabled && gb_data.is_guest == '' && gb_data.completion_type != 'hide_button') {
		grassblade_control_lms_mark_complete_btn();
	    setTimeout(function () {
	    	grassblade_get_iframe();
	    }, 2000);
	}
});

function grassblade_get_iframe(){
	if (typeof gb_data != 'undefined' && !gb_data.is_admin && gb_data.completion_tracking_enabled && gb_data.lrs_exists && gb_data.is_guest == '' && gb_data.completion_type != 'hide_button') {
		var inpage_content = document.querySelectorAll('iframe.grassblade_iframe');
		for (var i = 0; i < inpage_content.length; i++) {
			var completion_data = grassblade_get_data_attribute(inpage_content[i], 'completion');
			if ((completion_data.completion_tracking != false) && (completion_data.completion_type != 'hide_button')){
				grassblade_script_to_iframe( document.querySelectorAll('iframe.grassblade_iframe')[i] );
			}
		}
	}
}

function grassblade_get_lightbox_iframe(){
	if (gb_data != 'undefined' && !gb_data.is_admin && gb_data.completion_tracking_enabled && gb_data.lrs_exists && gb_data.is_guest == '' && gb_data.completion_type != 'hide_button') {
		var lightbox_content = document.querySelectorAll('iframe.grassblade_lightbox_iframe');
		for (var i = 0; i < lightbox_content.length; i++) {
			var completion_data = grassblade_get_data_attribute(lightbox_content[i], 'completion');
			if ((completion_data.completion_tracking != false) && (completion_data.completion_type != 'hide_button')){
				grassblade_script_to_iframe( document.querySelectorAll('iframe.grassblade_lightbox_iframe')[i] );
			}
		}
	}
}

function grassblade_script_to_iframe(iframe_content){
	if( iframe_content.contentDocument != null ) { // adding script to iframe to trigger completion
  		if (iframe_content.contentDocument.querySelectorAll("script[src='"+gb_data.plugin_dir_url+"js/completion.js']").length <= 0){
	    	if(iframe_content.contentDocument.querySelector('body') != null){
  				iframe_content_selector = iframe_content.contentDocument.querySelector('body');
  			} else {
  				iframe_content_selector = iframe_content.contentDocument.querySelector('head');
  			}
	    	gb_scriptAppender(gb_data.plugin_dir_url+'js/completion.js', iframe_content_selector);
	    }
  	} else { // When not able to add or read script to iframe eg. external contents we start completion checking long pooling
		var completion_data = grassblade_get_data_attribute(iframe_content, 'completion');
		if ((completion_data.completion_tracking != false) && (completion_data.completion_type != 'hide_button')){
			grassblade_content_completion_request( completion_data.content_id , completion_data.registration , 0);
		}
 	} 
}

function gb_scriptAppender(src_path, selector) {
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = src_path;

	if (selector != null) {
		selector.appendChild(script);
	}
}

function grassblade_content_completion_request(content_id,registration,n) {
	console_log('grassblade_content_completion_request');
	console_log(n);

	if (gb_data.is_guest) {
		return;
	}
	jQuery('#grassblade_remark'+content_id).empty();

	if ( n != 0 && gb_data.is_guest == '') { // Show Loader Only for in Page and Lightbox content , Not for New Window and guest user
		var result_loader = "<div><strong> Getting your Result ... </strong><div class='gb-loader'></div></div>";
		jQuery('#grassblade_remark'+content_id).append(result_loader);
	}

	var activity_id = get_activity_id_by_content_id(content_id);
	if ((typeof(GB.completion) != "undefined") && GB.completion.disable_polling[activity_id]) {
		return; // if completion Code exist inside the content 
	}

	var data = {"content_id" : content_id , "registration" : registration, "post_id" :gb_data.post_id}

	jQuery.ajax({
        type : "POST",
        dataType : "json",
        url : content_data.ajax_url,
        data : { action: "grassblade_content_completion", data : data },
        success:function(data){
        	console_log('success');
        	console_log(data);

        	console_log('Value of n');
			console_log(n);

        	if (data != 0 && typeof(data.score_table) != "undefined") {
        		// Get content Score and completion
	        	grassblade_show_completion(data,content_id);
			} else {
				console_log('Result Not Found Yet');
				if (n == 0) {
					grassblade_content_completion_request(content_id,registration,0);
				} else if(n == 1){
					jQuery('#grassblade_remark'+content_id).empty();
					return;
				} else{
					grassblade_content_completion_request(content_id,registration,n-1);
				}
        	}
		},
		error: function(errorThrown){
			console_log('error');
			console_log(errorThrown);
			if (n = 0) {
				grassblade_content_completion_request(content_id,registration,1);
			} else if(n=1){
				return;
			} else{
				grassblade_content_completion_request(content_id,registration,n-1);
			}
		}  
    });
}

function grassblade_show_completion(data,content_id){
	console_log('grassblade_show_completion');
	console_log(data);

	jQuery('#grassblade_remark'+content_id).empty();

	if (jQuery('#grassblade_result-'+content_id).length) {
		jQuery('#grassblade_result-'+content_id).replaceWith(data.score_table);
	}

	if (data.completion_result.status == 'Failed') {
		var result_msg = "<strong>You did not pass.</strong>";
	}
	console_log(data.completion_result);
	console_log(data.completion_result.status);
	
	if (data.completion_result.status == 'Passed' || data.completion_result.status == 'Completed') {
		var result_msg = "<strong>Congratulations! You have successfully "+data.completion_result.status.toLowerCase()+" the content.</strong>";

		console_log(data.post_completion);
		if (data.post_completion == true) {
			var post_completion_type = get_post_completion_type();
			grassblade_lms_content_completion(post_completion_type);
		}
	}
	jQuery('#grassblade_remark'+content_id).append(result_msg);
}

function grassblade_lms_content_completion(completion_type){
	if (completion_type == 'disable_until_complete') {
    	jQuery(gb_data.mark_complete_button).prop('disabled', false);

    } else if (completion_type == 'hidden_until_complete'){
    	jQuery(gb_data.mark_complete_button).show();

    } else if (completion_type == 'completion_move_nextlevel'){
    	setTimeout(function () { 
            jQuery(gb_data.mark_complete_button).trigger('click');
        }, 3000);

    } else if (completion_type == 'hide_button'){
        // we can add next button for this condition auto_completion
    } else {
    	jQuery(gb_data.mark_complete_button).hide();
    }
}

function grassblade_control_lms_mark_complete_btn(){
    if(!gb_data.is_admin && gb_data.completion_tracking_enabled && gb_data.mark_complete_button != ''){
        console_log(gb_data.completion_type);
        if (gb_data.completion_type == 'disable_until_complete') {
        	jQuery(gb_data.mark_complete_button).attr('disabled','disabled');
        } else {
        	jQuery(gb_data.mark_complete_button).hide();
        }
    }
}

function gb_IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function get_post_completion_type(){
	var all_contents = document.querySelectorAll('.grassblade');
	for (var i = all_contents.length - 1; i >= 0 ; i--) {
		var attributes_data = grassblade_get_data_attribute(all_contents[i].firstElementChild, 'completion');
		if (attributes_data.completion_tracking) {
			return attributes_data.completion_type;
		}
	}
}

function get_completion_data_by_object_id(object_id){
	var all_contents = document.querySelectorAll('.grassblade');
	for (var i = 0; i < all_contents.length; i++) {
		var attributes_data = grassblade_get_data_attribute(all_contents[i].firstElementChild, 'completion');
		if (attributes_data.activity_id == object_id ) {
			return attributes_data;
		}
	}
}

function get_activity_id_by_content_id(content_id){
	var all_contents = document.querySelectorAll('.grassblade');
	for (var i = 0; i < all_contents.length; i++) {
		var attributes_data = grassblade_get_data_attribute(all_contents[i].firstElementChild, 'completion');
		if (attributes_data.content_id == content_id ) {
			return attributes_data.activity_id;
		}
	}
}

function grassblade_get_data_attribute(el, key, key2) {
	var el_val = jQuery(el).data(key);
	if(typeof key2 == "string")
	{
		return el_val[key2];
	}
	else
		return el_val;
}

function call_grassblade_get_completion(data){
	if (typeof(data.statement) != "undefined"){
    	var completion_data = get_completion_data_by_object_id(data.statement.object.id);
    	if ((typeof(GB.completion) != "undefined") && GB.completion.disable_polling[data.statement.object.id]) {
			GB.completion.disable_polling[data.statement.object.id] = false;
		}
    	console_log(completion_data);
    	if (completion_data) {
    		if ((completion_data.completion_tracking != false) && (completion_data.completion_type != 'hide_button')){
   				grassblade_content_completion_request(completion_data.content_id,completion_data.registration,2);
   			}
    	}
	}
}

window.addEventListener( "message",
	function (event) {
		if(!gb_data.is_admin){
		    //if(event.origin !== 'http://imac2.gblrs.com'){ return; } 
		    var data = event.data;
		    console_log('received response:  ');
		    console_log(data);

		    if (typeof(data.statement) === 'object') {
		    	call_grassblade_get_completion(data);
		    }

			if (data.msg == 'code_exist') {
				GB.completion =[];
		    	GB.completion.disable_polling =[];
				GB.completion.disable_polling[data.activity_id] = true;
				console_log(GB.completion.disable_polling[data.activity_id]);
			}
		}
	},
false); 

function console_log(arguments) {
  if(typeof window.gbdebug != "undefined")
  console.error(arguments);
}

function get_gb_quiz_report(content_id,user_id,registration,statement_id) {
	var src = content_data.ajax_url+'?action=gb_rich_quiz_report&id='+content_id;

	if (typeof registration == "string" && registration.length > 1)
		src += '&registration='+registration;

	if (typeof statement_id == "string" && statement_id.length > 1)
		src += '&statement_id='+statement_id;

    if (!isNaN(user_id) && user_id != null)
        src += '&user_id='+user_id;

	var quiz_report_iframe = '<iframe id="xapi_quiz_report" style="width: 100%; height: 100%; border: none; overflow-x:hidden;" src='+src+'></iframe>';

	if(document.getElementById("grassblade_quiz_report") == null)
		jQuery("body").append("<div id='grassblade_quiz_report'></div>");
	
	html = "<div class='grassblade_lightbox_overlay' onClick='return grassblade_hide_popup();'></div>"+
			"<div class='grassblade_popup'>"+
				"<div class='grassblade_close' onClick='return grassblade_hide_popup();'>X</div>" +
				quiz_report_iframe+
			"</div>";
	
	jQuery("#grassblade_quiz_report").html(html);
	jQuery("#grassblade_quiz_report").show();

	return false;
}

function grassblade_hide_popup(){
	jQuery("#grassblade_quiz_report").hide();
	jQuery("#grassblade_quiz_report").html('');
}


