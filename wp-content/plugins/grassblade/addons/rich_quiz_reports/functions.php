<?php

 class gb_rich_quiz_report{

 	function __construct() {
 		add_action( 'wp_ajax_gb_rich_quiz_report', array($this, 'gb_rich_quiz_report' ));
 		add_filter( 'grassblade_your_scores',array($this, 'show_rich_quiz_report'), 10, 4);
	}

	function gb_rich_quiz_report() {
		//echo 'test';
		$content_id = intval($_REQUEST["id"]);
		$registration = isset($_REQUEST["registration"])?$_REQUEST["registration"] : '';
		$user_id = isset($_REQUEST["user_id"])?$_REQUEST["user_id"] : '';
		$statement_id = isset($_REQUEST["statement_id"])?$_REQUEST["statement_id"] : '';

		$current_user = wp_get_current_user();
		global $wpdb;
		if(!empty($current_user->ID) && (empty($user_id) || $user_id == $current_user->ID || current_user_can("manage_options") ) ) {
			//User has access to report.
			if(empty($user_id))
				$user_id = $current_user->ID;
		}
		else
		{
			if(!empty($current_user->ID) ) {
				$is_group_leader = apply_filters("grassblade_is_group_leader_of_user", false, $current_user->ID, $user_id);
			}

			if(empty($is_group_leader)) {
				echo "Unauthorized Access";
				exit;
			}
        }

		if (empty($registration)  && !empty($statement_id)) {
			$statement = $wpdb->get_var($wpdb->prepare("SELECT statement FROM `{$wpdb->prefix}grassblade_completions` WHERE user_id = %d AND content_id = %d AND statement LIKE %s LIMIT 1", $user_id , $content_id, '%' . $wpdb->esc_like($statement_id) . '%'));
			if (!empty($statement)) {
				$statement = json_decode($statement);
				$registration = $statement->context->registration;
				$agent_id = NSS_XAPI::get_agent_id($statement);
			}
		}

		if (empty($agent_id) && !empty($registration)) {
			$statement = $wpdb->get_var($wpdb->prepare("SELECT statement FROM `{$wpdb->prefix}grassblade_completions` WHERE user_id = %d AND content_id = %d AND statement LIKE %s LIMIT 1", $user_id , $content_id, '%' . $wpdb->esc_like($registration) . '%'));
			if (!empty($statement)) {
				$agent_id = NSS_XAPI::get_agent_id($statement);
			}
		}
		if (empty($agent_id)) {
			$agent = grassblade_getactor(NULL, NULL, get_user_by("id", $user_id));
			$agent_id = grassblade_get_actor_id($agent);
		}

		$objectid = grassblade_post_activityid($content_id);

		if(!empty($agent_id) && !empty($objectid))
		echo $this->get_rich_quiz_report($objectid, $agent_id,$registration);

		exit();
	}
	function get_rich_quiz_report($objectid, $agent_id,$registration) {

		$grassblade_settings = grassblade_settings();	
		$pass = grassblade_generate_secure_token(9, $grassblade_settings["pass"], $agent_id);
		$auth = base64_encode($grassblade_settings["user"].":".$pass);
		$grassblade_settings = grassblade_settings();	
		$endpoint = $grassblade_settings["endpoint"];
		$endpoint = str_replace("xAPI/", "api/v1/quiz_report/get", $endpoint);

		$params = array(
					"agent_id" 	=> $agent_id,
					"auth"		=> $auth,
					"related" 	=> 1,
					"objectid" 	=> $objectid,
				);
		if (!empty($registration))
			$params['registration'] = $registration;
		//print_r($_REQUEST);
		//print_r($params);
		$params_string = http_build_query($params);
		//echo $params_string;
		$url = $endpoint."?".$params_string;
		//echo $url;

	    //$html = str_replace("quiz_report/get?","quiz_report/get?auth=".$auth."&", grassblade_file_get_contents_curl($url));
		$html = grassblade_file_get_contents_curl($url);
		return $html;
	}

	function show_rich_quiz_report($scores, $user_id, $content_id, $raw_scores) {

		$show_rich_quiz_report = $this->is_enabled($content_id);

		if(empty($show_rich_quiz_report) )
			return $scores;

		foreach ($raw_scores as $key => $raw_score) {
			$statement = json_decode($raw_score['statement']);
			$registration = $statement->context->registration;

			$scores[$key][__("Quiz Report", "grassblade")] = '<a onclick="return get_gb_quiz_report('.$content_id.','.$user_id.',\''.$registration.'\');"><img class="gb-icon-img" src="'.plugins_url().'/grassblade/img/stats.png" width="20px"></a>';
		}
		return $scores;
	}
    static function is_enabled($content_id) {
        $content_data = grassblade_xapi_content::get_params($content_id);
        return !empty($content_data["show_rich_quiz_report"]);
    }

} // end of class

$gb_rqr = new gb_rich_quiz_report();





