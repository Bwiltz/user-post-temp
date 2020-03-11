<?php
define('GBL_LEARNDASH_PLUGIN_FILE', 'sfwd-lms/sfwd_lms.php');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if(is_plugin_active(GBL_LEARNDASH_PLUGIN_FILE))
{
	add_action('admin_menu', 'grassblade_learndash_menu', 1100);
	add_action('learndash_assignment_uploaded', 'grassblade_learndash_assignment_uploaded', 10, 2);

	if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX )
	{
		// Load Quiz Tracking module only when Ajax is Running
		include_once( dirname(__FILE__).'/quiz/index.php' );
		new grassblade_learndash_quiz();
	}
}

function grassblade_learndash_menu() {
	add_submenu_page("edit.php?post_type=sfwd-courses", __("TinCan Settings", "grassblade"), __("TinCan Settings", "grassblade"),'manage_options','admin.php?page=grassblade-lrs-settings', 'grassblade_menu_page');
	add_submenu_page("edit.php?post_type=sfwd-courses", __("PageViews Settings", "grassblade"),  __("PageViews Settings", "grassblade"),'manage_options','admin.php?page=pageviews-settings', 'grassblade_pageviews_menupage');
}

function grassblade_learndash_admin_tabs_on_page($admin_tabs_on_page, $admin_tabs, $current_page_id) {
	if(empty($admin_tabs_on_page["toplevel_page_grassblade-lrs-settings"]) || !count($admin_tabs_on_page["toplevel_page_grassblade-lrs-settings"]))
		$admin_tabs_on_page["toplevel_page_grassblade-lrs-settings"] = array();
	
	$admin_tabs_on_page["toplevel_page_grassblade-lrs-settings"] = array_merge($admin_tabs_on_page["sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses"], (array) $admin_tabs_on_page["toplevel_page_grassblade-lrs-settings"]);

	foreach ($admin_tabs as $key => $value) {
		if($value["id"] == $current_page_id && $value["menu_link"] == "edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses")
		{
			$admin_tabs_on_page[$current_page_id][] = "grassblade-lrs-settings";
			return $admin_tabs_on_page;
		}
	}

	return $admin_tabs_on_page;
}
add_filter("learndash_admin_tabs_on_page", "grassblade_learndash_admin_tabs_on_page", 3, 3);

function grassblade_learndash_submenu($add_submenu) {
	$add_submenu["grassblade"] = array(
									"name" 	=>	__('One Click Upload', "grassblade"),
									"cap"	=>	"manage_options",
									"link"	=> 'edit.php?post_type=gb_xapi_content'
									);
	return $add_submenu;
}
add_filter("learndash_submenu", "grassblade_learndash_submenu", 1, 1 );
function grassblade_learndash_admin_tabs($admin_tabs) {
	$admin_tabs["grassblade"] = array(
									"link"	=>	'edit.php?post_type=gb_xapi_content',
									"name"	=>	__('One Click Upload', "grassblade"),
									"id"	=>	"edit-gb_xapi_content",
									"menu_link"	=> 	"edit.php?post_type=gb_xapi_content",
								);
	$admin_tabs["grassblade-lrs-settings"] = array(
									"link"	=>	'admin.php?page=grassblade-lrs-settings',
									"name"	=>	__("TinCan Settings","grassblade"),
									"id"	=>	"toplevel_page_grassblade-lrs-settings",
									"menu_link"	=> 	"edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses",
								);
	return $admin_tabs;
}
add_filter("learndash_admin_tabs", "grassblade_learndash_admin_tabs", 1, 1);
function grassblade_learndash_mark_lesson_complete_if_children_complete($lesson_id, $user_id, $course_id = null) {
	$lesson = get_post($lesson_id);
	//$user_course_progress = get_user_meta($user_id, '_sfwd-course_progress', true);
	$quizzes = learndash_get_lesson_quiz_list($lesson, $user_id, $course_id);
	if(!empty($quizzes) && count($quizzes))
	{
		foreach ($quizzes as $quiz) {
			if($quiz["status"] != "completed")
				return false;
		}

	}
	else
	{
		$topics = learndash_topic_dots($lesson_id,false,"array",$user_id, $course_id);
		if(!empty($topics) && count($topics))
		{
			foreach ($topics as $topic) {
				if(empty($topic->completed))
					return false;
			}
		}
	}
	if(!empty($course_id)) {
		$ret = grassblade_learndash_process_and_verify_mark_complete($user_id, $lesson_id, false, $course_id);
		learndash_course_status($course_id, $user_id);				
	}
	else 
	{
		$ret = grassblade_learndash_process_and_verify_mark_complete($user_id, $lesson_id);
		$course_id = grassblade_learndash_get_course_id($lesson_id, $user_id);
		learndash_course_status($course_id, $user_id);				
	}
	return $ret;
}
function grassblade_learndash_lesson_completed($data) {

	grassblade_debug('grassblade_learndash_lesson_completed');
	//grassblade_debug($data);
	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
    $grassblade_tincan_user = $grassblade_settings["user"];
    $grassblade_tincan_password = $grassblade_settings["password"];
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

	$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
	$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $data["user"]);

	if(empty($actor))
	{
		grassblade_debug("No Actor. Shutting Down.");
		return;
	}
	$course = $data['course'];
	$lesson = $data['lesson'];
	$progress = $data['progress'];
	
	$course_title = $course->post_title;
	$course_url = grassblade_post_activityid($course->ID);
	$lesson_title = $lesson->post_title;
	$lesson_url = grassblade_post_activityid($lesson->ID);
	
	if(!empty($course->ID) &&!empty($data['progress'][$course->ID]['completed']) && $data['progress'][$course->ID]['completed'] == 1) {
		//Course Attempted
		$xapi->set_verb('attempted');
		$xapi->set_actor_by_object($actor);	
		$xapi->set_parent($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$xapi->set_object($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$statement = $xapi->build_statement();
		//grassblade_debug($statement);
		$xapi->new_statement();
			
	}
	
	//Lesson Attempted
	$xapi->set_verb('attempted');
	$xapi->set_actor_by_object($actor);	
	$xapi->set_parent($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_object($lesson_url, $lesson_title, $lesson_title, 'http://adlnet.gov/expapi/activities/lesson','Activity');
	$statement = $xapi->build_statement();
	//grassblade_debug($statement);
	$xapi->new_statement();
	
	//Lesson Completed
	$xapi->set_verb('completed');
	$xapi->set_actor_by_object($actor);	
	$xapi->set_parent($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_object($lesson_url, $lesson_title, $lesson_title, 'http://adlnet.gov/expapi/activities/lesson','Activity');
	$result = array(
				'completion' => true
				);	
	$xapi->set_result_by_object($result);

	$statement = $xapi->build_statement();
	//grassblade_debug($statement);
	$xapi->new_statement();
	
	foreach($xapi->statements as $statement)
	{
		$ret = $xapi->SendStatements(array($statement));
	}	
}
function grassblade_learndash_topic_completed($data) {

	grassblade_debug('grassblade_learndash_topic_completed');
	//grassblade_debug($data);
	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
    $grassblade_tincan_user = $grassblade_settings["user"];
    $grassblade_tincan_password = $grassblade_settings["password"];
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

	$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
	$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $data["user"]);

	if(empty($actor))
	{
		grassblade_debug("No Actor. Shutting Down.");
		return;
	}
	$course = $data['course'];
	$topic = $data['topic'];
	$progress = $data['progress'];
	
	$lesson_id = grassblade_learndash_course_get_single_parent_step($course->ID, $topic->ID, "sfwd-lessons");
	if(!empty($lesson_id))
	{
		$lesson = get_post($lesson_id);
	}

	$course_title = $course->post_title;
	$course_url = grassblade_post_activityid($course->ID);
	$topic_title = $topic->post_title;
	$topic_url = grassblade_post_activityid($topic->ID);
	
	if(!empty($lesson->ID))
	{
		$parent_title 	= $lesson->post_title;
		$parent_url 	= grassblade_post_activityid($lesson->ID);
		$parent_type	= 'lesson';
	}
	else
	{
		$parent_title	= $course_title;
		$parent_url		= $course_url;
		$parent_type	= 'course';
	}

	if(!empty($course->ID) &&!empty($data['progress'][$course->ID]['completed']) && $data['progress'][$course->ID]['completed'] == 1) {
		//Course Attempted
		$xapi->set_verb('attempted');
		$xapi->set_actor_by_object($actor);	
		$xapi->set_parent($parent_url, $parent_title, $parent_title, 'http://adlnet.gov/expapi/activities/'.$parent_type,'Activity');
		$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$xapi->set_object($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$statement = $xapi->build_statement();
		//grassblade_debug($statement);
		$xapi->new_statement();
			
	}
	
	//topic Attempted
	$xapi->set_verb('attempted');
	$xapi->set_actor_by_object($actor);	
	$xapi->set_parent($parent_url, $parent_title, $parent_title, 'http://adlnet.gov/expapi/activities/'.$parent_type,'Activity');
	$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_object($topic_url, $topic_title, $topic_title, 'http://adlnet.gov/expapi/activities/topic','Activity');
	$statement = $xapi->build_statement();
	//grassblade_debug($statement);
	$xapi->new_statement();
	
	//topic Completed
	$xapi->set_verb('completed');
	$xapi->set_actor_by_object($actor);	
	$xapi->set_parent($parent_url, $parent_title, $parent_title, 'http://adlnet.gov/expapi/activities/'.$parent_type,'Activity');
	$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_object($topic_url, $topic_title, $topic_title, 'http://adlnet.gov/expapi/activities/topic','Activity');
	$result = array(
				'completion' => true
				);	
	$xapi->set_result_by_object($result);

	$statement = $xapi->build_statement();
	//grassblade_debug($statement);
	$xapi->new_statement();
	
	foreach($xapi->statements as $statement)
	{
		$ret = $xapi->SendStatements(array($statement));
	}	
}
function grassblade_learndash_course_completed($data) {
	grassblade_debug('grassblade_learndash_course_completed');
	//grassblade_debug($data);
	
	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
    $grassblade_tincan_user = $grassblade_settings["user"];
    $grassblade_tincan_password = $grassblade_settings["password"];
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

	$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
	$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $data["user"]);

	if(empty($actor))
	{
		grassblade_debug("No Actor. Shutting Down.");
		return;
	}
	$course = $data['course'];
	$progress = $data['progress'];
	$course_title = $course->post_title;
	$course_url = grassblade_post_activityid($course->ID);	
	//Lesson Completed
	$xapi->set_verb('completed');
	$xapi->set_actor_by_object($actor);	
	$xapi->set_parent($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$xapi->set_object($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	$result = array(
				'completion' => true
				);	
	$xapi->set_result_by_object($result);	
	$statement = $xapi->build_statement();
	grassblade_debug($statement);
	$xapi->new_statement();	
	foreach($xapi->statements as $statement)
	{
		$ret = $xapi->SendStatements(array($statement));
	}		
}
function grassblade_learndash_quiz_completed($data, $user = null) {
	//define('GB_DEBUG', true);
	grassblade_debug('grassblade_learndash_quiz_completed');
	grassblade_debug($data);

	//if(!empty($data["statement_id"]))
	//	return;

	$grassblade_settings = grassblade_settings();

    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
    $grassblade_tincan_user = $grassblade_settings["user"];
    $grassblade_tincan_password = $grassblade_settings["password"];
	$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
	
	$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
	$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

	if(empty($actor))
	{
		grassblade_debug("No Actor. Shutting Down.");
		return;
	}
	$course = $data['course'];
	$quiz = $data['quiz'];
	$pass = !empty($data['pass'])? true:false;
	$score = $data['score']*1;

	if(empty($course))
	{
		$course_id = grassblade_learndash_get_course_id($quiz->ID);
		if(!empty($course_id))
			$course = get_post($course_id);
	}

	if(isset($data["percentage"]))
	$score_scaled = number_format( $data["percentage"]/100, 2);
	if(isset($data["timespent"]))
	$duration = "PT".$data["timespent"]."S";

	$course_title = $course->post_title;
	$course_url = grassblade_post_activityid($course->ID);
	$quiz_title = $quiz->post_title;
	$quiz_url = grassblade_post_activityid($quiz->ID);
	

	$has_xapi_content = grassblade_xapi_content::get_post_xapi_contents($quiz->ID);
	if(!empty($has_xapi_content)) {
		//Quiz Attempted
		$xapi->set_verb('attempted');
		$xapi->set_actor_by_object($actor);	
		if(!empty($course_url)) {
			$xapi->set_parent($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
			$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		}
		$xapi->set_object($quiz_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment','Activity');
		$statement = $xapi->build_statement();
		grassblade_debug($statement);
		$xapi->new_statement();
	}
	else
	{
		//LearnDash Quiz Passed/Failed
		if($pass)
		$xapi->set_verb('passed');
		else
		$xapi->set_verb('failed');
		$xapi->set_actor_by_object($actor);	
		$xapi->set_parent($quiz_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment','Activity');
		$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$xapi->set_object($quiz_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment','Activity');
		$result = array(
					'completion' => true,
					'success' => $pass,
					'score' => array('min' => 0, 'raw' => $score)
					);
		if(isset($data["points"]))
			$result["score"]["raw"] = $data["points"];
		if(isset($data["total_points"]))
			$result["score"]["max"] = $data["total_points"];

		if(isset($score_scaled))
			$result["score"]["scaled"] = $score_scaled;
		
		if(isset($duration))
			$result["duration"] = $duration;

		$xapi->set_result_by_object($result);

		$statement = $xapi->build_statement();
		grassblade_debug($statement);
		$xapi->new_statement();
	}
	//Quiz Completed
	$xapi->set_verb('completed');
	$xapi->set_actor_by_object($actor);	
	if(!empty($course_url)) {
		$xapi->set_parent($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
		$xapi->set_grouping($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	}
	$xapi->set_object($quiz_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment','Activity');
	$result = array(
				'completion' => true,
				'success' => $pass,
				'score' => array('raw' => $score)
				);	
	if(isset($data["points"]))
		$result["score"]["raw"] = $data["points"];
	
	if(isset($data["total_points"]))
		$result["score"]["max"] = $data["total_points"];
		
	if(isset($score_scaled))
		$result["score"]["scaled"] = $score_scaled;
	
	if(isset($duration))
		$result["duration"] = $duration;

	$xapi->set_result_by_object($result);

	$statement = $xapi->build_statement();
	grassblade_debug($statement);
	$xapi->new_statement();
	
	foreach($xapi->statements as $statement)
	{
		$ret = $xapi->SendStatements(array($statement));
		grassblade_debug($ret);
	}	
}
add_action('learndash_lesson_completed', 'grassblade_learndash_lesson_completed', 1, 1);
add_action('learndash_topic_completed', 'grassblade_learndash_topic_completed', 1, 1);
add_action('learndash_course_completed', 'grassblade_learndash_course_completed', 1, 1);
add_action('learndash_quiz_completed', 'grassblade_learndash_quiz_completed', 1, 2);

function grassblade_learndash_process_mark_complete($return, $post, $user) {

	if(empty($post->ID) || empty($user->ID))
		return false;

	$user_id = $user->ID;
	$completed = grassblade_xapi_content::post_contents_completed($post->ID, $user_id);

	if(is_bool($completed) && $completed) //No content
		return $return;

	if(empty($completed) || count($completed) == 0)	//Incomplete
		return false;

	if($post->post_type == "sfwd-quiz" && is_array($completed)) {
        $completed = array_pop($completed);
		grassblade_learndash_quiz_completion($post, $user, $completed);
	}

	return true;
}
add_filter("learndash_process_mark_complete", "grassblade_learndash_process_mark_complete", 1, 3);

/*
add_filter("the_content", function($content) {
	global $post;
	$all_content_ids = grassblade_xapi_content::get_post_xapi_contents($post->ID, false);
	foreach ($all_content_ids as $key => $value) {
		$p = get_post($value);
		$all_content_ids[$key] = $p->post_title;
	}
	$test = print_r($all_content_ids, true);
	$test .= print_r( array_pop($all_content_ids), true);
	$content .= "<pre>".$test."</pre>";
	return $content;
});
*/

function grassblade_learndash_content_completed($statement, $content_id, $user) {
	$user_id = $user->ID;
	$xapi_content = get_post_meta($content_id, "xapi_content", true);

	if(empty($xapi_content["completion_tracking"])) {
		grassblade_show_trigger_debug_messages( "\nCompletion tracking not enabled. " );
		return true;
	}
	
	global $wpdb;

    $statement_array = json_decode($statement);

    $posts = grassblade_get_post_with_content($content_id);
    
	foreach ($posts as $post) {
		$post_id = $post->ID;

		if($post->post_status != "publish")
			continue;

		$course_ids = grassblade_learndash_get_course_ids($post_id, true, $user_id);

		$has_access = false;
		foreach ($course_ids as $course_id => $course_name) {
			if(!$has_access && ld_course_check_user_access($course_id, $user_id)) {
				$has_access = true;
			}
		}
		if(!$has_access) {
			grassblade_show_trigger_debug_messages(  " User: ".$user_id." doesn't have access to ".$post_id );
			continue;
		}


		$completed = grassblade_xapi_content::post_contents_completed($post->ID,$user_id);

		if($post->post_type == "sfwd-quiz") {
			$quiz_id = $post->ID;

			if(!empty($statement_array->{"object"}->definition) && !empty($statement_array->{"object"}->definition->type) && $statement_array->{"object"}->definition->type == "http://adlnet.gov/expapi/activities/assessment")
				continue;

			$all_content_ids = grassblade_xapi_content::get_post_xapi_contents($quiz_id, $with_completion_tracking_enabled_only = true);
			
			if(empty($all_content_ids) || count($all_content_ids) == 0)
				continue;

			if(count($all_content_ids) == 1) 	//Don't worry if only one content on Quiz.
			{
				grassblade_learndash_quiz_completion($post, $user, $statement);
			}
			else
			{ 	//When multiple content on quiz. Last content is primary quiz reporting content.
			  	//

				$ld_quiz_completed = !learndash_is_quiz_notcomplete($user_id, array($quiz_id => 1));
				$last_content_id = array_pop($all_content_ids);
				$same_content_id = ($last_content_id == $content_id);
				$last_content_statement = (is_array($completed) && count($completed) > 0)? array_pop($completed):false;

				if($same_content_id) { 
					if($last_content_statement) //All content completed && on primary content, no problem in reporting score.
					grassblade_learndash_quiz_completion($post, $user, $statement);
					else
					grassblade_learndash_quiz_completion($post, $user, $statement, $reporting_condition = array("pass" => 0));
				}
				else 
				{
					if(!$ld_quiz_completed && $last_content_statement)
					grassblade_learndash_quiz_completion($post, $user, $last_content_statement);
					else
					continue;				
				}
			}
		}

		if(empty($completed))
			continue;
		
		if( function_exists('learndash_get_courses_for_step') && LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) {
			$courses = learndash_get_courses_for_step($post->ID, true);
			grassblade_show_trigger_debug_messages($courses);
			foreach ($courses as $courseid => $course_name) {
				if(sfwd_lms_has_access($courseid, $user->ID)) {
					grassblade_show_trigger_debug_messages(  "\n | user_id = ".$user->ID. " post_id = ".$post->ID. " course id = ".$courseid );

					if($post->post_type == "sfwd-lessons" || $post->post_type == "sfwd-topic")
					grassblade_show_trigger_debug_messages(  " => grassblade_learndash_mark_lesson_complete_if_children_complete: ".grassblade_learndash_mark_lesson_complete_if_children_complete($post->ID, $user->ID, $courseid) );
					else
					grassblade_show_trigger_debug_messages(  " => learndash_process_mark_complete: ".grassblade_learndash_process_and_verify_mark_complete($user->ID, $post->ID, false, $courseid) );

					$lesson_id = grassblade_learndash_course_get_single_parent_step($courseid, $post->ID); 
					$lesson_post = get_post($lesson_id);
					if(!empty($lesson_post->ID)) {
						grassblade_show_trigger_debug_messages(  " | user_id = ".$user->ID." lesson_id (".$lesson_post->post_type.") = ".$lesson_id." course id = ".$courseid." => grassblade_learndash_mark_lesson_complete_if_children_complete = ".grassblade_learndash_mark_lesson_complete_if_children_complete($lesson_id, $user->ID, $courseid) );

						if($lesson_post->post_type == "sfwd-topic") {
							$lesson_id = grassblade_learndash_course_get_single_parent_step($courseid, $lesson_post->ID); 
							if(!empty($lesson_id))
							grassblade_show_trigger_debug_messages(  " >> user_id = ".$user->ID." lesson_id = ".$lesson_id." course id = ".$courseid." => grassblade_learndash_mark_lesson_complete_if_children_complete = ".grassblade_learndash_mark_lesson_complete_if_children_complete($lesson_id, $user->ID, $courseid) );
						}
					}
				}
				else
					grassblade_show_trigger_debug_messages(  " | user_id: ".$user->ID. " doesn't have access to :".$courseid );
			}
		}
		else 
		{
			grassblade_show_trigger_debug_messages(  "\n: user_id = ".$user->ID. " post_id = ".$post->ID );
			
			if($post->post_type == "sfwd-lessons" || $post->post_type == "sfwd-topic")
			grassblade_show_trigger_debug_messages(  " => grassblade_learndash_mark_lesson_complete_if_children_complete: ".grassblade_learndash_mark_lesson_complete_if_children_complete($post->ID, $user->ID) );
			else
			grassblade_show_trigger_debug_messages(  " => grassblade_learndash_process_and_verify_mark_complete: ".grassblade_learndash_process_and_verify_mark_complete($user->ID, $post->ID) );
			
			$lesson_id = grassblade_learndash_course_get_single_parent_step(null, $post->ID); 
			$lesson_post = get_post($lesson_id);
			if(!empty($lesson_post->ID) && defined("LEARNDASH_VERSION") && version_compare(LEARNDASH_VERSION, "2.0.5.3.", ">=")) {
				$r = " | lesson (".$lesson_post->post_type.") = ".$lesson_id." user_id = ".$user->ID." grassblade_learndash_mark_lesson_complete_if_children_complete: ".grassblade_learndash_mark_lesson_complete_if_children_complete($lesson_id, $user->ID);
				grassblade_show_trigger_debug_messages(  $r );

				if($lesson_post->post_type == "sfwd-topic") {
					$lesson_id = grassblade_learndash_course_get_single_parent_step(null, $lesson_post->ID); 
					if(!empty($lesson_id)) {
						$r = " | lesson = ".$lesson_id." user_id = ".$user->ID." grassblade_learndash_mark_lesson_complete_if_children_complete: ".grassblade_learndash_mark_lesson_complete_if_children_complete($lesson_id, $user->ID);
						grassblade_show_trigger_debug_messages(  $r );
					}
				}
			}
		}

	}
}
function grassblade_learndash_course_get_single_parent_step($course_id, $step_id, $post_type = null) {
	if(function_exists('learndash_course_get_single_parent_step')) {

		if(empty($course_id))
			$course_id = learndash_get_course_id( $step_id );

		if(empty($course_id)) {
			$courses = grassblade_learndash_get_course_ids($step_id);
			$course_id = key($courses);
		}

		return learndash_course_get_single_parent_step($course_id, $step_id, $post_type);
	}
	if($post_type == "sfwd-lessons" || $post_type == "sfwd-topic")
		return learndash_get_setting($step_id, "lesson");

	if($post_type == "sfwd-courses")
		return learndash_get_course_id($step_id);
}
function grassblade_learndash_get_course_id($post_id, $user_id = null) {
	$courses = grassblade_learndash_get_course_ids($post_id, true, $user_id);
	if(empty($courses) || !is_array($courses))
		return false;

	if(empty($user_id)) {
		return key($courses); //Return first course id
	}

	foreach ($courses as $course_id => $course) {
		$user_has_access = ld_course_check_user_access($course_id, $user_id);
		if( $user_has_access )
			return $course_id;
	}
	return false;
}
function grassblade_learndash_get_course_ids($post_id, $return_flat_array = false, $user_id = null) {
	if(!function_exists('learndash_get_course_id'))
		return array();

	if( function_exists('learndash_get_courses_for_step') && LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) {
		$courses = learndash_get_courses_for_step($post_id, true);
		if(empty($return_flat_array)) {
			foreach ($courses as $course_id => $value) {
				$courses[$course_id]	= get_post($course_id);
			}
		}
	}
	else 
	{
		$course_id = learndash_get_course_id($post_id);
		$course = get_post($course_id);

		if($return_flat_array)
			$courses = array($course_id => $course->post_title);
		else
			$courses = array($course_id => $course);
	}

	if(empty($user_id)) {
		return $courses; //Return all courses
	}

	foreach ($courses as $course_id => $course) {
		$user_has_access = ld_course_check_user_access($course_id, $user_id);
		if(!$user_has_access)
			unset($courses[$course_id]);
	}
	return $courses; //Return only user accessed courses
}
//add_action("init", "grassblade_learndash_content_completed");
add_action("grassblade_completed", "grassblade_learndash_content_completed", 10, 3);

function grassblade_learndash_quiz_completion($post, $user, $statement, $reporting_condition = array()) {
	grassblade_show_trigger_debug_messages(  " grassblade_learndash_quiz_completion " );
	$user_id = $user->ID;
	$quiz_id = $post_id = $post->ID;
	
	if($post->post_type == "sfwd-quiz" && !empty($statement)) {
		$statement = json_decode($statement);

		if(empty($statement->id)) {
			grassblade_show_trigger_debug_messages("no statement");
			return;
		}

		$result = @$statement->result;

		$usermeta = get_user_meta( $user->ID, '_sfwd-quizzes', true );
		$usermeta = maybe_unserialize( $usermeta );
		if ( !is_array( $usermeta ) ) $usermeta = Array();
		
		foreach($usermeta as $quiz_data) {
			if(!empty($quiz_data["statement_id"]) && $quiz_data["statement_id"] == @$statement->id && $quiz_data["quiz"] == $quiz_id) {
				grassblade_show_trigger_debug_messages( " => already completed " );
				return;
			}
		}

		$score = isset($statement->result->score->raw)? $statement->result->score->raw:(!empty($statement->result->score->scaled)? $statement->result->score->scaled*100:0);
		$percentage = isset($statement->result->score->scaled)? $statement->result->score->scaled*100:((!empty($statement->result->score->max) && isset($statement->result->score->raw))? $statement->result->score->raw*100/($statement->result->score->max - @$statement->result->score->min):100);
		$percentage = round($percentage, 2);

		$timespent = isset($statement->result->duration)? grassblade_duration_to_seconds($statement->result->duration):null;
		$count = 1;
		
		$quiz = get_post_meta($quiz_id, '_sfwd-quiz', true);
		$passingpercentage = intVal($quiz['sfwd-quiz_passingpercentage']);
		$pass = ($percentage >= $passingpercentage)? 1:0;

		if(!empty($reporting_condition) && isset($reporting_condition["pass"]) && $reporting_condition["pass"] != $pass ) {
			grassblade_show_trigger_debug_messages( " => criterion not met, ".print_r($reporting_condition, true) );
			return;
		}

		$quiz = get_post($quiz_id);
		$course_ids = grassblade_learndash_get_course_ids($quiz_id, true, $user_id);
		foreach ($course_ids as $course_id => $course_title) {
	
			$quizdata = array( "statement_id" => @$statement->id, "registration" => @$statement->registration, "course" => $course_id, "quiz" => $quiz_id, "quiz_title" => $quiz->post_title, "score" => $score, "total" => $score, "count" => $count, "pass" => $pass, "rank" => '-', "time" => strtotime(@$statement->stored), 'percentage' => $percentage, 'timespent' => $timespent);
			$usermeta[] = $quizdata;

			$quizdata['quiz'] = $quiz;
			$quizdata['course'] = get_post($course_id);		
			$quizdata['started'] = $quizdata['completed'] = strtotime(@$statement->stored);

			update_user_meta( $user_id, '_sfwd-quizzes', $usermeta );

	        //learndash_process_mark_complete( $user_id, $quiz_id, false, $courseid );
			//add_action( 'learndash_quiz_completed', array( new LD_QuizPro(), 'set_quiz_status_meta' ), 1, 2 );

			do_action("learndash_quiz_completed", $quizdata, $user); //Hook for completed quiz
		}

		return true;
	}
}
function grassblade_learndash_slickquiz_loadresources($return, $post) { //Very old integration
	if(is_numeric($post))
		$post_id = $post;
	else if(!empty($post->ID))
		$post_id = $post->ID;
	else
		return $return;
	
	$has_xapi_content = grassblade_xapi_content::get_post_xapi_contents($post->ID);
	$has_xapi_content = !empty($has_xapi_content);

	if($has_xapi_content)
		return false; //Disable SlickQuiz
	else
		return $return;
}
add_filter("leandash_slickquiz_loadresources", "grassblade_learndash_slickquiz_loadresources", 1, 2);

function grassblade_learndash_disable_advance_quiz($return, $post) {
	if(is_numeric($post))
		$post_id = $post;
	else if(!empty($post->ID))
		$post_id = $post->ID;
	else
		return $return;

	$has_xapi_content = grassblade_xapi_content::get_post_xapi_contents($post_id);
	$has_xapi_content = !empty($has_xapi_content);

	if($has_xapi_content)
		return true; //Disable Advance Quiz
	else
		return $return;
}
add_filter("learndash_disable_advance_quiz", "grassblade_learndash_disable_advance_quiz", 1, 2);

function grassblade_learndash_quiz_content_access($return, $post) {
	$has_access = grassblade_learndash_quiz_has_access($post);

	if(empty($has_access)) {
	    $lesson_id = grassblade_learndash_course_get_single_parent_step(null, $post->ID);
        $lesson = get_post($lesson_id);
            
	    if(!empty($lesson_id))
	    	return sprintf(__("You do not have access to this quiz. Please go back and complete the content on the previous page: %s ", "grassblade"), "<a href='".get_permalink($lesson_id)."'>".$lesson->post_title."</a>");
	    else
		return false;
	}
	return $return;
}
add_filter("learndash_content_access", "grassblade_learndash_quiz_content_access", 10, 2);

function grassblade_learndash_quiz_block_content_access($return, $post, $attributes = array(), $content = "") {
	if($post)
	return grassblade_learndash_quiz_has_access($post);
	else
	return $return;
}
add_filter("gb_xapi_block_access", "grassblade_learndash_quiz_block_content_access", 10, 4);

function grassblade_learndash_quiz_has_access($post){
	if($post->post_type != "sfwd-quiz")
		return true;

    $lesson_progression_enabled = learndash_lesson_progression_enabled();
    if(empty($lesson_progression_enabled))
		return true;

    $lesson_id = grassblade_learndash_course_get_single_parent_step(null, $post->ID);
	if(!empty($lesson_id)) {
		$completed = grassblade_xapi_content::post_contents_completed($lesson_id);

		if(empty($completed)) {
			return false;
		}
	}
	return true;
}

function grassblade_learndash_add_certificate_link($content) {
	global $post;
	$xapi_contents = grassblade_xapi_content::get_post_xapi_contents($post->ID,true);
	if(!empty($xapi_contents))
	{
		if(!learndash_is_quiz_notcomplete(null, array($post->ID => 1 ))) {
			$link = learndash_get_certificate_link($post->ID);
			$link = str_replace("<a ", "<a class='btn-blue grassblade-print-certificate' ", $link);
			$content .= "<br>" . apply_filters("grassblade_learndash_quiz_certificate_link", $link, $post);
		}
	}
	return $content;
}
if(is_plugin_active(GBL_LEARNDASH_PLUGIN_FILE))
add_filter("the_content", "grassblade_learndash_add_certificate_link", 1, 10);

	function grassblade_duration_to_seconds($timeval) {
		if(empty($timeval)) return 0;
		
		$timeval = str_replace("PT", "", $timeval);
		$timeval = str_replace("H", "h ", $timeval);
		$timeval = str_replace("M", "m ", $timeval);
		$timeval = str_replace("S", "s ", $timeval);

		$time_sections = explode(" ", $timeval);
		$h = $m = $s = 0;
		foreach($time_sections as $k => $v) {
			$value = trim($v);
			
			if(strpos($value, "h"))
			$h = intVal($value);
			else if(strpos($value, "m"))
			$m = intVal($value);
			else if(strpos($value, "s"))
			$s = intVal($value);
		}
		$time = $h * 60 * 60 + $m * 60 + $s;
		
		if($time == 0)
		$time = (int) $timeval;
		
		return $time;
	}


add_filter("grassblade_group_leaders", "grassblade_get_learndash_group_leaders", 10, 2);
function grassblade_get_learndash_group_leaders($leaders, $group) {
	if(@$group->post_type != "groups")
		return $leaders;

	return $leaders +  grassblade_learndash_group_leaders($group);
}


add_filter("grassblade_group_users", "grassblade_get_learndash_group_users" , 10, 2);
function grassblade_get_learndash_group_users($users, $group) {
	if(@$group->post_type != "groups")
		return $users;

	return $users +  grassblade_learndash_group_users($group);
}

function grassblade_learndash_group_leaders($group) {
	if(!function_exists("learndash_get_groups_administrator_ids"))
		return array();

	$leader_ids = learndash_get_groups_administrator_ids($group->ID);
	$leaders = array();
	if(!empty($leader_ids))
	foreach ($leader_ids as $leader_id) {
		$leader = get_user_by("id", $leader_id);
		$leaders[$leader_id] = $leader->user_email;
	}

	return $leaders;
}
function grassblade_learndash_group_users($group) {
	if(!function_exists("learndash_get_groups_user_ids"))
		return array();

	$user_ids = learndash_get_groups_user_ids($group->ID);
	if(!empty($user_ids))
	foreach ($user_ids as $user_id) {
		$users[$user_id] = grassblade_user_email($user_id);
	}

	return $users;
}

add_filter("grassblade_groups", "grassblade_get_learndash_groups", 10, 2);
function grassblade_get_learndash_groups($return, $params) {
	if(!function_exists('learndash_get_course_id'))
		return array();
	
    $params["post_type"] = "groups";
    if(empty($params["posts_per_page"]))
    	$params["posts_per_page"] = -1;

    $leaders_list = @$params["leaders_list"];
    $users_list = @$params["users_list"];
    unset($params["leaders_list"]);
    unset($params["users_list"]);

    if(!empty($params["id"]))
    	$groups = array(get_post($params["id"]));
	else
    	$groups = get_posts($params);    

    foreach ($groups as $k => $group) {
		$g = array();
		$g["ID"] = $group->ID;
		$g["name"] = $group->post_title;
		if(!empty($leaders_list))
		$g["group_leaders"] = grassblade_learndash_group_leaders($group);

		if(!empty($users_list))
		$g["group_users"] = grassblade_learndash_group_users($group);
        $return[$g["ID"]] = $g;
    }
    if(!empty($params["id"]))
    {	
    	if(!empty($return[$params["id"]]))
    	return $return[$params["id"]];
    }
    return $return;
}

add_filter("gb_leaderboard_ids", 'grassblade_learndash_leaderboard_ids', 10, 3); 
function grassblade_learndash_leaderboard_ids($ids, $post, $shortcode_atts) {
	if($post->post_type == "sfwd-courses")
	{
		global $wpdb;
		if(class_exists( 'LDLMS_Course_Steps' ) && LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' )  {
			$LDLMS_Course_Steps = new LDLMS_Course_Steps($post->ID);
			$children = $LDLMS_Course_Steps->get_steps('l');
			$steps_ids = $post_ids = array();
			foreach ($children as $key => $value) {
				$value = explode(":", $value);
				if(!empty($value[1]) && is_numeric($value[1]) && intVal($value[1]) > 0)
				$steps_ids[] = intVal($value[1]);
			}
			if(!empty($steps_ids))
			$post_ids = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key IN ('show_xapi_content', 'show_xapi_content_blocks') AND  meta_value > 0 AND post_id IN (".implode(",", $steps_ids).")");
		}
		else
		{
			$post_ids = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key IN ('show_xapi_content', 'show_xapi_content_blocks') AND  meta_value > 0 AND post_id IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'course_id' AND meta_value = '%d')", $post->ID));
		}

		if(!empty($post_ids))
		$ids = array_merge($ids, $post_ids);

		if(!empty($post_ids))
		return $ids;
	}
	return $ids;
}

add_action('wp_print_scripts','grassblade_learndash_hide_mark_complete_button');
function grassblade_learndash_hide_mark_complete_button(){
	global $post;
	if(empty($post->ID)) return;
	if(!in_array($post->post_type, array('sfwd-quiz', 'sfwd-topic', 'sfwd-lessons')))
		return;
	$completed = grassblade_xapi_content::post_contents_completed($post->ID);

	//No content = true - No change
	//Has content but completion tracking disabled = true - No change 
	//Has content with completion tracking but at least one incomplete = false - Hide or Disable Mark Complete 
	//Has content with completion tracking and all complete = statements - No change 
	$completion_type = grassblade_xapi_content::post_completion_type($post->ID);
	if(empty($completed) && $completion_type == 'hide_button') {
		//Enable Next Lesson link
		add_filter("learndash_show_next_link", function($r, $u, $p) {return true;}, 100, 3);
		?>
		<style type="text/css">
			#sfwd-mark-complete, .learndash-wrapper form.sfwd-mark-complete, form.sfwd-mark-complete { 
			 	display:none;
			}
		</style>
		<?php
	}
}

add_filter("grassblade_lms_mark_complete_button_id","grassblade_learndash_get_mark_complete_btn_id",10,2);

function grassblade_learndash_get_mark_complete_btn_id($return,$post){
	if(empty($post->ID))
		return $return;

	if(!in_array($post->post_type, array('sfwd-quiz', 'sfwd-topic', 'sfwd-lessons'))){
		return $return;
	} else {
		return '.learndash_mark_complete_button';
	}
}

add_filter("grassblade_add_to_content_post", "grassblade_learndash_grassblade_add_to_content_post", 2, 3);
function grassblade_learndash_grassblade_add_to_content_post($selected_id, $post, $content) {
	if(!empty($post) && $post->post_type == "sfwd-quiz")
		return false;
	else
		return $selected_id;
}
add_filter("learndash_quiz_content", "grassblade_learndash_quiz_content", 10, 2);
function grassblade_learndash_quiz_content($quiz_start_button, $post) {
	$xapi_content_id_metabox = get_post_meta($post->ID, "show_xapi_content", true);
	$has_xapi_content = grassblade_xapi_content::get_post_xapi_contents( $post->ID );
	$has_xapi_content = !empty($has_xapi_content);
	if(empty($post->ID) || !$has_xapi_content)
		return $quiz_start_button;

	$quiz_start_button = "";

	if(!empty($xapi_content_id_metabox)) {
		if(strpos($quiz_start_button, "[grassblade]") === false)
		$quiz_start_button = do_shortcode('[grassblade id='.$xapi_content_id_metabox."]");
		else
		$quiz_start_button = str_replace("[grassblade]", do_shortcode('[grassblade id='.$xapi_content_id_metabox."]"),	$quiz_start_button);
	}

	if(learndash_is_quiz_notcomplete(null, array($post->ID => 1 )))
		$quiz_start_button .= "<br>" . learndash_mark_complete($post); //Show mark complete button if Quiz is Not Completed. And page has a xAPI Content (with or without completion tracking)
	
	return $quiz_start_button;
}
//apply_filters( 'learndash_header_tab_menu', $header_data['tabs'], $menu_tab_key, $screen_post_type );
add_filter( 'learndash_header_tab_menu', function($header_data_tabs, $menu_tab_key, $screen_post_type) {
	global $post;
	if(!is_admin() || $screen_post_type != "sfwd-quiz" || empty($post->ID))
		return $header_data_tabs;

	$has_xapi_content = grassblade_xapi_content::get_post_xapi_contents( $post->ID );
	$has_xapi_content = !empty($has_xapi_content);
	if($has_xapi_content) {
		$header_data_tabs_new = array();
		foreach ($header_data_tabs as $key => $value) {
			if($value["id"] != "learndash_quiz_builder")
				$header_data_tabs_new[] = $value;
		}
		return $header_data_tabs_new;
	}
	
	return $header_data_tabs;
}, 10, 3);


function grassblade_learndash_add_msg_to_mark_complete($content) {
	global $post;
	
	if( !in_array($post->post_type, array( "sfwd-quiz", "sfwd-lessons", "sfwd-topic" )) )
		return $content;

	//Added both all Lessons/Topics/Quizzes
	$completion_tracking = grassblade_xapi_content::is_completion_tracking_enabled_by_post($post->ID);
	$gb_completion_tracking_alert = apply_filters("gb_completion_tracking_alert", __("Click OK to confirm that you have completed the content above?", "grassblade"), $post->ID, 0);
	if($completion_tracking && !empty($gb_completion_tracking_alert)) {
		$content .= ' <script type="text/javascript"> jQuery(function() { jQuery("form#sfwd-mark-complete,from.sfwd-mark-complete").submit(function(e) { var completed_course=confirm("'.$gb_completion_tracking_alert.'"); if(completed_course == false) e.preventDefault();}); });</script> ';
	}
	return $content;
}
add_filter( 'the_content', 'grassblade_learndash_add_msg_to_mark_complete', 2, 1);

add_filter("grassblade_add_to_content_box_onchange", "grassblade_learndash_grassblade_add_to_content_box_onchange", 10, 2);
function grassblade_learndash_grassblade_add_to_content_box_onchange($action, $post) {
	if(!empty($post->post_type) && $post->post_type == "sfwd-quiz")
		return '';
	else
		return $action;
}
function grassblade_learndash_assignment_uploaded($assignment_post_id, $assignment_meta) {
	$assignment = get_post($assignment_post_id);
	if(empty($assignment->ID))
		return;

	$user = get_user_by("id", $assignment->post_author);
	if(empty($user->ID))
		return;

	$actor = grassblade_getactor(false, null, $user);

	$assignment_url_id = grassblade_post_activityid($assignment_post_id);

	$verb = grassblade_getverb( "submitted" );

	$object = grassblade_getobject($assignment_url_id, $assignment->post_title, $assignment->post_title, "http://nextsoftwaresolutions.com/xapi/activities/assignment/");

    $course_id 	= grassblade_learndash_get_course_id( $assignment_post_id, $user->ID );
    $course 	= get_post($course_id);
	$lesson_id 	= grassblade_learndash_course_get_single_parent_step($course_id, $assignment->ID, "sfwd-lessons");
	if(!empty($lesson_id))
	{
		$lesson = get_post($lesson_id);
	}

	$course_title = $course->post_title;
	$course_url = grassblade_post_activityid($course->ID);
	
	if(!empty($lesson->ID))
	{
		$parent_title 	= $lesson->post_title;
		$parent_url 	= grassblade_post_activityid($lesson->ID);
		$parent_type	= 'lesson';
	}
	else
	{
		$parent_title	= $course_title;
		$parent_url		= $course_url;
		$parent_type	= 'course';
	}

	$parent_object = grassblade_getobject($assignment_url_id, $assignment->post_title, $assignment->post_title, "http://nextsoftwaresolutions.com/xapi/activities/assignment/");

	$parent_object = grassblade_getobject($parent_url, $parent_title, $parent_title, 'http://adlnet.gov/expapi/activities/'.$parent_type,'Activity');
	$course_object = grassblade_getobject($course_url, $course_title, $course_title, 'http://adlnet.gov/expapi/activities/course','Activity');
	

    $statement =    array(
                        "actor" => $actor,
                        "verb"  => $verb,
                        "object" => $object,
                        "context" => array("contextActivities" => array("parent" => $parent_object, "grouping" => $course_object)),
                    );

    $statements = array($statement);
    grassblade_send_statements($statements);
}

add_filter("grassblade_learndash_get_courses", function($r, $params) {
	ini_set('set_time_limit', 1200);
	global $wpdb;
	add_filter("ld_course_list_shortcode_attr_values", function($atts, $attr) {
		$atts["num"] = -1;
		return $atts;
	}, 10, 2);

	$posts_per_page = -1;// isset($params["posts_per_page"])? $params["posts_per_page"]:-1;
	if(!empty($params["id"])) {
		$course  = $params["id"];
		if(empty($course)  || $course->post_status != "publish")
			return stdClass();

		$courses = array($course);
	}
	else
	{
//		echo gmdate("Y-m-d H:i:s",$params["modified_time"]);exit();
		$contents = array();
		if(function_exists('ld_course_list'))
		$courses = ld_course_list(array("array" => true, "num" => $posts_per_page));
		if(!empty($params["modified_time"])) {
			$sql = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type IN ('sfwd-quiz', 'sfwd-courses', 'sfwd-topic', 'sfwd-lessons', 'gb_xapi_content') AND post_modified_gmt > '%s' ", gmdate("Y-m-d H:i:s", @$params["modified_time"]));
			$results = $wpdb->get_results($sql);

			//print_r($results);exit;
			$changed_courses = array();
			foreach ($results as $key => $value) {
				if(function_exists('ld_course_list') && in_array($value->post_type, array("sfwd-courses", "sfwd-lessons", "sfwd-topic", "sfwd-quiz"))) {
					//$results[$key]->course_ids = grassblade_learndash_get_course_ids($value->ID, true);
					if($value->post_type == "sfwd-courses")
					$changed_courses[$value->ID] = $value;
					else
					$changed_courses += grassblade_learndash_get_course_ids($value->ID, false);
				}
				else if($value->post_type == "gb_xapi_content") {
					$posts = grassblade_get_post_with_content($value->ID);
					$contents[$value->ID] = $value;

					//$course_ids = array();
					if(!empty($posts))
					foreach ($posts as $key2 => $value2) {
						if($value2->post_status == "publish" && function_exists('ld_course_list') && in_array($value2->post_type, array("sfwd-courses", "sfwd-lessons", "sfwd-topic", "sfwd-quiz"))) {
							//$course_ids += grassblade_learndash_get_course_ids($value2->ID, true);
							$changed_courses += grassblade_learndash_get_course_ids($value2->ID, false);

							if(isset($contents[$value->ID]))
								unset($contents[$value->ID]);
						}
					}
				}
			}
		}
		else
		{
			$xapi_contents = get_posts(array("posts_per_page" => -1, "post_type" => "gb_xapi_content", 'post_status' => 'publish'));
			foreach ($xapi_contents as $value) {
				$contents[$value->ID] = $value;
			}
		}
	}

	$return_courses = new stdClass();
	if(!empty($courses))
	foreach ($courses as $course) {
		if($course->post_status == "publish") {
			if(!isset($changed_courses) || isset($changed_courses[$course->ID]) && !is_null($changed_courses[$course->ID]))
			{
				$return_courses->{$course->ID} = grassblade_learndash_get_course_structure($course);

				$contents = grassblade_remove_contents_in_object($return_courses->{$course->ID}, $contents);
			}
			else 
				$return_courses->{$course->ID} = true;
		}
	}

	foreach ($contents as $key => $value) {
		$contents[$key]->activity_id = grassblade_post_activityid($value->ID);
	}

	$return_courses->contents = $contents;
	return $return_courses;
}, 10, 2);

function grassblade_remove_contents_in_object($object, $contents) {
	if(isset($object->{'xapi_content'}))
	{
		if(isset($contents[$object->{'xapi_content'}->ID]))
		unset($contents[$object->{'xapi_content'}->ID]);
	}

	foreach ($object as $key => $value) {
		
		if(is_object($value) && empty($value->ID))
		$contents = grassblade_remove_contents_in_object($value, $contents);
	}
	return $contents;
}

function grassblade_learndash_get_course_structure($course) {
	$course_structure = new stdClass();
	$course->activity_id = grassblade_post_activityid($course->ID);
	$course_structure->course = $course; //grassblade_post_activityid($course->ID);
	
	$lessons = learndash_get_lesson_list($course->ID); //learndash_get_course_lessons_list($course)
	$structure_lessons = new stdClass();
	foreach ($lessons as $lesson) {
		$structure_lessons->{$lesson->ID} = new stdClass();
		$lesson->activity_id = grassblade_post_activityid($lesson->ID);
		$structure_lessons->{$lesson->ID}->lesson = $lesson;
		
		/*** Lesson Topics ***/
		$topics = learndash_get_topic_list( $lesson->ID, $course->ID );
		if(!empty($topics)) {
			$structure_topics = new stdClass();
			foreach ($topics as $topic) {
				$structure_topics->{$topic->ID} = new stdClass();
				$topic->activity_id = grassblade_post_activityid($topic->ID);
				$structure_topics->{$topic->ID}->topic = $topic;
	
				/*** Topic Quizzes ***/
				$quizzes = learndash_get_lesson_quiz_list($topic->ID, null, $course->ID);
				$structure_topics->{$topic->ID} = grassblade_learndash_add_quiz_structure($structure_topics->{$topic->ID}, $quizzes);
				/*** Topic Quizzes ***/

				/*** Topic xAPI Content ***/
				$structure_topics->{$topic->ID} = grassblade_learndash_add_xapi_content_structure($structure_topics->{$topic->ID}, $topic->ID);
				/*** Topic xAPI Content ***/
			}

			$structure_lessons->{$lesson->ID}->topics  = $structure_topics;
		}
		/*** Lesson Topics ***/

		/*** Lesson Quizzes ***/
		$quizzes = learndash_get_lesson_quiz_list($lesson->ID, null, $course->ID);
		$structure_lessons->{$lesson->ID} = grassblade_learndash_add_quiz_structure($structure_lessons->{$lesson->ID}, $quizzes);
		/*** Lesson Quizzes ***/
		

		/*** Lesson xAPI Content ***/
		$structure_lessons->{$lesson->ID} = grassblade_learndash_add_xapi_content_structure($structure_lessons->{$lesson->ID}, $lesson->ID);
		/*** Lesson xAPI Content ***/

	}
	$course_structure->lessons = $structure_lessons;


	/*** Course Quizzes ***/
	$quizzes = learndash_get_course_quiz_list($course->ID);
	$course_structure = grassblade_learndash_add_quiz_structure($course_structure, $quizzes);
	/*** Course Quizzes ***/

	/*** Course xAPI Content ***/
	$course_structure = grassblade_learndash_add_xapi_content_structure($course_structure, $course->ID);
	/*** Course xAPI Content ***/

	return $course_structure;
}
function grassblade_learndash_add_quiz_structure($structure, $quizzes) {
	$structure_quizzes = new stdClass();
	foreach ($quizzes as $quiz) {
		$quiz = $quiz["post"];
		$structure_quizzes->{$quiz->ID} = new stdClass();
		$quiz->activity_id = grassblade_post_activityid($quiz->ID);
		$structure_quizzes->{$quiz->ID}->quiz = $quiz;
		$structure_quizzes->{$quiz->ID} = grassblade_learndash_add_xapi_content_structure($structure_quizzes->{$quiz->ID}, $quiz->ID);
	}
	if(count($quizzes) > 0)
	$structure->quizzes = $structure_quizzes;
	return $structure;
}
function grassblade_learndash_add_xapi_content_structure($structure, $post_id) {
	$xapi_content_ids = grassblade_xapi_content::get_post_xapi_contents( $post_id );

	if(!empty($xapi_content_ids) && is_array($xapi_content_ids)) {
		foreach ($xapi_content_ids as $xapi_content_id) {
			$xapi_content = get_post($xapi_content_id);
			if(!empty($xapi_content->ID) && $xapi_content->post_status == "publish") {
				$xapi_content->activity_id = grassblade_post_activityid($xapi_content->ID);

				if(empty($structure->xapi_contents))
					$structure->xapi_contents = array();
				
				$structure->xapi_contents[] = $structure->xapi_content = $xapi_content; //Multiple xAPI Contents supported only after LRS v2.3
			}
		}
	}
	return $structure;
}
function grassblade_learndash_get_courses_rest_api( $d ) {
    $params = array();

    if(isset($_REQUEST["id"]) && is_numeric($_REQUEST["id"]))
        $params["id"] = $_REQUEST["id"];

    if(isset($_REQUEST["posts_per_page"]) && is_numeric($_REQUEST["posts_per_page"]))
        $params["posts_per_page"] = $_REQUEST["posts_per_page"];

    if(isset($_REQUEST["modified_time"]) && is_numeric($_REQUEST["modified_time"]))
        $params["modified_time"] = $_REQUEST["modified_time"];

    return apply_filters("grassblade_learndash_get_courses", array(), $params);
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'grassblade/v1', '/courses', array(
    'methods' => 'GET',
    'callback' => 'grassblade_learndash_get_courses_rest_api',
    'permission_callback' => function () {
      return current_user_can( 'connect_grassblade_lrs' ) ||  current_user_can( 'manage_options' );
    }
  ) );
} );

//learndash_process_mark_complete($user_id, $lesson_id, false, $course_id);
function grassblade_learndash_process_and_verify_mark_complete($user_id, $post_id, $onlycalculate = false, $course_id = null) {
	$ret = learndash_process_mark_complete($user_id, $post_id, $onlycalculate, $course_id);
	
	if(empty($ret))
	{
		return " Completion Failed";
	}
	return $ret;
}

/*  \/ Change the Course Status to In Progress if any content has been started \/ */
//    do_action("grassblade_xapi_tracked", $statement, $xapi_content_id, $user);
add_action("grassblade_xapi_tracked", function($statement_json, $xapi_content_id, $user) {
    $statement = json_decode($statement_json);
    if(!is_object($statement) || empty($user->ID) || empty($xapi_content_id))
    	return;

    if(is_string($statement->verb))
    	$verb = $statement->verb;
    else if(is_object($statement->verb) && is_string($statement->verb->id))
    	$verb = $statement->verb->id;
    else
    	return;

	$user_id = $user->ID;

    if(in_array($verb, array("attempted", "launched", "initialized", "http://adlnet.gov/expapi/verbs/attempted", "http://adlnet.gov/expapi/verbs/launched", "http://adlnet.gov/expapi/verbs/initialized"))) {
		$started = get_user_meta($user_id, "content_started_".$xapi_content_id, true );
		if(empty($started)) {
			update_user_meta($user_id, "content_started_".$xapi_content_id, time() );
		}
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare("select post_id from $wpdb->postmeta where meta_key IN ('show_xapi_content', 'show_xapi_content_blocks') AND meta_value = '%d'", $xapi_content_id) );

    	$done = array();
    	if(!empty($post_ids))
		foreach ($post_ids as $post_id) {
			$course_ids = grassblade_learndash_get_course_ids($post_id, true,$user_id);

			foreach ($course_ids as $course_id => $course_name) {
				if(empty($done[$course_id]) && ld_course_check_user_access($course_id, $user_id)) {
					$started = get_user_meta($user_id, "content_course_started_".$course_id, true );
					if(empty($started))
						update_user_meta($user_id, "content_course_started_".$course_id, time() );
					$done[$course_id] = 1;
				}
			}
		}
    }
}, 10, 3);

//apply_filters( 'learndash_course_status', $course_status_str, $id, $user_id, $course_progress[ $id ] );
add_filter("learndash_course_status", function($course_status_str, $course_id, $user_id, $progress) {
	if($course_status_str == esc_html__( 'Not Started', 'learndash' ))
	{
		$started = get_user_meta($user_id, "content_course_started_".$course_id, true );
		if(!empty($started))
		{
			return esc_html__( 'In Progress', 'learndash' );
		}
	}
	return $course_status_str;
}, 10, 4);
/* /\  Change the Course Status to In Progress if any content has been started /\  */


/* Show Next Link if Completion Tracking enabled */
add_filter( 'learndash_show_next_link', function($current_complete, $user_id, $post_id ) {
    if(method_exists("grassblade_xapi_content", "is_completion_tracking_enabled_by_post"))
    $completion_tracking_enabled = grassblade_xapi_content::is_completion_tracking_enabled_by_post($post_id);
	$completion_type = grassblade_xapi_content::post_completion_type($post_id);
	$lesson_progression_enabled = learndash_lesson_progression_enabled();
	// Course Progression Disable = current_complete true

	if(( !$current_complete || !$lesson_progression_enabled) && !empty($completion_tracking_enabled) && $completion_type == 'hide_button') //Content is not complete. Mark Complete button is being removed by GrassBlade. Replaced with this \/ Next Button.
	add_filter('learndash_mark_complete', function($return, $post) {

		if(!function_exists("learndash_get_content_label"))
			return $return;
		
		$learndash_next_nav = gb_get_learndash_next_nav($post);
		$button_class = "ld-button ";
		return '<a class="'. esc_attr($button_class).'" href="'.esc_attr($learndash_next_nav).'">
	            <span class="ld-text">'. sprintf( esc_html_x( 'Next %s', 'Previous Item Text', 'learndash' ), learndash_get_content_label(get_post_type()) ).'</span>
	            <span class="ld-icon ld-icon-arrow-right"></span>
	    </a>';
	}, 10, 2); 

    return $current_complete;
}, 9, 3); //Had to use Priority 9 due to interference from Uncanny Toolkit Pro. This might now interfere with any other plugins trying to change the Mark Complete Button.

function gb_get_learndash_next_nav($post){
	$learndash_next_nav = learndash_next_post_link( null, true, $post );
	if( empty($learndash_next_nav) )
	{
		$course_id = learndash_get_course_id();
		$parent_id = learndash_course_get_single_parent_step($course_id,$post->ID);
		if (!empty($parent_id)) {
			return get_permalink($parent_id);
		} else {
			return get_permalink($course_id);
		}
	}
	return $learndash_next_nav;
}

add_action('learndash_update_course_access','grassblade_learndash_send_statements_on_enroll_unenroll', 1, 4);

/**
 * Course Access Updation.
 *
 *
 * @param  int  	$user_id 		
 * @param  int  	$course_id
 * @param  array  	$access_list
 * @param  bool  	$remove
 *
 */
function grassblade_learndash_send_statements_on_enroll_unenroll($user_id, $course_id, $access_list, $remove){
	if (class_exists('grassblade_events_tracking')) { 
		if ($remove) {
			grassblade_events_tracking::send_unenrolled($user_id,$course_id);
		} else {
			grassblade_events_tracking::send_enrolled($user_id,$course_id);
		}
	} // end of if grassblade_activities class exists
} 

add_filter('learndash_profile_quiz_columns', 'grassblade_ld_content_quiz_report', 10, 2);

function grassblade_ld_content_quiz_report($quiz_columns,$quiz_attempt){
	if(!class_exists('gb_rich_quiz_report'))
		return $quiz_columns;

	if (empty($quiz_attempt['statement_id']))
		return $quiz_columns;
	
	$content = str_replace("-", "", $quiz_columns['stats']['content']);
	if (empty($content)) {
		$content_id = grassblade_xapi_content::last_post_content_with_completion_tracking($quiz_attempt['post']->ID);
		if(!empty($content_id))
		$rich_quiz_report = gb_rich_quiz_report::is_enabled($content_id);
		if (!empty($rich_quiz_report)) {
			$registration = 0;
			$quiz_report_btn = '<a class="gb-pointer" onclick="get_gb_quiz_report('.$content_id.',null,'.$registration.',\''.$quiz_attempt['statement_id'].'\');"><span class="ld-icon ld-icon-assignment"></span></a>';
			$quiz_columns['stats']['content'] = $quiz_report_btn;
		}
	}
	return $quiz_columns;
}

add_filter('ld_template_args_profile','grassbalde_ld_profile_args',10,3);

function grassbalde_ld_profile_args($args, $filepath, $echo){

	//var_dump($args['quiz_attempts']); exit;
	if(empty($args['quiz_attempts'][0]))
		return $args;

	$user = wp_get_current_user();
	if (empty($user->ID)) 
		return $args;

	$course_ids = array();
	$updated_quiz_attempts = array();
	foreach ($args['quiz_attempts'][0] as $key => $quiz_attempt) {
		if (isset($quiz_attempt['statement_id'])) {
			$quiz_id = $quiz_attempt['quiz'];

			if(!isset($course_ids[$quiz_id]))
			$course_ids[$quiz_id] = grassblade_learndash_get_course_ids($quiz_id, true, $user->ID);

			if (!empty($course_ids[$quiz_id])) {
				foreach ($course_ids[$quiz_id] as $course_id => $course) {
					$args['quiz_attempts'][$course_id][] = $quiz_attempt;
				}
				$updated_quiz_attempts[$quiz_attempt["quiz"].".".$quiz_attempt["statement_id"]] = $course_ids[$quiz_id];
				unset($args['quiz_attempts'][0][$key]);
			}
		}
	}

	if(empty($updated_quiz_attempts)) //No updates return
		return $args;

	$usermeta = get_user_meta( $user->ID, '_sfwd-quizzes', true );

	foreach ($usermeta as $key => $usermeta_quiz_attempt) {
		if (empty($usermeta_quiz_attempt['course']) && isset($usermeta_quiz_attempt['statement_id']) ) {
			if (isset($updated_quiz_attempts[$usermeta_quiz_attempt['quiz'].".".$usermeta_quiz_attempt['statement_id']])) {
				foreach ($updated_quiz_attempts[$usermeta_quiz_attempt['quiz'].".".$usermeta_quiz_attempt['statement_id']] as $course_id => $course) {
					if(empty($usermeta[$key]['course']))
					$usermeta[$key]['course'] = $course_id;
					else
					{
						$new_quiz_attempt = $usermeta[$key];
						$new_quiz_attempt['course'] = $course_id;
						$usermeta[] = $new_quiz_attempt;
					}
				
					$usermeta_updated = true;
				}
			}
		}
	}

	if(!empty($usermeta_updated))
	update_user_meta( $user->ID, '_sfwd-quizzes',$usermeta);

	return $args;
}

add_filter("grassblade_is_group_leader_of_user", function($r, $current_user_id, $user_id) {
	if (!function_exists('learndash_is_group_leader_user'))
		return false;

	return learndash_is_group_leader_user() && learndash_is_group_leader_of_user($current_user_id, $user_id);
}, 10, 3);


