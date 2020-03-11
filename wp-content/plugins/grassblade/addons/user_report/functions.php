<?php

class grassblade_user_report {

	function __construct() {
		add_shortcode("gb_user_report", array($this,"user_report"));
		add_action( 'init', array($this,'custom_script_gb_profile') );
	}

	function user_report($attr){

		$shortcode_defaults = array(
	 		'user_id' 		=> null,
	 		'bg_color' 		=> null,
	 		'class'			=> ''
		);

		$shortcode_atts = shortcode_atts ( $shortcode_defaults, $attr);

		extract($shortcode_atts);
		$requested_user_id = '';
		if(isset($_REQUEST['user_id']))
			$requested_user_id = $_REQUEST['user_id'];

		$current_user = wp_get_current_user();

		if(!empty($current_user->ID) && (empty($requested_user_id) || $requested_user_id == $current_user->ID || current_user_can("manage_options") || apply_filters("grassblade_is_group_leader_of_user", false, $current_user->ID, $requested_user_id) ) ) {
			$user_id = $requested_user_id;
			if(empty($user_id)){
				$user_id = $current_user->ID;
				$user = $current_user;
			}
		}
		else
		{
			if(!empty($current_user->ID) ) {
				$user_id = $current_user->ID;
				$user = $current_user;
			}
        }

		if(empty($user_id))
			return '';

		if (empty($user))
            $user = get_userdata($user_id);

        if(empty($bg_color))
			$bg_color = '#83BA39';

		$xapi_contents = $this->get_xapi_contents($user_id);
		$completed = 0;
		$in_progress = 0;
		$total_score = 0;
		foreach ($xapi_contents as $key => $value) {
			if ($value['content_status'] == 'Passed' || $value['content_status'] == 'Completed') {
				$completed++;
			}
			if ($value['total_attempts'] == 0 && !empty($value['is_inprogress'])) {
				$in_progress++;
			}
			$total_score += intval($value['best_score']);
		}

		$profile_data = array(  'user' => $user,
								'profile_pic' => get_avatar( $user->user_email, 150 ),
								'edit_profile' => get_edit_user_link(),
								'blog_url' => get_bloginfo('wpurl'),
								'xapi_contents' => $xapi_contents,
								'total_xapi_contents' => count($xapi_contents),
								'total_completed' => $completed,
								'total_in_progress' => $in_progress,
								'avg_score' => round($total_score/count($xapi_contents),2)
							);

		$profile_data = apply_filters("gb_profile_data", $profile_data,$user_id);

		extract($profile_data);
		ob_start();

		include_once(dirname(__FILE__)."/templates/xapi_default.php");
		
		$user_report =  ob_get_clean();

		return "<div id='gb_user_report_".$user_id."' class='gb_user_report ".$class."'>".$user_report."</div>";
	}

	function get_xapi_contents($user_id){
		global $wpdb;
		$xapi_contents = array();
		$xapi_contents_data = $wpdb->get_results("SELECT ID, post_title, post_status FROM $wpdb->posts WHERE post_type = 'gb_xapi_content' AND post_status = 'publish' ORDER BY post_title ASC");
		$all_attempts_raw = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}grassblade_completions` WHERE user_id = '%d' ORDER BY id DESC", $user_id), ARRAY_A);
		$all_attempts = $best_attempts = array();
		if(!empty($all_attempts_raw))
		foreach ($all_attempts_raw as $attempt) {
			$content_id = $attempt["content_id"];
			$all_attempts[$content_id] = empty($all_attempts[$content_id])? array():$all_attempts[$content_id];
			$all_attempts[$content_id][] = $attempt;

			if(empty($best_attempts[$content_id]) || $attempt["percentage"] > $best_attempts[$content_id]["percentage"])
				$best_attempts[$content_id] = $attempt;
		}

		foreach ($xapi_contents_data as $xapi_data) {
			$attempts = !empty($all_attempts[$xapi_data->ID])? $all_attempts[$xapi_data->ID]:array();
			$best_score = '--';
			$content_status = '--';
			$is_inprogress = false;
			if (!empty($attempts)) {
				$total_time_spent = 0;
				$best_score = $best_attempts[$xapi_data->ID]['percentage'];
				$content_status = $best_attempts[$xapi_data->ID]['status'];
				foreach ($attempts as $key => $attempt) {
					$total_time_spent += $attempt['timespent'];
					$attempts[$key]['timespent'] = grassblade_seconds_to_time($attempt['timespent']);
					$attempts[$key]['timestamp'] = date( get_option( 'date_format' ) . " " . get_option( 'time_format' ), strtotime($attempt['timestamp']));
				}
				$total_time_spent = grassblade_seconds_to_time($total_time_spent);
			} else {
				$in_progress = grassblade_xapi_content::is_inprogress($xapi_data->ID,$user_id);
				if (!empty($in_progress)) {
					$content_status = 'In Progress';
					$is_inprogress = true;
				}
				$total_time_spent = '--:--';
			}
			$xapi_contents[] = array('content' => $xapi_data,
									'best_score' => $best_score,
									'content_status' => $content_status,
									'total_time_spent' => $total_time_spent,
									'attempts' => $attempts, 
									'total_attempts' => count($attempts),
									'is_inprogress' => $is_inprogress,
									'quiz_report_enable' => !empty($attempts) && gb_rich_quiz_report::is_enabled($xapi_data->ID)
								  );
			
		} // end of foreach

		$xapi_contents = apply_filters("gb_user_report_contents", $xapi_contents,$user_id);
		return $xapi_contents;
	}

	function custom_script_gb_profile(){
		wp_enqueue_script( 'gb-user-profile', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ) , GRASSBLADE_VERSION);

		$gb_profile = array('date' => __("Date"),
							 'score' => __("Score"),
							 'status' => __("Status"),
							 'timespent' => __("Timespent"),
							 'quiz_report' => __("Quiz Report"),
							);
	wp_localize_script( 'gb-user-profile', 'gb_profile',  $gb_profile);
	} //end of custom_script_gb_profile function 

} // end of class

$gb_ur = new grassblade_user_report();



