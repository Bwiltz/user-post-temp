<?php

add_action("init", "grassblade_badgeos_init", 1);

add_action("wp", "grassblade_badgeos_compatibility_code2", 10);

function grassblade_badgeos_init() {
	if(!class_exists('BadgeOS'))
		return;

	add_action("badgeos_award_achievement", "grassblade_badgeos_awarded_achievement", 10, 2); //
	add_filter("grassblade_user_score", "grassblade_badgeos_add_points_to_user_score", 10, 3);
	
	if(!defined('DISABLE_GRASSBLADE_BADGEOS_COMPATIBILITY_MODE'))
		grassblade_badgeos_compatibility_code();
}

function grassblade_badgeos_compatibility_code() {
	remove_action("grassblade_completed", "grassblade_learndash_content_completed", 10, 3); //
}

function grassblade_badgeos_compatibility_code2() {
	if(!class_exists('BadgeOS'))
		return;

	global $post;
	if(empty($post->ID)) 
		return;

	$completion_type = grassblade_xapi_content::post_completion_type($post->ID);

	if($completion_type == 'hide_button') {
		remove_action('wp_head','grassblade_learndash_control_mark_complete_button');
		remove_action('wp_print_scripts','grassblade_learndash_control_mark_complete_button');
	}
}

function grassblade_badgeos_awarded_achievement($user_id, $achievement_id) {
//	define("GB_DEBUG", true);
//	grassblade_debug("[grassblade_badgeos_awarded_achievement] $user_id : $achievement_id");
	$user = get_user_by("id", $user_id);
	if(empty($user->ID))
		return;

	$parent_achievement = badgeos_get_parent_of_achievement($achievement_id);

	$actor = grassblade_getactor(false, null, $user);

	$achievement = get_post($achievement_id);
	$achievement_url_id = grassblade_post_activityid($achievement_id);

	$verb = array(
				"id" => "http://nextsoftwaresolutions.com/xapi/verbs/earned",
				"display" => array("en-US" => "earned")
			);

	$object = grassblade_getobject($achievement_url_id, $achievement->post_title, $achievement->post_title, "http://nextsoftwaresolutions.com/xapi/activities/badgeos/".$achievement->post_type);

    $statement =    array(
                            "actor" => $actor,
                            "verb"  => $verb,
                            "object" => $object,
                            );
    if(!empty($parent_achievement)) {
    	$parent_url_id = grassblade_post_activityid($parent_achievement->ID);
		$parent = grassblade_getobject($parent_url_id, $parent_achievement->post_title, $parent_achievement->post_title, "http://nextsoftwaresolutions.com/xapi/activities/badgeos/".$parent_achievement->post_type);
    	$statement["context"] = array("contextActivities" => array("parent" => $parent));
    }

    $statements = array($statement);
    grassblade_send_statements($statements);
}

function grassblade_badgeos_add_points_to_user_score($return, $attr, $shortcode_atts) {
	if(empty($shortcode_atts["add"]) || $shortcode_atts["add"] != "badgeos_points" || !function_exists('badgeos_get_users_points')) 
		return $return;

	if(!empty($shortcode_atts["user_id"]))
		$user_id = $shortcode_atts["user_id"];
	else
	{
		$user = wp_get_current_user();
		$user_id = $user->ID;
	}

	if(empty($user_id))
		return $return;

	return ($return + number_format(badgeos_get_users_points($shortcode_atts["user_id"])));
}