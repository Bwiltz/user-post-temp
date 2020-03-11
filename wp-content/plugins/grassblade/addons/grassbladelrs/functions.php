<?php
//require_once(dirname(__FILE__)."/../nss_xapi.class.php");
//require_once(dirname(__FILE__)."/pv_xapi.class.php");
add_action( 'wp_ajax_nopriv_grassblade_completion_tracking', 'grassblade_grassbladelrs_process_triggers' );
add_action( 'wp_ajax_grassblade_completion_tracking', 'grassblade_grassbladelrs_process_triggers' );

add_action( 'wp_ajax_nopriv_grassblade_xapi_track', 'grassblade_grassbladelrs_xapi_track' );
add_action( 'wp_ajax_grassblade_xapi_track', 'grassblade_grassbladelrs_xapi_track' );

add_action('admin_menu', 'grassblade_grassbladelrs_menu', 1);
function grassblade_grassbladelrs_menu() {
	add_submenu_page("grassblade-lrs-settings", "GrassBlade LRS", "GrassBlade LRS",'manage_options','grassbladelrs-settings', 'grassblade_grassbladelrs_menupage');
}
function grassblade_show_trigger_debug_messages($msg) {
    if(!empty($_REQUEST["action"]) && in_array($_REQUEST["action"], array("grassblade_completion_tracking", "grassblade_xapi_track"))) {
        echo "\n";
        print_r($msg);
        echo "\n";
    }
}
function grassblade_grassbladelrs_menupage()
{
   //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    $grassblade_settings = grassblade_settings();
    $endpoint = $grassblade_settings["endpoint"];
    $api_user = $grassblade_settings["user"];
    $api_pass = $grassblade_settings["password"];
    $sso_auth = grassblade_file_get_contents_curl($endpoint."?api_user=".$api_user."&api_pass=".$api_pass."&t=".time());
    if(!empty($_GET['test'])) {
        echo $endpoint."?api_user=".$api_user."&api_pass=".$api_pass."&t=".time();
        print_r($sso_auth);
    }
    $invalid_access = (strpos($sso_auth, "Invalid Access") > -1);
    $sso_auth = json_decode($sso_auth);
    if(!empty($sso_auth) && !empty($sso_auth->sso_auth_token)) {
        $grassblade_lrs_launch_url = apply_filters("grassblade_lrs_launch_url", $endpoint."?sso_auth_token=".$sso_auth->sso_auth_token, $endpoint, $sso_auth->sso_auth_token);
    	?>
		<div class="wrap">
    	<iframe width="100%" height="1000px" src="<?php echo $grassblade_lrs_launch_url; ?>"></iframe>
    	</div>
    	<?php
    }
    else {
	?>
		<div class=wrap>
		<h2><img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', dirname(dirname(__FILE__))); ?>"/>
		GrassBlade LRS</h2>
		<br>
		<?php 
            if($invalid_access) {
                $hosts = array($_SERVER['SERVER_ADDR']);
                if(!in_array($_SERVER['HTTP_HOST'], $hosts))
                    $hosts[] = $_SERVER['HTTP_HOST'];
                if(!in_array($_SERVER['SERVER_NAME'], $hosts))
                    $hosts[] = $_SERVER['SERVER_NAME'];

                $url = str_replace("xAPI/", "Configure/Integrations#tab_2", $endpoint);
                echo sprintf(__("Your GrassBlade LRS did not authorize your request. Try adding your WordPress IP and domain:  %s in your %s ", "grassblade"), "<input id='ip_select' onClick='jQuery(this).select();' value='".implode(",",  $hosts)."' />", "<a href='".$url."'' target='_blank'>".__("GrassBlade LRS > Configure > Integrations > SSO", "grassblade")."</a>") ;
            }
            else
            echo sprintf(__("Please install %s and configure the API credentials to use this LRS Management Page", "grassblade"), "<a href='http://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/' target='_blank'>GrassBlade LRS</a>"); 
        ?>
		</div>
	<?php
	}
}

function grassblade_grassbladelrs_xapi_track() {
    if(empty($_REQUEST["grassblade_trigger"]))
        return;

    if(empty($_REQUEST["statement"]) || empty($_REQUEST["objectid"]) || empty($_REQUEST["agent_id"]))
    {
        grassblade_show_trigger_debug_messages( "Incomplete Data" );
        exit;
    }
    $statement = stripcslashes($_REQUEST["statement"]);
    //$statement_array = json_decode($statement);
    $objectid = urldecode(stripcslashes($_REQUEST["objectid"]));
    $objectid = explode("#", $objectid);
    $objectid = $objectid[0];
    $grassblade_xapi_content = new grassblade_xapi_content();
    $xapi_content_id = $grassblade_xapi_content->get_id_by_activity_id($objectid);
    if(empty( $xapi_content_id)) {
        grassblade_show_trigger_debug_messages( "Activity [".$objectid."] not linked to any content" );
        exit;
    }

    //$email = rawurldecode(stripcslashes($_REQUEST["agent_id"]));
    $user = grassblade_get_statement_user($statement);// get_user_by_grassblade_email($email);
    if(empty($user->ID)) {
        grassblade_show_trigger_debug_messages( "Unknown user: ".print_r($statement, true) );
        exit;
    }

    $statement = apply_filters("grassblade_xapi_tracked_pre", $statement, $xapi_content_id, $user);
    if(!empty($statement)) {
       // update_user_meta($user->ID, "completed_".$xapi_content_id, $statement);
        do_action("grassblade_xapi_tracked", $statement, $xapi_content_id, $user);
    }
    grassblade_show_trigger_debug_messages( "Processed ".$xapi_content_id );
}
add_action("parse_request", "grassblade_grassbladelrs_process_triggers");
function grassblade_grassbladelrs_process_triggers() {
    if(empty($_REQUEST["grassblade_trigger"]) || empty($_REQUEST["grassblade_completion_tracking"]))
        return;

    if(empty($_REQUEST["statement"]) || empty($_REQUEST["objectid"]) || empty($_REQUEST["agent_id"]))
    {
        grassblade_show_trigger_debug_messages( "Incomplete Data" );
        exit;
    }
    $statement = stripcslashes($_REQUEST["statement"]);
    //$statement_array = json_decode($statement);
    $objectid = urldecode(stripcslashes($_REQUEST["objectid"]));
    $grassblade_xapi_content = new grassblade_xapi_content();
    $xapi_content_id = $grassblade_xapi_content->get_id_by_activity_id($objectid);
    if(empty( $xapi_content_id)) {
        grassblade_show_trigger_debug_messages( "Activity [".$objectid."] not linked to any content" );
        exit;
    }

    //$email = rawurldecode(stripcslashes($_REQUEST["agent_id"]));
    $user = grassblade_get_statement_user($statement);// get_user_by_grassblade_email($email);
    if(empty($user->ID)) {
        grassblade_show_trigger_debug_messages( "Unknown user: ".print_r($statement, true) );
        exit;
    }

    $statement = apply_filters("grassblade_completed_pre", $statement, $xapi_content_id, $user);
    if(!empty($statement)) {
        $completed = apply_filters("grassblade_mark_complete", true, $_REQUEST, $statement, $xapi_content_id, $user->ID);
        if($completed) {
            grassblade_show_trigger_debug_messages( " Mark content completed " );
            update_user_meta($user->ID, "completed_".$xapi_content_id, $statement);
        }
        do_action("grassblade_completed", $statement, $xapi_content_id, $user);
    }
    grassblade_show_trigger_debug_messages( "Processed ".$xapi_content_id );
    exit;
}
add_filter("grassblade_mark_complete", function($return, $data, $statement_json, $xapi_content_id, $user_id) {
    $statement = json_decode($statement_json);
    $result = @$statement->result;

    $percentage = isset($statement->result->score->scaled)? $statement->result->score->scaled*100:((!empty($statement->result->score->max) && isset($statement->result->score->raw))? $statement->result->score->raw*100/($statement->result->score->max - @$statement->result->score->min):100);
    $percentage = round($percentage, 2);

    $xapi_content = get_post_meta($xapi_content_id, "xapi_content", true);
    if(isset($xapi_content["passing_percentage"]) && trim($xapi_content["passing_percentage"]) == "") {
        if(isset($statement->result->success)) {
            $status = (empty($statement->result->success) || is_string($statement->result->success) && $statement->result->success == "false")? "Failed":"Passed";
        }
        else
            $status = "Completed";
    }
    else
    {
        $pass = ($percentage >= @$xapi_content["passing_percentage"])? 1:0;
        $status = !empty($pass)? "Passed":"Failed";
    }
    return ($status != "Failed");
}, 10, 5);
function grassblade_get_statement_user($statement) {
    if (!is_string($statement))
        $statement = json_encode($statement);
    
    $statement = json_decode($statement);

    if(empty($statement) || empty($statement->actor))
        return false;

    if( !empty($statement->actor->account) ) {
        $homePage = @$statement->actor->account->homePage;
        $site_homePage = get_site_url(null, '', 'http');
        if($homePage == $site_homePage) {
            $user_id = @$statement->actor->account->name;
            if(!empty($user_id))
            return get_user_by("id", $user_id);
        }
        else
            grassblade_show_trigger_debug_messages('Mismatch in actor.account.homePage: '.$homePage." != ".$site_homePage);
    }
    if(!empty($statement->actor->mbox)) {
        $mbox = is_array($statement->actor->mbox)? $statement->actor->mbox[0]:$statement->actor->mbox;
        $email = str_replace("mailto:", "", $mbox);
        $user = get_user_by_grassblade_email($email);
        if(!empty($user->ID))
            return $user;
    }
    return false;
}

add_action("grassblade_completed", "grassblade_lrs_update_registration", 10, 3);
function grassblade_lrs_update_registration($statement_json, $xapi_content_id, $user) {
    if(empty($xapi_content_id))
        return;

    $grassblade_xapi_content = new grassblade_xapi_content();
    $xapi_content = $grassblade_xapi_content->get_params($xapi_content_id);
    if(empty($xapi_content["activity_id"]) || !empty($xapi_content["registration"]) && $xapi_content["registration"] != "auto" )
        return;

    $statement = json_decode($statement_json);
    $activity_id = $xapi_content["activity_id"];
    $r = get_user_meta($user->ID, "xapi_reg_".$activity_id, true);
    $registration = grassblade_gen_uuid();

    if(empty($r["latest"]))
    $r = array(
            "latest" => $registration,
            "registrations" => array(
                    $registration => array(
                            "generated" => time()
                        )
                )
        );
    else
    {
        $r["latest"] = $registration;
        if(!empty($statement->registration) && !empty($r["registrations"][$statement->registration])) {
            $r["registrations"][$statement->registration]["completed"] = $statement_json;
            $r["registrations"][$statement->registration]["completed_timestamp"] = $statement->timestamp;
        }
        
        if(empty($r["registrations"]))
            $r["registrations"] = array();

        $r["registrations"][$registration] = array(
                                                "generated" => time()
                                            );
    }

    update_user_meta($user->ID, "xapi_reg_".$activity_id, $r);
}
add_action("grassblade_completed", "grassblade_lrs_store_completion", 10, 3);
function grassblade_lrs_store_completion($statement_json, $xapi_content_id, $user) {
        $user_id = $user->ID;
        $statement = json_decode($statement_json);
        $result = @$statement->result;

        $score = !empty($statement->result->score->raw)? $statement->result->score->raw:(!empty($statement->result->score->scaled)? $statement->result->score->scaled*100:0);
        $percentage = isset($statement->result->score->scaled)? $statement->result->score->scaled*100:((!empty($statement->result->score->max) && isset($statement->result->score->raw))? $statement->result->score->raw*100/($statement->result->score->max - @$statement->result->score->min):100);
        $percentage = round($percentage, 2);
        $timespent = isset($statement->result->duration)? grassblade_duration_to_seconds($statement->result->duration):null;
		
        $timestamp = !empty($statement->timestamp)? strtotime($statement->timestamp):time();
        $passed_text = __("Passed", "grassblade");
        $failed_text = __("Failed", "grassblade");
        $completed_text = __("Completed", "grassblade");

        $xapi_content = get_post_meta($xapi_content_id, "xapi_content", true);
		if(isset($xapi_content["passing_percentage"]) && trim($xapi_content["passing_percentage"]) == "") {
            if(isset($statement->result->success)) {
                $status = (empty($statement->result->success) || is_string($statement->result->success) && $statement->result->success == "false")? "Failed":"Passed";
            }
            else
                $status = "Completed";
        }
		else
		{
        	$pass = ($percentage >= @$xapi_content["passing_percentage"])? 1:0;
			$status = !empty($pass)? "Passed":"Failed";
		}
		$data = array(
				"content_id" => $xapi_content_id,
				"user_id" => $user_id,
				"percentage" => $percentage,
				"status" => $status,
                "score" => $score,
				"statement" => $statement_json,
				"timespent" => $timespent,
				"timestamp" => date("Y-m-d H:i:s", $timestamp),
			);
        $data = apply_filters("grassblade_completions_data", $data);
        if(!empty($data)) {
            global $wpdb;
    		$wpdb->insert($wpdb->prefix."grassblade_completions", $data);
        }
}
add_action('delete_user', 'delete_grassblade_data');

function delete_grassblade_data($user_id) {
    global $wpdb;
    if (!empty($user_id) && is_numeric($user_id)) { 
        $wpdb->delete($wpdb->prefix."grassblade_completions", array('user_id' => $user_id));
    }
    return true;
}
/*
add_action('delete_post', 'delete_xapi_content', 10);
function delete_xapi_content($post_id) { 
    if (!empty($post_id) && is_numeric($post_id)) {     
        delete_post_meta($post_id, "xapi_activity_id");
        delete_post_meta($post_id, "xapi_content");
    }
    return true;
}
*/

add_filter( 'authenticate',  'grassblade_rest_api_authenticate', 20, 3);
add_filter( 'determine_current_user',  'grassblade_rest_api_auth_handler', 20, 1);
function grassblade_rest_api_auth_handler( $input_user ) {
    // Don't authenticate twice
    if ( ! empty( $input_user ) ) {
        return $input_user;
    }
    if( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
    }
    else
    {
        $headers = getallheaders();
        if(!empty($headers["Authorization"]))
            $auth = $headers["Authorization"];
        else if(!empty($_SERVER["REMOTE_USER"]))
            $auth = $_SERVER["REMOTE_USER"];
        else if(!empty($_SERVER["REDIRECT_REMOTE_USER"]))
            $auth = $_SERVER["REDIRECT_REMOTE_USER"];
        else if(!empty($_SERVER["HTTP_AUTHORIZATION"]))
            $auth = $_SERVER["HTTP_AUTHORIZATION"];
        else if(!empty($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]))
            $auth = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];

        if(empty($auth))
            return $input_user;

        $auth = explode(":", base64_decode( str_replace("Basic ", "", $auth) ) );
        $user = @$auth[0];
        $pass = @$auth[1];
    }    

    // Check that we're trying to authenticate
    if ( empty($user)  || empty($pass) ) {
        return $input_user;
    }

    $user = grassblade_rest_api_authenticate( $input_user, $user, $pass );

    if ( $user instanceof WP_User ) {
        return $user->ID;
    }

    // If it wasn't a user what got returned, just pass on what we had received originally.
    return $input_user;
}
if( !function_exists('getallheaders') )
{
    function getallheaders()
    {
       $headers = [];
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}
function grassblade_rest_api_authenticate($input_user, $username, $password ) {
    $api_request = ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
    if ( ! apply_filters( 'application_password_is_api_request', $api_request ) ) {
        return $input_user;
    }

    $user = get_user_by( 'login',  $username );

    // If the login name is invalid, short circuit.
    if ( ! $user ) {
        return $input_user;
    }

    if(wp_check_password($password, $user->user_pass, $user->ID)) {
       return $user; 
    }

    /*
     * Strip out anything non-alphanumeric. This is so passwords can be used with
     * or without spaces to indicate the groupings for readability.
     *
     * Generated application passwords are exclusively alphanumeric.
     */
    $password = preg_replace( '/[^a-z\d]/i', '', $password );

    $hashed_passwords = get_user_meta( $user->ID, 'grassblade_application_passwords', true );

    // If there aren't any, there's nothing to return.  Avoid the foreach.
    if ( empty( $hashed_passwords ) ) {
        return $input_user;
    }

    foreach ( $hashed_passwords as $key => $item ) {
        if ( wp_check_password( $password, $item['password'], $user->ID ) ) {
            $item['last_used'] = time();
            $item['last_ip']   = $_SERVER['REMOTE_ADDR'];
            $hashed_passwords[ $key ] = $item;
            update_user_meta( $user->ID, 'grassblade_application_passwords', $hashed_passwords );
            return $user;
        }
    }

    // By default, return what we've been passed.
    return $input_user;
}
/**
 * Prevent caching of unauthenticated status.
 */
add_filter( 'wp_rest_server_class', 'grassblade_wp_rest_server_class' );
function grassblade_wp_rest_server_class( $class ) {
    global $current_user;
    if ( defined( 'REST_REQUEST' )
         && REST_REQUEST
         && $current_user instanceof WP_User
         && 0 === $current_user->ID ) {
        /*
         * For our authentication to work, we need to remove the cached lack
         * of a current user, so the next time it checks, we can detect that
         * this is a rest api request and allow our override to happen.  This
         * is because the constant is defined later than the first get current
         * user call may run.
         */
        $current_user = null;
    }
    return $class;
}
