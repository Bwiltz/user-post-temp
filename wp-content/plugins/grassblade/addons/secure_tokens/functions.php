<?php

add_filter("grassblade_shortcode_defaults", "grassblade_secure_tokens_grassblade_shortcode_defaults", 10, 1);
function grassblade_secure_tokens_grassblade_shortcode_defaults($shortcode_defaults) {
	if(!isset($shortcode_defaults["secure_tokens"])) {
		global $grassblade_xapi_companion;
		$shortcode_defaults["secure_tokens"] = @$grassblade_xapi_companion->grassblade_settings["secure_tokens"];
	}
	return $shortcode_defaults;
}

add_filter("grassblade_xapi_content_fields", "grassblade_secure_tokens_xapi_content_fields", 10, 2);
function grassblade_secure_tokens_xapi_content_fields($xapi_content_fields, $xapi_content) {
	if(!in_array("endpoint", $xapi_content_fields))
		return $xapi_content_fields;

	$xapi_content_fields[] = "secure_tokens";
	return $xapi_content_fields;
}

add_filter("grassblade_shortcode_atts", "grassblade_secure_tokens", 10, 2);
function grassblade_secure_tokens($shortcode_atts, $attr) {
//	echo "<pre>";print_r($shortcode_atts);echo "</pre>";

	if(empty($shortcode_atts["secure_tokens"]))
		return $shortcode_atts;

	extract($shortcode_atts);

	global $grassblade_xapi_companion;
	$grassblade_settings = $grassblade_xapi_companion->grassblade_settings;

	if(empty($shortcode_atts["endpoint"]))
	{
		$shortcode_atts["endpoint"] = $grassblade_settings["endpoint"];
		$shortcode_atts["user"] = $grassblade_settings["user"];
		$shortcode_atts["pass"] = $grassblade_settings["password"];
	}
	if(empty($shortcode_atts["endpoint"]) || empty($shortcode_atts["user"]) || empty($shortcode_atts["pass"]))
		return $shortcode_atts;

	$secure_tokens = $shortcode_atts["secure_tokens"];
	$pass = str_pad(substr($shortcode_atts["pass"], 0, 4), 4, '0') .str_pad($secure_tokens, 3, chr(rand(65,90)), STR_PAD_RIGHT);
	$time = time();
	//$duration = rand(1111111, 9999999);

	if(intval($secure_tokens) & 8) {
		$duration = str_pad(86400, 7, chr(rand(65,90)));
	}

	$pass = $pass.$time.$duration;
	$pass2 = "";
	if(intval($secure_tokens) & 1) {
		if($guest === false)
		$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
		else
		$grassblade_tincan_track_guest = $guest;
		
		$actor = grassblade_getactor($grassblade_tincan_track_guest, $version);
		$actor_id = grassblade_get_actor_id($actor);
		$pass2 = $pass2.$actor_id;
	}
	if(intval($secure_tokens) & 2) {
		$pass2 = $pass2.$shortcode_atts["activity_id"];
	}
	if(intval($secure_tokens) & 4) {
		$pass2 = $pass2.$_SERVER["REMOTE_ADDR"];
	}
	//echo "<br>".$shortcode_atts["pass"].$pass.$pass2."<Br>";
	$pass2 = sha1($shortcode_atts["pass"].$pass.$pass2);
	$shortcode_atts["pass"] = $pass2.$pass;

	//gb_secure_tokens_decode
	return $shortcode_atts;
}

function grassblade_generate_secure_token($secure_tokens, $api_pass = null, $actor_id = null, $activity_id = null) {
	//$secure_tokens = 8 (Duration) + 4 (IP) + 2 (Activity) + 1 (User)
	//Low = 8 (Duration) + 1 (User) = 9
	//Medium = 8 (Duration) + 2 (Activity) + 1 (User) = 11
	//High = 8 (Duration) + 4 (IP) + 2 (Activity) + 1 (User) = 15

	if(empty($secure_tokens))
		return;

	global $grassblade_xapi_companion;
	$grassblade_settings = $grassblade_xapi_companion->grassblade_settings;

	if(empty($api_pass))
	{
		$api_pass = $grassblade_settings["password"];
	}

	if(empty($api_pass))
		return;

	$pass = str_pad(substr($api_pass, 0, 4), 4, '0') .str_pad($secure_tokens, 3, chr(rand(65,90)), STR_PAD_RIGHT);
	$time = time();
	//$duration = rand(1111111, 9999999);

	if(intval($secure_tokens) & 8) {
		$duration = str_pad(86400, 7, chr(rand(65,90)));
	}

	$pass = $pass.$time.$duration;
	$pass2 = "";
	if(intval($secure_tokens) & 1) {
		if($guest === false)
		$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
		else
		$grassblade_tincan_track_guest = $guest;
		
		if(empty($actor_id)) {
			$actor = grassblade_getactor($grassblade_tincan_track_guest, $version);
			$actor_id = grassblade_get_actor_id($actor);
		}
		$pass2 = $pass2.$actor_id;
	}
	if(intval($secure_tokens) & 2 && !empty($activity_id)) {
		$pass2 = $pass2.$activity_id;
	}
	if(intval($secure_tokens) & 4) {
		$pass2 = $pass2.$_SERVER["REMOTE_ADDR"];
	}
	//echo "<br>".$shortcode_atts["pass"].$pass.$pass2."<Br>";
	$pass2 = sha1($api_pass.$pass.$pass2);
	return $pass2.$pass;
}



