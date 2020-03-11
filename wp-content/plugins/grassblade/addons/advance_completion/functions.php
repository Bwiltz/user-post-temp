<?php 

class grassblade_advance_completion {

	function __construct() {
		add_action( 'wp_ajax_grassblade_content_completion', array($this,'content_completion' ));
		add_filter('grassblade_localize_script_data',array( $this,'grassblade_advance_completion_data'),10,2);
	}

	function content_completion() {
		$data = $_POST['data'];

		$content_id = $data['content_id'];
		$registration = $data['registration'];
		$post_id = $data['post_id'];
		$user = wp_get_current_user();
		
		if (!empty($user->ID)) {

			$completion_result = $this->get_completion($user->ID,$content_id,$registration);

			if( !empty($completion_result) ) {

				$grassblade_xapi_content = new grassblade_xapi_content();
				$score_table = $grassblade_xapi_content->get_score_table($user->ID,$content_id);

				$completed = grassblade_xapi_content::post_contents_completed($post_id,$user->ID);
				if (empty($completed)) {
					$post_completion = false;
				} else {
					$post_completion = true;
				}

				$data = array( "score_table" => $score_table, "completion_result" => $completion_result[0],"post_completion" => $post_completion );

				echo json_encode($data);
				die();
			} 
		}
	}

	function get_completion($user_id,$content_id,$registration) {
		global $wpdb;
		$completion_check_time = defined("GB_COMPLETION_CHECK_TIME")? GB_COMPLETION_CHECK_TIME:30;
		$long_pooling_time = defined("GB_POOLING_TIME")? GB_POOLING_TIME:90;
		set_time_limit(intVal($long_pooling_time) + 60);

		$count = ceil( $long_pooling_time / $completion_check_time );

		for( $i=0; $i < $count; $i++) {
			$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}grassblade_completions` WHERE user_id = %d AND content_id = %d AND statement LIKE %s ORDER BY id DESC LIMIT 1", $user_id , $content_id, '%' . $wpdb->esc_like($registration) . '%'));

			if(!empty($result)) {
				//Delete User Meta if sleep has happened before return. 
				//Fixing bug: get_user_meta is not reading completed_<xapi_content> in case of Open in New Window
				//because the completion happened after this request started.
				if($i > 0)
					wp_cache_delete($user_id, "user_meta"); 

				return $result;
			}

			sleep($completion_check_time); //30 second wait by default
		}

		return;
	}

	function grassblade_advance_completion_data($gb_data,$post){
		if(empty($post->ID)) 
			return $gb_data;

		$completed = grassblade_xapi_content::post_contents_completed($post->ID);

		//No content = true - No change
		//Has content but completion tracking disabled = true - No change 
		//Has content with completion tracking but at least one incomplete = false - Hide or Disable Mark Complete 
		//Has content with completion tracking and all complete = statements - No change 

		if(empty($completed)) {
			$gb_data['completion_type'] = grassblade_xapi_content::post_completion_type($post->ID);

			$mark_complete_button = '';
			$mark_complete_button = apply_filters('grassblade_lms_mark_complete_button_id',$mark_complete_button,$post);

			$gb_data['mark_complete_button'] = $mark_complete_button;
		}
		return $gb_data;
	}
}

$gb_ac = new grassblade_advance_completion();
