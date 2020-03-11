<?php
add_shortcode("gb_leaderboard", "gb_leaderboard");
function gb_leaderboard($attr) {
	global $wpdb;
	$shortcode_atts = shortcode_atts ( array(
 		'id' => null,
 		'allow' => 'all',
 		'score' => 'score',
 		'class'	=> '',
 		'limit' => 20
	), $attr);
	extract($shortcode_atts);
	$score = (strtolower(trim($score)) == "score")? "score":"percentage"; 
	$limit = intval($limit);

	$allow = explode(",", $allow);
	foreach ($allow as  $role) {
		if($role == "all" || current_user_can($role))
		{
			$has_permissions = true;
			break;
		}
	}
	if(!empty($has_permissions)) {
		if(empty($id))
		{
			global $post;
			$id = $post->ID;
		}

		$p = get_post($id);
		if(empty($p->ID))
			return '';
		$ids = array();
		if($p->post_type == "gb_xapi_content") {			
			$ids[] = $id;
		}
		else 
		{
			$show_xapi_content = get_post_meta($id, "show_xapi_content", true);
			if(!empty($show_xapi_content))
				$ids[] = $show_xapi_content;
		}
		$ids = apply_filters("gb_leaderboard_ids", $ids, $p, $shortcode_atts);
		if(empty($ids))
			return;
		$leaderboard = $wpdb->get_results("SELECT user_id, ROUND(sum($score),2) as total, sum(timespent) as total_timespent, timestamp, status FROM
				(SELECT * FROM (SELECT * FROM `".$wpdb->prefix."grassblade_completions` WHERE content_id IN (".implode(",", $ids).") ORDER BY $score DESC) oc 
 				GROUP BY user_id, content_id) uc 
				GROUP BY user_id ORDER BY total DESC, total_timespent DESC LIMIT ".$limit, ARRAY_A);

		return "<div class='".$class." gb_leaderboard'>" . grassblade_leaderboard_table($leaderboard) . "</div>";
	}
}
function grassblade_leaderboard_table($leaderboard) {
	if(empty($leaderboard))
		return "";
	$i = 1;
	$leaderboard_new = array();
	foreach ($leaderboard as $key => $value) {
                $user = get_user_by("id", $value["user_id"]);
                if(empty($user->ID))
                continue;
		$row = array();
		$row[__("Rank","grassblade")] = $i++;
		$row[__("Name","grassblade")] = $user->display_name;
		$row[__("Total","grassblade")] = $value["total"];
		$row[__("Total Timespent", "grassblade")] = grassblade_seconds_to_time($value["total_timespent"]);
		$row[__("Status", "grassblade")] = $value["status"];
		$row[__("Date", "grassblade")] = date( get_option( 'date_format' ) . " " . get_option( 'time_format' ), strtotime($value["timestamp"]));
		$leaderboard_new[$key] = $row;
	}
	$leaderboard_new = apply_filters("gb_leaderboard_array", $leaderboard_new, $leaderboard);
	include_once(dirname(__FILE__)."/../nss_arraytotable.class.php");
	$ArrayToTable = new NSS_ArrayToTable($leaderboard_new);
	return $ArrayToTable->get();
}
