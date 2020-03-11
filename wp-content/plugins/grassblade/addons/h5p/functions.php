<?php
add_action( 'admin_init', 'grassblade_h5p_check_plugin_availability' );
function grassblade_h5p_check_plugin_availability() {
  if (is_plugin_active('h5p/h5p.php') && class_exists('H5PContentQuery')){
    define("GB_H5P_SUPPORT_ENABLED", true);
  }
}

add_filter("xapi_content_params_update", "grassblade_h5p_params_update", 10, 2);
function grassblade_h5p_params_update($params, $post_id) {
  if(!empty($params['h5p_content'])) {
    $params['h5p_content_id'] = $params['h5p_content'];
    $params['src'] = admin_url( 'admin-ajax.php' )."?action=h5p_embed&id=".$params['h5p_content'];
    $params['activity_id'] = admin_url( 'admin-ajax.php' )."?action=h5p_embed&id=".$params['h5p_content'];
    update_post_meta( $post_id, 'h5p_content_id', $params['h5p_content_id'] );

    if(isset($params['content_path']))
    unset($params["content_path"]);
    if(isset($params['content_url']))
    unset($params["content_url"]);
    if(isset($params['content_size']))
    unset($params["content_size"]);
    if(isset($params['launch_path']))
    unset($params["launch_path"]);
    if(isset($params["original_activity_id"]))
      unset($params["original_activity_id"]);
    
    $params["content_type"] = "h5p";
  }
  else 
  {
    if(!empty($params["content_type"]) && $params["content_type"] == "h5p")
      $params["content_type"] = "";

    if(isset($params['h5p_content_id']))
    unset($params["h5p_content_id"]);

    delete_post_meta( $post_id, 'h5p_content_id' );
  }

  return $params;
}

/**
 * Check for the valid h5p content and embed the xapi js libraries.
 */
function grassblade_h5pmods_alter_scripts(&$scripts, $libraries, $embed_type) {
    global $wpdb;
    global $grassblade_h5p_add_xapi_config;

    if(empty($grassblade_h5p_add_xapi_config)) {
      if (empty($_GET['id'])) 
        return;

      $post_id = $_GET['id'];
      $h5p_content = $wpdb->get_row($wpdb->prepare("SELECT id
            FROM {$wpdb->prefix}h5p_contents
            WHERE id = '%d'", $post_id));

       // check for the valid h5p content    
      if (empty($h5p_content)) 
        return;

       // check auth and endpoint values attached with the h5p content launch url
      if (empty($_GET['auth']) || empty($_GET['endpoint'])) 
        return;    
    }
    $scripts[] = (object)array('path' => plugins_url('StatementViewer/scripts/xapiwrapper.js', dirname(__FILE__)), 'version' => '?ver=1.0');
    $scripts[] = (object)array('path' => plugins_url('h5p/js/script.js', dirname(__FILE__)), 'version' => '?ver=1.0');

    if(!empty($grassblade_h5p_add_xapi_config))
    {
      $data = array(
            "endpoint"  => $grassblade_h5p_add_xapi_config["endpoint"],
            "auth"      => $grassblade_h5p_add_xapi_config["auth"], 
            "registration" => $grassblade_h5p_add_xapi_config["registration"], 
            "actor"     => $grassblade_h5p_add_xapi_config["actor"],
          //  "activity_id" => $grassblade_h5p_add_xapi_config["activity_id"], 
          );
      $data = str_replace("=", "", base64_encode( serialize($data) ) );
      $url = admin_url( 'admin-ajax.php' )."?action=grassblade_h5p_script&token=".$data."&v=1.0";
      $scripts[] = (object)array('path' => $url);
    }
}
add_action('h5p_alter_library_scripts', 'grassblade_h5pmods_alter_scripts', 10, 3);

add_action( 'wp_ajax_nopriv_grassblade_h5p_script', 'grassblade_h5p_script' );
add_action( 'wp_ajax_grassblade_h5p_script', 'grassblade_h5p_script' );
function grassblade_h5p_script() {
  if(!empty($_GET['token'])) {
    $data = $_GET['token'];
    $data = maybe_unserialize( base64_decode($data."=") );

    $data["endpoint"] = urldecode(@$data["endpoint"]);
    $data["auth"] = urldecode(@$data["auth"]);
    $data["registration"] = urldecode(@$data["registration"]);
    $data["actor"] = urldecode(@$data["actor"]);

    if(empty($data["endpoint"]) || !filter_var($data["endpoint"], FILTER_VALIDATE_URL) || empty($data["auth"]) || preg_match('/[^A-Za-z0-9+/=]/', $data["auth"]))
      exit();

    header('Content-Type: application/javascript');

    $config = array(
            "endpoint"  => $data["endpoint"],
            "auth"      => $data["auth"],
            "registration" => $data["registration"],
            "actor"     => $data["actor"]
        );
    ?>
    //<![CDATA[
      ADL.XAPIWrapper.changeConfig(<?php echo json_encode($config); ?>);
    //]]>
    <?php  
  }
  exit();
}
/**  h5p content filter **/
function grassblade_h5pmods_embed_access($access, $h5p_content_id, $post_id = null) {
  if (!empty($h5p_content_id)) {
    if(empty($post_id)) {
    	global $wpdb;
    	$post_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'h5p_content_id' AND meta_value = '%d'", $h5p_content_id));
    	if(empty($post_ids))
    		return false;

    	foreach($post_ids as $post_id) {
    		if(grassblade_h5pmods_embed_access($access, $h5p_content_id, $post_id))
    			return true;
    	}
    	return false;
    }

    if(current_user_can('manage_options'))
      return true;

    $xapi_content = get_post_meta( $post_id, 'xapi_content', true); 

    if(!empty($xapi_content['guest']))
      return true; //if guest access is enabled, allow access. (by content setting) 
    else {
      $grassblade_settings = grassblade_settings();
      $grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
      if ($grassblade_tincan_track_guest) {
        return true; //if guest access is enabled, allow access.(by global setting) 
      }
    }
    
    $user_id = get_current_user_id();
    $posts_with_content = grassblade_get_post_with_content($post_id);
    if(empty($posts_with_content)) 
      return false;
    elseif (function_exists('sfwd_lms_has_access')) 
    {
      foreach ($posts_with_content as $p) {
        $course_ids = grassblade_learndash_get_course_ids($p->ID,true);

        if($course_ids) {
          foreach ($course_ids as $course_id) {
            $has_access = sfwd_lms_has_access($course_id, $user_id);
            if($has_access)
              return true; //allow access if has access to any LearnDash course with the content
          }
        }
        else
          $is_outside_learndash = true;
      }
      if(empty($is_outside_learndash))
        return false; //if content is only on learndash pages, and user doesn't have access to the courses. He doesn't have access to content. 
    }
    
    if($user_id)
      return true; //if nothing, allow access if logged in.
    else
      return false;
  }
  return $access;
}

add_filter('h5p_embed_access', 'grassblade_h5pmods_embed_access', 10, 2);

function grassblade_h5pmods_grassblade_shortcode_return($return, $params, $shortcode_atts, $attr, $completion_data = '') {

  $id = @$shortcode_atts["id"];

  if(!empty($id))
  $h5p_content_id = get_post_meta( $id, 'h5p_content_id', true);

  if(empty($h5p_content_id))
	return $return;

  $access = grassblade_h5pmods_embed_access(true, $h5p_content_id, $id);
  if(!$access) 
    return;

  if($shortcode_atts["target"] == "iframe" && (empty($_GET["context"]) || $_GET["context"] != "edit") ) {
    global $grassblade_h5p_add_xapi_config;
    $grassblade_h5p_add_xapi_config = $params;
    $id = empty($attr["id"])? "":"id='grassblade-".intVal($attr["id"])."'"; 
    $return = "<div $id class='grassblade'> <div class='grassblade-h5p' data-completion='".$completion_data."'>".do_shortcode('[h5p id="'.$h5p_content_id.'"]')."</div>";
  }

  return $return;
}

add_filter("grassblade_shortcode_return", "grassblade_h5pmods_grassblade_shortcode_return", 8, 5);


add_filter( 'grassblade_process_upload', 'non_h5p_content_process' , 80, 3);

function non_h5p_content_process($params, $post , $upload){

  if ($params['process_status'] == 1) {
      if (isset($params['h5p_content_id'])) {
          unset($params['h5p_content_id']);
      }
      if (isset($params['h5p_content'])) {
          unset($params['h5p_content']);
      }
  }

  return $params;
}


