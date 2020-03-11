<?php

add_filter("grassblade_shortcode_atts", "grassblade_video_shortcode_atts", 2,2);

function grassblade_video_shortcode_atts($shortcode_atts, $attr) {
	if(!empty($shortcode_atts["video"])) 
	{
		$shortcode_atts["activity_id"] = $shortcode_atts["video"];
		$ext = pathinfo($shortcode_atts["activity_id"], PATHINFO_EXTENSION);
		$index_path = strtolower($ext) == "mpd" ? 'v2/dash.html':'v2/index.html';
		$shortcode_atts["src"] = apply_filters( "grassblade_video_player", plugins_url( $index_path , __FILE__ ), plugins_url( 'v1/index.html' , __FILE__ ), $shortcode_atts, $attr );
		if(!empty($shortcode_atts["activity_name"]))
			 $shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "activity_name=".rawurlencode($shortcode_atts["activity_name"]);

		if(!empty($shortcode_atts["passing_percentage"]) && is_numeric($shortcode_atts["passing_percentage"])) {
			$completion_threshold = number_format( $shortcode_atts["passing_percentage"]/100, 2);
			if(!empty($completion_threshold)) {
				$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "completion_threshold=".$completion_threshold;				
			}
		}

		if(!empty($shortcode_atts["video_hide_controls"])) {
			$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "video_controls=0";				
		}
		if(!empty($shortcode_atts["video_autoplay"]) && (empty($_REQUEST['context']) || is_admin() && $_REQUEST['context'] != "edit" ) ) {
			$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "video_autoplay=1";				
		}
		if(!empty($shortcode_atts["width"])) {
			$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "width=".rawurlencode($shortcode_atts["width"]);				
		}
		if(!empty($shortcode_atts["height"])) {
			$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "height=".rawurlencode($shortcode_atts["height"]);	
		}
		if(!empty($shortcode_atts["aspect"])) {
			$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "aspect=".rawurlencode($shortcode_atts["aspect"]);	
		}
		if(!empty($shortcode_atts["target"])) {
			$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "target=".rawurlencode($shortcode_atts["target"]);	
		}

		$exit_msg 				= __("You have reached the end of the video", "grassblade");
		$exit_button_name 		= __("Exit", "grassblade");
		$restart_button_name 	= __("Restart", "grassblade");
		$results_button_name 	= __("Results", "grassblade");

		$shortcode_atts["src"] .= ( strpos($shortcode_atts["src"], "?")?  "&":"?" ) . "exit_msg=".rawurlencode($exit_msg)."&exit_button_name=".rawurlencode($exit_button_name)."&restart_button_name=".rawurlencode($restart_button_name)."&results_button_name=".rawurlencode($results_button_name);
	}
	return $shortcode_atts;
}


add_filter("xapi_content_params_update", "grassblade_video_params_update", 10, 2);
function grassblade_video_params_update($params, $post_id) {
	if(!empty($params['video'])) {
		$video_url = $params['activity_id'] = $params['video'];
		$params['src'] = '';
	    $params["content_type"] = "video";

	    if(!empty($params['content_url'])) {
	    	$content_url = str_replace(array("http://", "https://"), array("",""), strtolower($params['content_url']));
			$video_url = str_replace(array("http://", "https://"), array("",""), strtolower($video_url));
			if(strpos($video_url, $content_url) === false)
			{
				if(isset($params["content_url"]))
					unset($params["content_url"]);
				if(isset($params["content_path"]))
					unset($params["content_path"]);
				if(isset($params["type"]))
					unset($params["type"]);
				if(isset($params["content_size"]))
					unset($params["content_size"]);
			}
		}
		if(isset($params["original_activity_id"]))
			unset($params["original_activity_id"]);
	}
	else { 
		if(isset($params['video_type']))
			unset($params['video_type']);

		if(!empty($params["content_type"]) && $params["content_type"] == "video" )  
			$params["content_type"] = "";
	}
	return $params;
}

add_filter( 'grassblade_process_upload', 'video_content_upload' , 30, 3);

function video_content_upload($params, $post , $upload) {
	$supported_file_formats = array("mp4", "mp3");

	$supported_zipped_file_formats = array(
							"playlist.m3u8", //HLS
							"*/playlist.m3u8", //HLS
							"index.m3u8", //HLS
							"*/index.m3u8", //HLS
							"*.m3u8", //HLS
							"*.mpd", //MPEG-DASH
							"*/*.mpd", //MPEG-DASH
						);

	$supported_file_formats = apply_filters("grassblade_video_file_formats", $supported_file_formats);
	$supported_zipped_file_formats = apply_filters("grassblade_video_zipped_file_formats", $supported_zipped_file_formats);

	if (empty($params['process_status'])) {

		if (isset($params['src'])) {
			unset($params['src']);
		}

		if ($ext = pathinfo($upload['content_url'], PATHINFO_EXTENSION)) {
			if(in_array($ext, $supported_file_formats)) {
				$params['video'] =  $upload['content_url'];
				$params['activity_id'] = $upload['content_url'];
				$params['video_type'] = $ext;
				$params['process_status'] = 1;
			}
		} else if(is_dir($upload["content_path"])) {
			$file_url = get_video_url($upload['content_path'], $upload['content_url'], $supported_zipped_file_formats);

			if ($file_url) {
				$params['video'] =  $file_url;
				$params['activity_id'] = $file_url;
				$ext = pathinfo($file_url, PATHINFO_EXTENSION);
				$params['video_type'] = $ext;
				$params['process_status'] = 1;
			}
		}

		if(!empty($params['process_status'])) {
			$params["content_url"] 	= $upload["content_url"];
			$params["content_path"] = $upload["content_path"];
			$params["type"] 		= $upload["type"];
		}

		return $params;
	}
	else
	if (isset($params['video'])) {
		unset($params['video']);
		unset($params['video_hide_controls']);
		unset($params['video_autoplay']);
	}
	
	return $params;
}

function get_video_url($dir, $url, $formats) {

	foreach ($formats as $format) {
		$files = glob($dir."/".$format);
		grassblade_debug($dir."/".$format);
		grassblade_debug($files);
		if(!empty($files[0]))
			return str_replace($dir, $url, $files[0]);
	}

	return ''; 
}

