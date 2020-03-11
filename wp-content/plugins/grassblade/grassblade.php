<?php
/**
 * @package GrassBlade 
 * @version 3.1.0
 */
/*
Plugin Name: GrassBlade xAPI Companion
Plugin URI: https://www.nextsoftwaresolutions.com
Description: GrassBlade xAPI Companion is a support tool for Experience API (xAPI) or Tin Can API. You can upload and launch Tin Can content published using Articulate, iSpring, DominKnow, Lectora and more. Can send tracking statements to the LRS on Page View. And, show statements in its Statement Viewer, and much more. 
Author: Next Software Solutions
Version: 3.1.0
Author URI: https://www.nextsoftwaresolutions.com
*/
define("GRASSBLADE_VERSION", "3.1.0");
define("GRASSBLADE_ADDON_DIR", dirname(__FILE__)."/addons");
require_once(dirname(__FILE__)."/db.update.php");
new GrassBlade_DB();

include(GRASSBLADE_ADDON_DIR."/grassblade_addons.php");
require_once(GRASSBLADE_ADDON_DIR."/nss_xapi.class.php");
require_once(GRASSBLADE_ADDON_DIR."/nss_xapi_verbs.class.php");
$GrassBladeAddons = new GrassBladeAddons();
$GrassBladeAddons->IncludeFunctionFiles();
define('GRASSBLADE_ICON15', plugin_dir_url(__FILE__)."img/button-15.png");
define('GB_DEBUG', false);

function grassblade($attr) {
	if ( post_password_required() )
	      return '';
	$grassblade_settings = grassblade_settings();
		
	$shortcode_defaults = array(
	 		'id' => 0,
			'version' => '1.0',
			'extra' => '',
			'target' => 'iframe',
			'width' => $grassblade_settings["width"],
			'height' => $grassblade_settings["height"],
			'aspect' => '',
			'endpoint' => '',
			'auth' => '',
			'user' => '',
			'pass' => '',
			'src' => '',
			'video'	=> '',
			'activity_name' => '',
			'title'	=> '',
			'video'	=> '',
			'text' => 'Launch',
			'link_button_image'	=> '',
			'guest' => false,
			'actor_type' => '',
			'activity_id' => '',
			'registration' => '',
			'show_results' => '',
			'show_rich_quiz_report' => '',
			'passing_percentage' => '',
			'video_hide_controls'	=> 0,
			'video_autoplay'	=> 0,
			'class'	=> '',
			);
	$shortcode_defaults = apply_filters("grassblade_shortcode_defaults", $shortcode_defaults);
	$id = intVal(@$attr["id"]);
	if(!empty($id)) {
		$xapi_content = new grassblade_xapi_content();
		$params = $xapi_content->get_shortcode($id, true);
		$shortcode_params = shortcode_atts ($params, $attr);
		if(empty($shortcode_params["title"])) {
			$xapi_content_post = get_post($id);
			$shortcode_params["title"] = @$xapi_content_post->post_title;
		}
		if(!empty($params["aspect_lock"])) {
			$shortcode_params["aspect"] = number_format( floatval($shortcode_params["width"]) / floatval($shortcode_params["height"]) , 4);
		}
		$shortcode_params["id"] = $id;
		$shortcode_defaults = shortcode_atts ( $shortcode_defaults, $shortcode_params);
	}
	$shortcode_atts = shortcode_atts ( $shortcode_defaults, $attr);
	$shortcode_atts = apply_filters("grassblade_shortcode_atts", $shortcode_atts, $attr);
	extract($shortcode_atts);
    	// Read in existing option value from database
	if(empty($endpoint))
    	$endpoint = $grassblade_settings["endpoint"];

	if(empty($user))
    	$user = $grassblade_settings["user"];

	if(empty($pass))
    	$pass = $grassblade_settings["password"];
	
	if($guest === false)
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
	else
	$grassblade_tincan_track_guest = $guest;
	
	if(empty($actor_type))
		$actor_type = empty($grassblade_settings["actor_type"])? "mbox":$grassblade_settings["actor_type"];

	$actor = grassblade_getactor($grassblade_tincan_track_guest, $version, $actor_type);
	
	if(empty($actor))
	{
		if($grassblade_tincan_track_guest == 2)
		{
			$guest_name = strip_tags(isset($_REQUEST['actor_name'])?$_REQUEST['actor_name']:'');
			$guest_mailto = strip_tags(isset($_REQUEST['actor_mbox'])?$_REQUEST['actor_mbox']:'');

			$name_email_form = "
				<div id='grassblade_name_email_form'>
				".__("Please enter your name and email to view the content:","grassblade")."<br>
				<form method='post'>
					<label>".__("Name", "grassblade")."</label>
					<input type='text' name='actor_name' value='".$guest_name."'/>
					<label>".__("Email", "grassblade")."</label>
					<input type='text' name='actor_mbox' value='".$guest_mailto."'/>
					<input type='submit' name='submit' value='".__("Submit", "grassblade")."'/>
				</form>
				</div>
			";
			return $name_email_form;
		}
		else
		return  __( 'Please login.', 'grassblade' );
	}
	$actor = rawurlencode(json_encode($actor));
	
	
	if(!empty($auth))
	$auth = rawurlencode($auth);
	else
	$auth = rawurlencode("Basic ".base64_encode($user.":".$pass));

	$endpoint = rawurlencode($endpoint);
	
	if(!empty($activity_id))
	$activity = 'activity_id='.rawurlencode($activity_id);
	else
	$activity = '';
		
	if(empty($registration) || $registration == "auto") {
		global $current_user;
		if(empty($current_user->ID))
		{
			$registration = grassblade_gen_uuid();
		}
		else
		{
			$r = get_user_meta($current_user->ID, "xapi_reg_".$activity_id, true);
			if(!empty($r["latest"])) {
				$registration = strip_tags( $r["latest"] );
			}
			else
			{
				if(empty($registration))
				$registration = "36fc1ee0-2849-4bb9-b697-71cd4cad1b6e";//.grassblade_gen_uuid();
				else if($registration == "auto")
				$registration = grassblade_gen_uuid();

				$r = array(
						"latest" => $registration,
						"registrations"	=> array(
								$registration => array(
										"generated"	=> time()
									)
							)
					);
				update_user_meta($current_user->ID, "xapi_reg_".$activity_id, $r);
			}
		}
	}
	else if($registration != 0)
	$registration = strip_tags( $registration );
	
	//$content_endpoint = "content_endpoint=".rawurlencode(dirname($src).'/');
	//$content_token = "content_token=".grassblade_gen_uuid();
	
	if(empty($src)) {
		//Don't change src if src is empty.	
	}
	else if($version == "none")
	{
		if(!empty($video))
			$src = $src."&".$activity;
		//Don't change SRC. Supporting Non xAPI Content.
	}
	else
	{
		$src .= (strpos($src,"?") !== false)? "&":"?";
		$src .= "actor=".$actor."&auth=".$auth."&endpoint=".$endpoint."&registration=".$registration."&".$activity;//."&".$content_endpoint."&".$content_token;
	}
	if(!empty($text))
		$text = sanitize_text_field( $text );

	if(!empty($link_button_image)) {
		$text = "<img src='". esc_url( $link_button_image )."' />";
	}
	$src = esc_url($src);

	$aspect_attr = !empty($aspect)? "data-aspect='".$aspect."'":"";

	$current_user = wp_get_current_user();
	if (empty($current_user->ID)) {
		$is_guest = true;
	} else {
		$is_guest = false;
	}

	$completion_data = array(
						"registration" => $registration,
						"content_id" => $id,
						"completion_tracking" => grassblade_xapi_content::is_completion_tracking_enabled($id),
						"completion_type" => grassblade_xapi_content::get_completion_type($id),
						"activity_id" => $activity_id,
						"is_guest" => $is_guest
					);

	$completion_data = json_encode($completion_data);

	if(empty($src))
		$return = ''; //Return blank if empty src
	else if($target == 'iframe')
	$return = "<iframe class='grassblade_iframe' data-completion='$completion_data' frameBorder='0' data-src='$src' ".$aspect_attr."  style='margin:0; position: relative;' width='$width' height='$height' webkitallowfullscreen mozallowfullscreen allowfullscreen allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' onLoad='grassblade_xapi_content_autosize_content();'></iframe>";
	else if($target == '_blank')
	$return = "<a class='grassblade_launch_link' data-completion='$completion_data' href='$src' target='_blank' onClick='return grassblade_launch_link_click(this);'>$text</a>";
	else if($target == '_self')
	$return = "<a class='grassblade_launch_link' data-completion='$completion_data' href='$src' target='_self'>$text</a>";
	else if($target == 'lightbox')
	{	
		$return = grassblade_lightbox($src, $completion_data, $text,$width, $height, $aspect);
	}
	else //if($target == 'url')
	$return = $src;

	$params = array(
			"src" 	=> $src,
			"actor" => $actor,
			"auth"	=> $auth,
			"activity_id"	=> $activity_id,
			"registration"	=> $registration,
			"endpoint"	=> @$endpoint,
			"auth"		=> @$auth
		);
	$gb_remark = '<div id="grassblade_remark'.$id.'"></div>';
	$id = empty($id)? "":"id='grassblade-".$id."'";

	$classes = 'grassblade';
	if (!empty($class)) {
		$additional_class = explode(" ", $class);
		in_array("grassblade", $additional_class)? '' : $class .= ' grassblade';
		$classes = $class;
	}
	$return = "<div $id class='$classes'>".$return."</div>".$gb_remark;
	return apply_filters("grassblade_shortcode_return", $return, $params, $shortcode_atts, $attr, $completion_data);
}
function grassblade_scripts() {
	global $pagenow;

	$grassblade_add_scripts_on_page = array("grassblade-lrs-settings");
	$grassblade_add_scripts_on_page = apply_filters("grassblade_add_scripts_on_page", $grassblade_add_scripts_on_page);
	if(!is_admin() || $pagenow == "post-new.php" || $pagenow == "post.php" || !empty($_GET["page"]) && in_array($_GET["page"], $grassblade_add_scripts_on_page))
	wp_enqueue_script(
		'grassblade',
		plugins_url('/js/script.js', __FILE__),
		array('jquery'), GRASSBLADE_VERSION
	);
	
	global $post;

	$post_id = empty($post)?'':$post->ID;
	$lrs_exists = true;
	$grassblade_settings = grassblade_settings();
	if (isset($grassblade_settings['endpoint']) && $grassblade_settings['endpoint'] == '/' || $grassblade_settings['endpoint'] == null) {
		$lrs_exists = false;
	}
	$gb_data = array('plugin_dir_url' => plugin_dir_url( __FILE__ ),
					 'is_admin' => is_admin(),
					 "is_guest" => (get_current_user_id() == 0),
					 "post_id" => $post_id,
					 "lrs_exists" => $lrs_exists,
					 "completion_tracking_enabled" => grassblade_xapi_content::is_completion_tracking_enabled_by_post($post_id)
					);
	$gb_data = apply_filters('grassblade_localize_script_data', $gb_data, $post);
	
	wp_localize_script( 'grassblade', 'gb_data',  $gb_data);
}
function grassblade_styles() {
	wp_enqueue_style(
		'grassblade',
		plugins_url('/css/styles.css', __FILE__),
		null, GRASSBLADE_VERSION
	);
}

add_action("init", "grassblade_styles");
add_action("wp_print_scripts", "grassblade_scripts");

function grassblade_lightbox($src, $completion_data, $text, $width, $height, $aspect = null) {
	$completion_data_lightbox = addslashes($completion_data);
	$return = '';
	$id = 'grassblade_'.md5($src);
	$return .= "<a class='grassblade_launch_link' data-completion='$completion_data' href='#' onClick='grassblade_show_lightbox(\"$id\", \"$src\", \"$completion_data_lightbox\", \"$width\", \"$height\", \"$aspect\"); return false;'>".$text."</a>";
	return $return;
}
function grassblade_gen_uuid() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

		// 16 bits for "time_mid"
		mt_rand( 0, 0xffff ),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand( 0, 0x0fff ) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand( 0, 0x3fff ) | 0x8000,

		// 48 bits for "node"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}
function grassblade_textdomain() {
 $plugin_dir = basename(dirname(__FILE__));
 load_plugin_textdomain( 'grassblade', $plugin_dir."/languages", $plugin_dir."/languages" );
}
add_action('plugins_loaded', 'grassblade_textdomain');



function grassblade_getdomain()
{
	$domain = $_SERVER["HTTP_HOST"];
	
	if(empty($domain))
	$domain = $_SERVER["SERVER_NAME"];
	
	if(filter_var($domain, FILTER_VALIDATE_IP))
	return $domain.".com";
	else
	return $domain;
}
function grassblade_getactor($guest = false, $version = "1.0", $user = null, $actor_type = null)
{
	if(!empty($user->ID))
		$current_user = $user;
	else
		$current_user = wp_get_current_user();

	if(empty($current_user->ID))
	{
		if(empty($guest))
			return false;
		else if(intval($guest) === 2) // Guest with Name and Email
		{
			if(empty($_REQUEST['actor_mbox']) || empty($_REQUEST['actor_name']) || filter_var($_REQUEST['actor_mbox'], FILTER_VALIDATE_EMAIL) === false) 
				return false;

			$guest_name = strip_tags($_REQUEST['actor_name']);
			$guest_mailto = "mailto:".$_REQUEST['actor_mbox'];
			if($version == "0.90")
				$actor = array('mbox' => array($guest_mailto), 'name' => array($guest_name), "objectType" =>  "Agent");
			else
				$actor = array('mbox' => $guest_mailto, 'name' => $guest_name, "objectType" =>  "Agent");			

			return $actor;
		}
		else
		{ // Guest with IP
			$guest_mailto = "mailto:guest-".$_SERVER['REMOTE_ADDR'].'@'.grassblade_getdomain();
			$guest_name = "Guest ".$_SERVER['REMOTE_ADDR'];
			if($version == "0.90")
				$actor = array('mbox' => array($guest_mailto), 'name' => array($guest_name), "objectType" =>  "Agent");
			else
				$actor = array('mbox' => $guest_mailto, 'name' => $guest_name, "objectType" =>  "Agent");			
				
			return $actor;
		}
	}
	
	if(!empty($current_user->display_name))
	$name = $current_user->display_name;
	else
	if(!empty($current_user->user_firstname) || !empty($current_user->user_firstname))
	$name = $current_user->user_firstname." ".$current_user->user_lastname;
	else
	$name = $current_user->user_login;
	
	if(empty($actor_type)) {
		 $grassblade_settings = grassblade_settings();
		 $actor_type = empty($grassblade_settings["actor_type"])? "mbox":$grassblade_settings["actor_type"];
	}

	if( $actor_type == "mbox" ) {
		$mbox = "mailto:".grassblade_user_email($current_user->ID);
		if($version == "0.90")
		$actor = array('mbox' => array($mbox), 'name' => array($name), "objectType" =>  "Agent");
		else
		$actor = array('mbox' => $mbox, 'name' => $name, "objectType" =>  "Agent");
	} else if( $actor_type == "account" ) {
		$accountHomePage = get_site_url(null, '', 'http');

		if($version == "0.90")
		$actor = array('account' => array( array("accountServiceHomePage" => $accountHomePage, "accountName" => (string) $current_user->ID ) ), 'name' => array( $name ), "objectType" =>  "Agent");
		else
		$actor = array('account' => array("homePage" => $accountHomePage, "name" => (string) $current_user->ID ), 'name' => $name, "objectType" =>  "Agent");
	}
	return $actor;
}
function grassblade_get_actor_id($actor) {
	if(isset($actor["mbox"]))
		return str_replace("mailto:", "", @$actor["mbox"]);
	if(isset($actor["mbox_sha1sum"]))
		return $actor["mbox_sha1sum"];
	if(isset($actor["openid"]))
		return $actor["openid"];
	if(isset($actor["account"]))
		return @$actor["account"]["homePage"]."/". (@$actor["account"]["name"]);
}
add_shortcode("grassblade", "grassblade");

function grassblade_user_email($user_id) {
	$email = get_user_meta($user_id, "grassblade_email", true);
	if(!empty($email))
		return $email;
	$user = get_user_by("id", $user_id);
	update_user_meta($user_id, "grassblade_email", $user->user_email);
	return $user->user_email;
}
function grassblade_post_activityid($post_id) {
	if(empty($post_id))
		return '';
	
	$activity_id = get_post_meta($post_id, "xapi_activity_id", true);
	if(!empty($activity_id))
		return $activity_id;

	$activity_id = get_post_meta($post_id, "activity_id", true);
	if(!empty($activity_id))
		return $activity_id;

	$activity_id = get_permalink($post_id);
	update_post_meta($post_id, "activity_id", $activity_id);
	return $activity_id;
}
function get_user_by_grassblade_email($email) {
	$users = get_users("meta_key=grassblade_email&meta_value=".$email);
	if(!empty($users[0]))
		return $users[0];

	return get_user_by("email", $email);
}
function get_post_by_grassblade_activity_id($activity_id){

	if (empty($activity_id)) {
		return false;
	}

	$args = array (
	    'post_type'              => array( 'gb_xapi_content' ),
	    'post_status'            => array( 'publish' ),
	    'meta_query'             => array(
	        array(
	            'key'       => 'xapi_activity_id',
	            'value'     => $activity_id,
	        ),
	    ),
	);

	$xapi_contents = get_posts($args);

	if (!empty($xapi_contents)) {
		return $xapi_contents[0];
	}

	return false;
}

function grassblade_settings($return = null) {
	global $grassblade_xapi_companion;
	if(method_exists($grassblade_xapi_companion, 'get_params'))
	$grassblade_settings = $grassblade_xapi_companion->get_params();

	$grassblade_settings = apply_filters("grassblade_settings", @$grassblade_settings, $return);

	if(empty($return))
		return $grassblade_settings;
	else
		return @$grassblade_settings[$return];
}
class grassblade_xapi_companion {
	public $debug = false;
	public $secure_token_options;
	public $grassblade_settings;
	function __construct() {
		$this->secure_token_options = array(
					"" => __("None", "grassblade"),
					"9" => __("Low", "grassblade"), //User 
					"11" => __("Medium", "grassblade"), //User + Activity
					"15" => __("High", "grassblade"), //User + Activity + IP
				);
		$this->grassblade_settings = $this ->get_params();
	}
	function run() {
		add_action('admin_menu', array($this, 'admin_menu'), 0);
	}
	function define_fields() {
		if(!empty($this->fields))
			return $this->fields;
		// define the product metadata fields used by this plugin
		$versions = array(
					'1.0' => '1.0',
					'0.95' => '0.95',
					'0.9' => '0.9',
					'none' => 'Not XAPI'
				);

		$completion_option = array(
									'hide_button' => __('Hide Button', "grassblade"),
									'hidden_until_complete' => __('Show button on completion', "grassblade"),
									'disable_until_complete' => __('Enable button on completion', "grassblade"),
									'completion_move_nextlevel' => __('Auto-redirect on completion', "grassblade"),
								);

		$track_guest_values = array(
					'' => __('No', 'grassblade'),
					'1' => __('Allow Guests', 'grassblade'),
					'2' => __('Allow Guests (ask Name/Email)', 'grassblade'),
				);
		$actor_types = array(
					"mbox"	=> "Name and Email",
					"account" => "Name and User ID"
				);
		$domain = grassblade_getdomain();
		$this->fields = array(
			array( 'id' => "lrs_settings", 'label' => __("LRS Settings", "grassblade"), "type" => "html", "subtype" => "field_group_start", "class" => "default_open"),
			array( 'id' => 'endpoint', 'label' => __( 'Endpoint URL', 'grassblade' ), 'placeholder' => '', 'type' => 'text', 'values'=> '', 'never_hide' => true , 'help' => __( 'provided by your LRS, check FAQ below for details', 'grassblade').". ".__("You might need to add a trailing slash (<b>/</b>) at the end of the url if its not already there.","grassblade")),
			array( 'id' => 'user', 'label' => __( 'API User', 'grassblade' ), 'placeholder' => '', 'type' => 'text', 'values'=> '', 'never_hide' => true ,'help' => __( 'provided by your LRS, check FAQ below for details', 'grassblade')),
			array( 'id' => 'password', 'label' => __( 'API Password', 'grassblade' ),  'placeholder' => '', 'type' => 'text', 'values'=> '', 'never_hide' => true ,'help' => __( 'provided by your LRS, check FAQ below for details', 'grassblade')),
			array( 'id' => 'secure_tokens', 'label' => __( 'Secure Tokens', 'grassblade' ),   'placeholder' => '', 'type' => 'select', 'values'=> $this->secure_token_options, 'never_hide' => true ,'help' => __( 'Generates secure random tokens when launching xAPI Content.', 'grassblade')),
			array( 'id' => "lrs_settings_end", "type" => "html", "subtype" => "field_group_end"),
			array( 'id' => "content_settings", 'label' => __("Content Settings", "grassblade"), "type" => "html", "subtype" => "field_group_start"),
			array( 'id' => 'width', 'label' => __( 'Width', 'grassblade' ),  'placeholder' => '', 'type' => 'text', 'values'=> '', 'never_hide' => true ,'help' => __('Default width of iframe/lightbox in which content is launched', 'grassblade')),
			array( 'id' => 'height', 'label' => __( 'Height', 'grassblade' ), 'placeholder' => '', 'type' => 'text', 'values'=> '', 'never_hide' => true ,'help' => __('Default height of iframe/lightbox in which content is launched', 'grassblade')),
			array( 'id' => 'completion_type', 'label' => __( 'Completion Type', 'grassblade' ),   'placeholder' => '', 'type' => 'select', 'values'=> $completion_option, 'never_hide' => true ,'help' => __('Default Completion on content', 'grassblade')),
			array( 'id' => 'version', 'label' => __( 'Version', 'grassblade' ),   'placeholder' => '', 'type' => 'select', 'values'=> $versions, 'never_hide' => true ,'help' => __( 'Default xAPI (Tin Can) version of content. Generally depends on your Authoring Tool.', 'grassblade')),
			array( 'id' => 'track_guest', 'label' => __( 'Track Guest Users', 'grassblade' ),  'placeholder' => '', 'type' => 'select', 'values'=> $track_guest_values, 'never_hide' => true ,'help' => sprintf(__('<b>Allow Guests:</b> Tracked as <b>{"name":"Guest XXX.XXX.XXX.XXX", "actor":{"mbox": "mailto:guest-XXX.XXX.XXX.XXX@%s"}</b>) where <i>XXX.XXX.XXX.XXX</i> is users IP. Not Logged In users will be able to access content, and their page views will also be tracked.  <br><b>Allow Guests (ask Name/Email):</b> Asks for Name and Email from not logged in user before taking content.', 'grassblade'), $domain)),
			array( 'id' => 'actor_type', 'label' => __( 'User Identifier', 'grassblade' ),  'placeholder' => '', 'type' => 'select', 'values'=> $actor_types, 'never_hide' => true ,'help' => __('This is the user identification information sent to the LRS.', 'grassblade')),
			array( 'id' => 'url_slug', 'label' => __( 'URL Slug', 'grassblade' ),  'placeholder' => 'gb_xapi_content', 'type' => 'text', 'values'=> '', 'never_hide' => true, 'help' => __('This is part of the url of your xAPI Content page.', 'grassblade')),
			array( 'id' => 'disable_statement_viewer', 'label' => __( 'Disable Statement Viewer', 'grassblade' ),  'placeholder' => '', 'type' => 'checkbox', 'values'=> '', 'never_hide' => true ,'help' => __('Disable Statement Viewer on xAPI Content Edit Page.', "grassblade")),
			array( 'id' => "content_settings_end", "type" => "html", "subtype" => "field_group_end"),
			array( 'id' => "upload_settings", 'label' => __("Upload Settings", "grassblade"), "type" => "html", "subtype" => "field_group_start"),
			array( 'id' => 'dropbox_app_key', 'label' => __( 'Dropbox APP Key', 'grassblade' ),  'placeholder' => '', 'type' => 'text', 'values'=> '', 'never_hide' => true ,'help' => __( 'Required only if you want to upload xAPI Content from Dropbox, Work well with large file.', 'grassblade'). " (<a href='http://www.nextsoftwaresolutions.com/direct-upload-of-tin-can-api-content-from-dropbox-to-wordpress-using-grassblade-xapi-companion/'  target='_blank'>".__('Get your Dropbox App Key, takes less than minutes','grassblade')."</a>)" ),
			array( 'id' => "upload_settings_end", "type" => "html", "subtype" => "field_group_end"),
		);
		$this->fields = apply_filters("grassblade_settings_fields", $this->fields);
	}
	function form() {
			global $post;
			$data = $this->get_params();
			
			$this->define_fields();
		?>
			<div id="grassblade_xapi_settings_form"><table width="100%">
			<?php
				foreach ($this->fields as $field) {
					if($field["type"] == "html" && @$field["subtype"] == "field_group_start") {
						echo "<tr><td colspan='2'  class='grassblade_field_group ".esc_attr(@$field["class"] )."'>";
						echo "<div class='grassblade_field_group_label'><div class='dashicons dashicons-arrow-down-alt2'></div><span>".esc_html( $field["label"] )."</span></div>";
						echo "<div class='grassblade_field_group_fields' style='". esc_attr( @$field["style"] )."'><table width='100%'>";
						continue;
					}
					if($field["type"] == "html" && @$field["subtype"] == "field_group_end") {
						echo "</table></div></td></tr>";
						continue;
					}

					$value = isset($data[$field['id']])? $data[$field['id']]:'';
					echo '<tr id="field-'.esc_attr( $field['id'] ).'"><td width="20%" valign="top"><label for="'.esc_attr( $field['id'] ).'">'. esc_html( $field['label'] ).'</label></td><td width="100%">';
					switch ($field['type']) {
						case 'html' :
							echo $field["html"];
						break;
						case 'text' :
							echo '<input  style="width:80%" type="text"  id="'.esc_attr( $field['id'] ).'" name="'.esc_attr( $field['id'] ).'" value="'.sanitize_text_field( $value ).'" placeholder="'.sanitize_text_field( $field['placeholder']).'"/>';
						break;
						case 'file' :
							echo '<input  style="width:80%" type="file"  id="'.esc_attr( $field['id'] ).'" name="'.esc_attr( $field['id'] ).'" value="'.sanitize_text_field( $value ).'" placeholder="'.sanitize_text_field( $field['placeholder']).'"/>';
						break;
						case 'number' :
							echo '<input  style="width:80%" type="number" id="'.esc_attr( $field['id'] ).'" name="'.esc_attr( $field['id'] ).'" value="'.sanitize_text_field( $value ).'" placeholder="'.sanitize_text_field( $field['placeholder'] ).'"/>';
						break;
						case 'textarea' :
							echo '<textarea   style="width:80%"  id="'.esc_attr( $field['id'] ).'" name="'.esc_attr( $field['id'] ).'" placeholder="'.sanitize_text_field(  $field['placeholder'] ).'">'.esc_textarea( $value ).'</textarea>';
						break;
						case 'checkbox' :
							$checked = !empty($value) ? ' checked=checked' : '';
							echo '<input type="checkbox" id="'.esc_attr( $field['id'] ).'" name="'.esc_attr( $field['id'] ).'" value="1"'.$checked.'>';
						break;
						case 'select' :
							echo '<select id="'.esc_attr( $field ['id'] ).'" name="'.esc_attr( $field['id'] ).'">';
							foreach ($field['values'] as $k => $v) :
								$selected = ($value == $k && $value != '') ? ' selected="selected"' : '';
								echo '<option value="'.esc_attr( $k ).'"'.$selected.'>'.sanitize_text_field( $v ).'</option>';
							endforeach;
							echo '</select>';
						break;
						case 'select-multiple':
						
							echo '<select id="'.esc_html( $field['id'] ).'" name="'. esc_attr( $field['id'] ).'[]" multiple="multiple">';

							foreach ($field['values'] as $k => $v) :
								if(!is_array($value)) $value = (array) $value;
								$selected = (in_array($k, $value)) ? ' selected="selected"' : '';
								echo '<option value="'.esc_attr( $k ).'"'.$selected.'>'.sanitize_text_field( $v ).'</option>';
							endforeach;
							echo '</select>';

					}
					if(!empty($field['help'])) {
						echo '<br><small>'. $field['help'] .'</small><br><br>';
						echo '</td></tr>';
					}
				}
				?>
				</table>
				<br>
			</div>
		<?php
	}
	function set_params($id = null, $value = null) {
		if(!empty($id) && !is_null($value)) {
			update_option("grassblade_tincan_".$id, $value);
			return;
		}
		if( !isset($_POST[ "update_GrassBladeSettings" ]) )
			return;
    	
    	$grassblade_settings_old = $this->get_params();

		$this->define_fields();
		foreach ($this->fields as $field) {
			if($field["type"] == "html") 
				continue;
			switch ($field["id"]) {
				case 'endpoint':
					$value = trim(@$_POST[$field["id"]]);
					if(substr($value, -1) != "/")
						$value = $value."/";
					break;
				/*case 'track_guest':
					$value = (isset($_POST[$field["id"]]) && !empty($_POST[$field["id"]]))? 1:0;
					break;*/
				case 'width':
				case 'height':
        			$value = intVal(@$_POST[$field["id"]]).(strpos(@$_POST[$field["id"]], "%")? "%":"px");

				default:
					$value = trim(@$_POST[$field["id"]]);
					break;
			}
			update_option("grassblade_tincan_".$field["id"], $value);
		}
    	$grassblade_settings_new = $this->get_params();
    	do_action("grassblade_settings_update", $grassblade_settings_old, $grassblade_settings_new);
	}
	function get_params($id = null) {
		if(!empty($id)) {
			return $this->maybe_migrate_field($id, get_option("grassblade_tincan_".$id));
		}

		$this->define_fields();
		$data = array();
		foreach ($this->fields as $key => $field) {
			if($field["type"] == "html") 
				continue;
			$data[$field["id"]] = $this->maybe_migrate_field($field["id"], get_option("grassblade_tincan_".$field["id"]));

			if($field["id"] == "width") {
				$data[$field["id"]] = empty($data[$field["id"]])? "940px":intVal($data[$field["id"]]).(strpos($data[$field["id"]], "%")? "%":"px");
			}
			else if($field["id"] == "height") {
				$data[$field["id"]] = empty($data[$field["id"]])? "640px":intVal($data[$field["id"]]).(strpos($data[$field["id"]], "%")? "%":"px");
			}
		}
		return $data;
	}
	function maybe_migrate_field($field, $data) {
		if(!empty($data))
			return $data;

		if($field == "dropbox_app_key") {
			$dropbox_app_key = get_option("grassblade_dropbox_app_key");
			if(!empty($dropbox_app_key)) {
				update_option("grassblade_tincan_dropbox_app_key", $dropbox_app_key);
			//	delete_option("grassblade_dropbox_app_key");
			}
			return $dropbox_app_key;
		}
	}
	function admin_menu() {
	    add_menu_page("GrassBlade", "GrassBlade", "manage_options", "grassblade-lrs-settings", null, GRASSBLADE_ICON15, null);
	    add_submenu_page("grassblade-lrs-settings", __("GrassBlade Settings", "grassblade"), __("GrassBlade Settings", "grassblade"),'manage_options','grassblade-lrs-settings', array($this, 'menu_page') );
	}
	function menu_page() {
	    //must check that the user has the required capability 
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.','grassblade') );
	    }

	    if( isset($_POST[ "update_GrassBladeSettings" ]) ) {
	    	$this->set_params();
	        // Put an settings updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'grassblade' ); ?></strong></p></div>
		<?php
	    }
		?>
		<div class=wrap>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<h2><img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', __FILE__); ?>"/>
		<?php _e('GrassBlade Settings', 'grassblade'); ?></h2>
		<div id="grassblade_settings_form">
			<?php
			   echo $this->form();
			?>
			<input type="submit" class="button-primary" name="update_GrassBladeSettings" value="<?php _e('Update Settings', 'grassblade') ?>" />
			<?php _e('Don\'t have an LRS? ','grassblade') ?><a href='http://www.nextsoftwaresolutions.com/learning-record-store' target="_blank"><?php _e(' Find an LRS','grassblade'); ?></a>
		</div>
		</form>
		<br><br>
		<?php include(dirname(__FILE__)."/help.php"); ?>
		</div>
		<?php
	}

} //end of class
global $grassblade_xapi_companion;
$grassblade_xapi_companion = new grassblade_xapi_companion();
$grassblade_xapi_companion->run();

function grassblade_connect() {
	$grassblade_settings = grassblade_settings();	
	global $xapi, $xapi_verbs;
	$xapi = new NSS_XAPI($grassblade_settings["endpoint"], $grassblade_settings["user"], $grassblade_settings["password"]);
	$xapi_verbs = new NSS_XAPI_Verbs();
}
grassblade_connect();

function grassblade_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=grassblade-lrs-settings">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

function grassblade_getverb($verb) {
	global $xapi_verbs;
	return $xapi_verbs->get_verb($verb);
}
function grassblade_getobject($id, $name, $description, $type, $objectType = 'Activity') {
	global $xapi;
	return $xapi->set_object($id, $name, $description, $type, $objectType);
}
function get_statement($atts) {
	global $xapi;
	$shortcode_atts = shortcode_atts(array(
			'statementId' => '',
			'voidedStatementId' => '',
			'email' => '',
			'agent' => '',//Object
			'actor' => '',//Object
			'version' => null,
			'guest' => false,
			'verb' => '', //IRI
			'activity' => '', //IRI
			'object' => '', //Object
			'registration' => '', //UUID
			'show' => 'array',
			//'related_activities' => false,
			//'related_agents' => false,
			'since' => '', //Timestamp: Only Statements stored since the specified timestamp (exclusive) are returned.
			'until' => '', //Timestamp: Only Statements stored at or before the specified timestamp are returned.
			//'format' => 'exact', // ids, exact, canonical
			//'attachments' => false,
			//'ascending' => false,
			'context' => false,
			'authoritative' => false,
			'sparse' => false,
			'limit' => 1,
			), $atts);

	//extract($shortcode_atts);

	$show = $shortcode_atts['show'];
	unset($shortcode_atts['show']);
	
	if(empty($shortcode_atts['email'])) {
		$actor = grassblade_getactor($shortcode_atts['guest'], $shortcode_atts['version']);
	} else {
		$email = $shortcode_atts['email'];
		if( $email != "none" ) {
			$actor = array(
							"objectType" =>	"Agent",
							"mbox" => "mailto:".$email
						);
		}
	}
	if(!empty($actor))
		$shortcode_atts['actor'] = $actor;
	
	if(!empty($shortcode_atts['activity']) && isset($shortcode_atts['version']) && $shortcode_atts['version'] < "1.0") {
		$shortcode_atts['object'] = array(
			"id" => $shortcode_atts['activity'],
			"objectType" => "Activity"
			);
		unset($shortcode_atts['activity']);
	}
	unset($shortcode_atts['guest']);
	unset($shortcode_atts['version']);
	unset($shortcode_atts["email"]);	
	
	foreach($shortcode_atts as $key=>$value) {
		if($value === "")
		unset($shortcode_atts[$key]);
	}	

	$statements = $xapi->GetStatements($shortcode_atts, 1);
	if(empty($statements['statements'][0]))
		return '';
	if($shortcode_atts['limit'] > 1)
	$statements = (array) $statements['statements'];
	else
	$statements = (array) $statements['statements'][0];
	
	if($show == '')
	return (array) $statements;
	if($show == 'array')
	return "<pre>".print_r($statements, true)."</pre>";
	
	$show = explode(".", $show);
	$value = (array) $statements;
	foreach($show as $key) {
		if(!isset($value[$key]))
			return "";
		$value = (is_object($value[$key]))? (array) $value[$key]:$value[$key];
	}
	return print_r($value, true);
}
add_shortcode("get_statement", "get_statement");

// Add settings link on plugin page 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'grassblade_plugin_settings_link' );

add_action('init', 'nss_plugin_updater_activate_grassblade');

function nss_plugin_updater_activate_grassblade()
{
	if(!class_exists('nss_plugin_updater'))
	require_once ('wp_autoupdate.php');
	
	$nss_plugin_updater_plugin_remote_path = 'http://license.nextsoftwaresolutions.com/';
	$nss_plugin_updater_plugin_slug = plugin_basename(__FILE__);

	new nss_plugin_updater ($nss_plugin_updater_plugin_remote_path, $nss_plugin_updater_plugin_slug);
}
	
/*** WYSIWYG Button ***/
/*add_action('init', 'add_grassblade_button');  
function add_grassblade_button() {
   if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') )  
   {  
     add_filter('mce_external_plugins', 'add_grassblade_plugin');  
     add_filter('mce_buttons', 'register_grassblade_button');  
   } 
}*/

function grassblade_debug($msg) {
	if(isset($_GET['debug']) || defined('GB_DEBUG')) {
		$original_log_errors = ini_get('log_errors');
		$original_error_log = ini_get('error_log');
		ini_set('log_errors', true);
		ini_set('error_log', dirname(__FILE__).DIRECTORY_SEPARATOR.'debug.log');
		
		global $processing_id;
		if(empty($processing_id))
		$processing_id	= time();
		
		error_log("[$processing_id] ".print_r($msg, true)); //Comment This line to stop logging debug messages.
		
		ini_set('log_errors', $original_log_errors);
		ini_set('error_log', $original_error_log);		
	}
}

function grassblade_send_statements($statements) {
	global $xapi;
	if(empty($xapi))
		return $false;
	
	return $xapi->SendStatements($statements);
}

/*function register_grassblade_button($buttons) {
   array_push($buttons, "grassblade");  
   return $buttons;
}*/

/*function add_grassblade_plugin($plugin_array) {  
   $plugin_array['grassblade'] = get_bloginfo('wpurl').'/wp-content/plugins/grassblade/js/grassblade_button.min.js';  
   return $plugin_array;  
} */ 
function grassblade_seconds_to_time($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    $return = "";
    // extract days
    $days = floor($inputSeconds / $secondsInADay);
    $return .= empty($days)? "":$days."day";

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);
    $return .= (empty($hours) && empty($days))? "":" ".$hours."hr";

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);
    $return .= (empty($hours) && empty($days) && empty($minutes))? "":" ".$minutes."min";

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);
    $return .= " ".$seconds."sec";

    return trim($return);
}
function grassblade_add_to_content_box() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		add_meta_box( 
			'grassblade_add_to_content_box',
			__( 'xAPI Content', 'grassblade' ),
			'grassblade_add_to_content_box_content',
			$post_type,
			'side',
			'high'
		);
	}
}
function grassblade_add_to_content_save($post_id) {
	$post = get_post( $post_id);
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	return;

	if ( !isset($_POST['grassblade_add_to_content_box_content_nonce']) || !wp_verify_nonce( $_POST['grassblade_add_to_content_box_content_nonce'], plugin_basename( __FILE__ ) ) )
	return;

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
		return;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
		return;
	}

	if(isset($_POST['show_xapi_content']))
		update_post_meta($post_id, "show_xapi_content", $_POST['show_xapi_content']);
}
function grassblade_add_to_content_box_content() {
	global $post;
	wp_nonce_field( plugin_basename( __FILE__ ), 'grassblade_add_to_content_box_content_nonce' );

	if($post->post_type != "gb_xapi_content") {
		global $wpdb;

		$xapi_contents = $wpdb->get_results("SELECT ID, post_title, post_status FROM $wpdb->posts WHERE post_type = 'gb_xapi_content' AND post_status = 'publish' ORDER BY post_title ASC"); // get_posts("post_type=gb_xapi_content&orderby=post_title&posts_per_page=-1");
		$selected_id = get_post_meta($post->ID, "show_xapi_content", true);
		$onChange = apply_filters("grassblade_add_to_content_box_onchange", "alert('".__("You can add the shortcode [grassblade] in the content to change the placement of the content.","grassblade")."');", $post);
		?>
		<div id="grassblade_add_to_content">
		<b><?php _e("Add to Page:", "grassblade"); ?> <a href='<?php echo admin_url('post-new.php?post_type=gb_xapi_content'); ?>'><?php _e("Add New", "grassblade"); ?></a></b><br>
		<select name="show_xapi_content" id="show_xapi_content" onChange="<?php echo $onChange; ?>">
			<option value="0"> -- <?php _e("Select", "grassblade"); ?> -- </option>
			<?php 
				foreach ($xapi_contents as $xapi_content) { 
					$completion_tracking = grassblade_xapi_content::is_completion_tracking_enabled($xapi_content->ID);
					$selected = ($selected_id == $xapi_content->ID)? 'selected="selected"':''; ?>
					<option value="<?php echo $xapi_content->ID; ?>" completion-tracking="<?php echo $completion_tracking; ?>" <?php echo $selected; ?>><?php echo $xapi_content->post_title; ?></option>		
			<?php } ?>
		</select>
		<a href="#" id="grassblade_add_to_content_edit_link" onClick="post_id = document.getElementById('show_xapi_content').value; if(post_id > 0) window.location = '<?php admin_url("post.php"); ?>?action=edit&message=1&post=' + post_id; return false;" style="<?php if(empty($selected_id)) echo 'display:none'; ?>"><?php _e("Edit", "grassblade"); ?></a>
		<br>
		<div>
			<a href="#" onClick="post_id = document.getElementById('show_xapi_content').value; if(post_id > 0) window.location = '<?php admin_url("post.php"); ?>?action=edit&message=1&post=' + post_id; return false;">
			<div id="completion_tracking_enabled" style="display:none;">
				<?php _e("Completion Tracking Enabled.", "grassblade"); ?>
			</div>
			<div id="completion_tracking_disabled" style="display:none;">
				<?php _e("Completion Tracking Disabled.", "grassblade"); ?>
			</div>
			</a>
		</div>
			<?php 
			$current_screen = get_current_screen();
			if( !method_exists($current_screen, "is_block_editor") || !$current_screen->is_block_editor() ) {
			?>
			<div>
				<br>
				<input name="save" type="submit" class="button button-primary button-large" value="Update">
			</div>
			<?php } ?>
		</div>
		<?php
	}
	else
	{
		$completion_tracking = grassblade_xapi_content::is_completion_tracking_enabled($post->ID);
		$xapi_contents = grassblade_get_post_with_content($post->ID);

		if(empty($xapi_contents)) {
			 _e("This xAPI Content is not added to any post/page.", "grassblade");
			 if($completion_tracking) {
			 	echo "<div class='error'>".__("Completion Tracking enabled but xAPI Content not added to any page/post")."</div>";
			 }
		}
		else 
		{
			echo "<b>".__("Added on:", "grassblade")."</b><div id='xapi_posts_list'><ul>";
			foreach($xapi_contents as $xapi_content) {
				echo "<li><a href='".get_edit_post_link($xapi_content->ID)."'>".$xapi_content->post_title."</a></li>";

			}
			echo "</ul></div>";

			if($completion_tracking) { ?>
				<div id="completion_tracking_enabled">
					<?php _e("Completion Tracking Enabled."); ?>
				</div>
			<?php } else { ?>
				<div id="completion_tracking_disabled">
					<?php _e("Completion Tracking Disabled."); ?>
				</div>
			<?php
			}
		}
	}
}
function grassblade_get_post_with_content($content_id) {
	if(empty($content_id) || !is_numeric($content_id)) 
		return array();
	else
	{
		global $wpdb;
        $meta_post_ids 	= $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'show_xapi_content' AND meta_value = '%d'", $content_id));
        $block_post_ids	= $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'show_xapi_content_blocks' AND meta_value = '%d'", $content_id ));

        $post_ids = array_merge($block_post_ids,$meta_post_ids);

		if(empty($post_ids))
			return;

		$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID IN (".implode(",", $post_ids).") AND post_status IN ('publish', 'draft') ORDER BY post_title");
		return $posts;
	}
}
function grassblade_add_to_content_post($content) {
	global $post;
	if(empty($post->ID))
        return $content;
	$selected_id = get_post_meta($post->ID, "show_xapi_content", true);
	$selected_id = apply_filters("grassblade_add_to_content_post", $selected_id, $post, $content);
	if(!empty($selected_id)) {
		if(strpos($content, "[grassblade]") === false)
		$content .= do_shortcode('[grassblade id='.$selected_id."]");
		else
		$content = str_replace("[grassblade]", do_shortcode('[grassblade id='.$selected_id."]"),	$content);
	}
	return $content;
}
function grassblade_file_get_contents_curl($url) {
        $url = str_replace(" ", "%20", $url);
        $url = str_replace("(", "%28", $url);
        $url = str_replace(")", "%29", $url);
        $ch = curl_init();
        $timeout = 5;
        $userAgent = !empty($_SERVER["HTTP_USER_AGENT"])? $_SERVER["HTTP_USER_AGENT"]:"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)";
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        if(!ini_get('safe_mode') && !ini_get('open_basedir'))
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
}
function grassblade_sanitize_filename($file) {
	return preg_replace('/[^A-Za-z0-9\-\_\.]/', '', $file);
}
function gb_ie_version() {
	preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
	if(count($matches)<2){
	  preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
	}

	if (count($matches)>1){
	  //Then we're using IE
	  $version = $matches[1];
	  return $version;
	}

	return 0; 
}

add_action( 'add_meta_boxes', 'grassblade_add_to_content_box');
add_filter( 'the_content', 'grassblade_add_to_content_post', 2, 1);
add_action( 'save_post', 'grassblade_add_to_content_save');
add_action( 'admin_notices', 'grassblade_admin_notice_handler');

function grassblade_error_check() {
	global $post;
    if(!empty($_POST))
    return;

	if( !empty($_GET['page']) && $_GET['page'] == "grassblade-lrs-settings" || !empty($_GET['post_type']) && $_GET['post_type'] == "gb_xapi_content" || !empty($post) && $post->post_type == "gb_xapi_content" ) {
		$site_url = get_site_url(); 
		if(strpos('a'.$site_url, "https://")) {
			$grassblade_settings = grassblade_settings();
			if( strpos('a'.$grassblade_settings["endpoint"], "http://" ) ) {
	    		$grassblade_settings_page = get_admin_url(null,'admin.php?page=grassblade-lrs-settings');
	            grassblade_admin_notice( sprintf("You are using SSL/https for your website, but http for your LRS. Please change the LRS endpoint url to https %s. If you do not have SSL installed on your LRS, you might need to do that first.", "<a href='".$grassblade_settings_page."'>here</a>" ));
			}
		}
	}
}
add_action( 'admin_head', 'grassblade_error_check', 200);
// Display any errors
function grassblade_admin_notice_handler() {

	$errors = get_option('grassblade_admin_errors');

	if($errors) {
		echo '<div class="error"><p>' . $errors . '</p></div>';
		 update_option('grassblade_admin_errors', false);
	}   

}
function grassblade_admin_notice($message, $type = "error") {
	update_option('grassblade_admin_errors', $message);	
}
/*** WYSIWYG Button ***/


/** Plugins Filter for GrassBlade **/
add_filter( 'views_plugins', 'grassblade_plugins');

/**
 * Add 'GrassBlade' tab into views of plugins manage.
 *
 * @since 3.0.0
 *
 * @param array $views
 *
 * @return array
 */
function grassblade_plugins( $views ) {
	global $s;

	$search          = get_grassblade_addons();
	$count_activated = 0;

	if ( $active_plugins = get_option( 'active_plugins' ) ) {
		if ( $search ) {
			foreach ( $search as $k => $v ) {
				if ( in_array( $k, $active_plugins ) ) {
					$count_activated ++;
				}
			}
		}
	}

	if ( $s && false !== stripos( $s, 'grassblade' ) ) {
		$views['grassblade-plugin'] = sprintf( '<a href="%s" class="current">%s <span class="count">(%d/%d)</span></a>', admin_url( 'plugins.php?s=grassblade' ), __( 'GrassBlade', 'grassblade' ), $count_activated, sizeof( $search ) );
	} else {
		$views['grassblade-plugin'] = sprintf( '<a href="%s">%s <span class="count">(%d/%d)</span></a>', admin_url( 'plugins.php?s=grassblade' ), __( 'GrassBlade', 'grassblade' ), $count_activated, sizeof( $search ) );
	}

	return $views;
}

function get_grassblade_addons() {
	$all_plugins = apply_filters( 'all_plugins', get_plugins() );

	return array_filter( $all_plugins, 'grassblade_plugins_search_callback' );
}

/**
 * Callback function for searching plugins have 'grassblade' inside.
 *
 * @since 3.0.0
 *
 * @param array $plugin
 *
 * @return bool
 */
function grassblade_plugins_search_callback( $plugin ) {
	foreach ( $plugin as $value ) {
		if ( is_string( $value ) && false !== stripos( strip_tags( $value ), 'grassblade' ) ) {
			return true;
		}
	}

	return false;
}
/** Plugins Filter for GrassBlade **/

