<?php 

// essential functions
require_once "subs.php";

class grassblade_scorm {

	function __construct() {

		add_filter( 'grassblade_content_versions', array($this,'add_scorm_versions'),10, 1);

		add_filter( 'grassblade_process_upload', array($this,'process_scorm_upload'), 10 , 3);

		add_action( 'wp_ajax_nopriv_grassblade_scorm', array( $this,'scorm_launch') );
		add_action( 'wp_ajax_grassblade_scorm', array( $this,'scorm_launch') );

		add_action( 'wp_ajax_nopriv_grassblade_scorm_commit', array( $this,'scorm_commit') );
		add_action( 'wp_ajax_grassblade_scorm_commit', array( $this,'scorm_commit') );

		add_action( 'wp_ajax_nopriv_grassblade_scorm_finish', array( $this,'scorm_finish') );
		add_action( 'wp_ajax_grassblade_scorm_finish', array( $this,'scorm_finish') );

		add_filter("grassblade_shortcode_atts", array($this, "grassblade_scorm_shortcode_atts"), 3, 2);

	}

	/**
	 * Add Scorm Versions to the content version List.
	 *
	 * @param array $versions List of existing versions.
	 *
	 * @return array $versions with scorm versions.
	 */
	function add_scorm_versions($versions) {
		$versions["scorm1.2"] = 'SCORM 1.2';
		$versions["scorm2004"] = 'SCORM 2004';
		return $versions;
	}

	/**
	 * Add Scorm Versions to the content version List.
	 *
	 * @param array $params.
	 * @param obj $post.
	 * @param array $upload with required index $upload['content_path'] and $upload['content_url'].
	 *
	 * @return array $params with scorm versions.
	 */
	function process_scorm_upload($params, $post , $upload) {

		if ( empty($params['process_status']) && isset($upload['content_path']) && is_dir($upload["content_path"]) ) {

			$imsmanifestxml_subdir = $this->get_imsmanifestxml($upload['content_path']);
						
			if(empty($imsmanifestxml_subdir))
			$imsmanifest_file = $upload['content_path'].DIRECTORY_SEPARATOR."imsmanifest.xml";
			else
			$imsmanifest_file = $upload['content_path'].DIRECTORY_SEPARATOR.$imsmanifestxml_subdir.DIRECTORY_SEPARATOR."imsmanifest.xml";

			if(file_exists($imsmanifest_file))
			{
				$scorm_version_ar = get_gb_scormversion($imsmanifest_file);

				$scorm_version = 'scorm1.2';

			    if (isset($scorm_version_ar['schemaversion'])) {
			    	if ( (strpos($scorm_version_ar['schemaversion'], '2004') !== false) ||  (trim($scorm_version_ar['schemaversion']) == 'CAM 1.3') ) {
						$scorm_version = 'scorm2004';
					}
				}

				$mastery_score = get_gb_masteryscore($imsmanifest_file);

				$SCOdata = gb_read_imsmanifestfile($imsmanifest_file);
				$ORGdata = gb_getORGdata($imsmanifest_file);

				$i = 0;
				foreach ($SCOdata as $identifier => $SCO)
				{
					$page[$i] = gb_cleanVar($SCO['href']);
					$i++;
				}

				foreach ($ORGdata as $identifier => $ORG)
				{
					if ($ORG['identifierref'] != ''){
						$key_ref=0;
						foreach ($SCOdata as $identifier_temp => $SCO)	{
							if ($identifier_temp == $identifier ){
								break;
							} else {
								$key_ref++;
							}
						}
						if ($key_ref >= 0 ){
							$launch_file = $page[$key_ref];

							if(empty($upload['activity_id']))
								$upload['activity_id'] = $upload['content_url'];

							$upload['launch_path'] = dirname($imsmanifest_file).DIRECTORY_SEPARATOR.$launch_file;

							if(empty($imsmanifestxml_subdir))
							$upload['src'] =  $upload['content_url'].'/'.$launch_file;
							else
							$upload['src'] =  $upload['content_url'].'/'.$imsmanifestxml_subdir.'/'.$launch_file;

							if(!file_exists($upload['launch_path']))
								return array("response" => 'error', "info" => 'Error: <i>'.$upload['launch_path'].'</i>. Launch file not found in package');

							$upload['version'] = $scorm_version;
							$upload["content_type"] = "scorm";
							$upload["mastery_score"] = $mastery_score;
							$upload['process_status'] = 1;

						} else { 
							return array("response" => 'error', "info" => "XML Error:  Launch file reference not found in imsmanifest.xml");
						}
					}
				}
			}
			foreach($upload as $k=>$v)
				$params[$k] = addslashes($v);
		}

		if(empty($params['process_status'])) {
			if(isset($params["src"]))
				unset($params["src"]);
			if(isset($params["launch_path"]))
				unset($params["launch_path"]);
			if(isset($params["mastery_score"]))
				unset($params["mastery_score"]);
		}

		return $params;
	}

	function get_imsmanifestxml($dir) {
		$imsmanifestxml_file = $dir.DIRECTORY_SEPARATOR."imsmanifest.xml";
		
		if(file_exists($imsmanifestxml_file))
			return "";
		else
		{
			$dirlist = scandir($dir);
			foreach($dirlist as $d)
			{
				if($d != "." && $d != "..")
				{
					$imsmanifestxml_file = $dir.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR."imsmanifest.xml";
					if(file_exists($imsmanifestxml_file))
						return $d;
				}
			}
		}
		return 0;
	}

	function scorm_launch() {
		$content_id = $_REQUEST['content_id'];
		$content_data = get_post_meta($content_id, "xapi_content", true);

		$registration_id = $_REQUEST['registration'];

		$user = wp_get_current_user();
		$user_id = $user->ID;
		$user_name = !empty($user->data->display_name)? $user->data->display_name: $user->data->user_login;

		/*
		TODO: Check Actor against user id

		$actor = json_decode(stripslashes($_REQUEST['actor']),true);
		$user_email = grassblade_get_actor_id($actor);
		$user = get_user_by('email',$user_email);

		if (!empty($current_user_id)) { //Not Guest
			if($current_user_id != $user) {
				//Error
			}
		}
		else //Guest 
		{

		}
		*/

		if ($content_data['version'] == 'scorm2004') {
			$scorm_content_version = '2004';
		} else {
			$scorm_content_version = '1.2';
		}

		$scorm_data = gb_get_scorm_data($user_id, $content_id, $registration_id);

		//var_dump($_REQUEST);
		//var_dump($scorm_data);
		//var_dump($content_data); exit;
		?>
		<html>
		<head>
		<script>
			var GB_SCORM = {};
			GB_SCORM.content_provider = [];
			GB_SCORM.all_ajax_content = [];
			window.GB_SCORM.interaction_index = [];
    		window.GB_SCORM.completion_stmt = [];
			GB_SCORM.mastery_score = '<?php echo $content_data['mastery_score']; ?>';
			GB_SCORM.activity_id = '<?php echo $content_data['activity_id']; ?>';
		    GB_SCORM.scorm_version = <?php echo $scorm_content_version; ?>;
		    GB_SCORM.ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			var cache = new Object();
			var scorm_data = <?php echo json_encode($scorm_data); ?>;
		    if(GB_SCORM.scorm_version =='1.2'){

				// Content , User and registration information
				cache['cmi.core.student_id']= <?php echo $user_id; ?>;
				cache['cmi.core.student_name']= '<?php echo $user_name; ?>';
				cache['cmi.core.content_id']= <?php echo $content_id; ?>;
				cache['cmi.core.registration_id']= '<?php echo $registration_id; ?>';

				// test score
				cache['cmi.core.score.raw']= (scorm_data['cmi.core.score.raw'] === undefined) ? '' :scorm_data['cmi.core.score.raw'];
				cache['adlcp:masteryscore']= GB_SCORM.mastery_score;

				// SCO launch and suspend data
				cache['cmi.launch_data']= (scorm_data['cmi.launch_data'] === undefined) ? '' :scorm_data['cmi.launch_data'];
				cache['cmi.suspend_data']= (scorm_data['cmi.suspend_data'] === undefined) ? '' :scorm_data['cmi.suspend_data'];

				// progress and completion tracking
				cache['cmi.core.lesson_location']= (scorm_data['cmi.core.lesson_location'] === undefined) ? '' :scorm_data['cmi.core.lesson_location'];
				cache['cmi.core.credit']= (scorm_data['cmi.core.credit'] === undefined) ? 'credit' :scorm_data['cmi.core.credit'];
				cache['cmi.core.lesson_status']= (scorm_data['cmi.core.lesson_status'] === undefined) ? 'not attempted' :scorm_data['cmi.core.lesson_status'];
				cache['cmi.core.entry']= (scorm_data['cmi.core.entry'] === undefined) ? 'ab-initio' :scorm_data['cmi.core.entry'];
				cache['cmi.core.exit']=  (scorm_data['cmi.core.exit'] === undefined) ? '' :scorm_data['cmi.core.exit'];

				// seat time
				cache['cmi.core.total_time']= (scorm_data['cmi.core.total_time'] === undefined) ? '0000:00:00' :scorm_data['cmi.core.total_time'];
				cache['cmi.core.session_time']= (scorm_data['cmi.core.session_time'] === undefined) ? '' :scorm_data['cmi.core.session_time'];
				cache['cmi.interactions._count']= (scorm_data['cmi.interactions._count'] === undefined) ? '0' :scorm_data['cmi.interactions._count'];

			} else {

				// Content , User and registration information
				cache['cmi.learner_id']= <?php echo $user_id ; ?>;
				cache['cmi.learner_name']= '<?php echo $user_name ; ?>';
				cache['cmi.content_id']= <?php echo $content_id; ?>;
				cache['cmi.registration_id']= '<?php echo $registration_id; ?>';

				// test score
				cache['cmi.score.raw']= (scorm_data['cmi.score.raw'] === undefined) ? '' :scorm_data['cmi.score.raw'];
				cache['adlcp:masteryscore']= GB_SCORM.mastery_score;

				// SCO launch and suspend data
				cache['cmi.launch_data']=  (scorm_data['cmi.launch_data'] === undefined) ? '' :scorm_data['cmi.launch_data'];
				cache['cmi.suspend_data']= (scorm_data['cmi.suspend_data'] === undefined) ? '' :scorm_data['cmi.suspend_data'];

				// progress and completion tracking
				cache['cmi.location']= (scorm_data['cmi.location'] === undefined) ? '' :scorm_data['cmi.location'];
				cache['cmi.credit']= (scorm_data['cmi.credit'] === undefined) ? 'credit' :scorm_data['cmi.credit'];
				cache['cmi.completion_status']= (scorm_data['cmi.completion_status'] === undefined) ? 'not attempted' :scorm_data['cmi.completion_status'];
				cache['cmi.lesson_status']= (scorm_data['cmi.lesson_status'] === undefined) ? '' :scorm_data['cmi.lesson_status'];
				cache['cmi.entry']= (scorm_data['cmi.entry'] === undefined) ? 'ab-initio' :scorm_data['cmi.entry'];
				cache['cmi.exit']= (scorm_data['cmi.exit'] === undefined) ? '' :scorm_data['cmi.exit'];

				// seat time
				cache['cmi.total_time']= (scorm_data['cmi.total_time'] === undefined) ? '0000:00:00' :scorm_data['cmi.total_time'];
				cache['cmi.session_time']= (scorm_data['cmi.session_time'] === undefined) ? '' :scorm_data['cmi.session_time'];
				cache['cmi.interactions._count']= (scorm_data['cmi.interactions._count'] === undefined) ? '0' :scorm_data['cmi.interactions._count'];
			}
			GB_SCORM.cache = cache;
			GB_SCORM.scorm_version_ar = ['1.2']; 
		</script>
		<script src="<?php echo plugins_url('/js/scorm.js', __FILE__); ?>" type="text/javascript"></script>
		<?php /* $dec
		<script src="<?php echo plugins_url('/js/jquery-3.4.1.min.js', __FILE__); ?>" type="text/javascript"></script>
		<script src="<?php echo plugins_url('/js/json_library.js', __FILE__); ?>" type="text/javascript"></script>
		<script src="<?php echo plugins_url('/js/rte_functions.js', __FILE__); ?>" type="text/javascript"></script>
		<script src="<?php echo plugins_url('/js/xapiwrapper.min.js', __FILE__); ?>" type="text/javascript"></script>
		<script src="<?php echo plugins_url('/js/SCORMToXAPIFunctions.js', __FILE__); ?>" type="text/javascript"></script>
		*/ ?>
		<?php if($scorm_content_version != '1.2'){ ?>
		    <script src="<?php echo plugins_url('/js/rte_2004.min.js', __FILE__); ?>" type="text/javascript"></script>
		<?php } else { ?>
		    <script src="<?php echo plugins_url('/js/rte_1.2.min.js', __FILE__); ?>" type="text/javascript"></script>
		<?php } ?>
		</head>
		<?php
		if($scorm_content_version != '1.2'){
		    echo '<frameset frameborder="0" framespacing="0" border="0" rows="*" cols="*" onbeforeunload="API_1484_11.Terminate(\'\');" onunload="API_1484_11.Terminate(\'\');">';
		        echo '<frame src="'.utf8_encode($content_data['src']).'" name="course">';
		    echo '</frameset>';
		} else {
			echo '<frameset frameborder="0" framespacing="0" border="0" rows="*" cols="*" onbeforeunload="API.LMSFinish(\'\');" onunload="API.LMSFinish(\'\');">';
		        echo '<frame src="'.utf8_encode($content_data['src']).'" name="course">';
		    echo '</frameset>';
		}
		echo "</html>";
	}

	function scorm_commit() {

		$params = $_REQUEST['params'];
		$data = $params['data'];
		$scorm_version = $params['scorm_version'];

		// iterate through the data elements
		if ($scorm_version == '1.2') {
		    foreach ($data as $varname => $varvalue) {
		        if ($varname == 'cmi.core.entry') {
		            if ($data['cmi.core.exit'] == 'suspend' && ($data['cmi.core.lesson_status'] != 'completed' || $data['cmi.core.lesson_status'] != 'passed')) {
		                gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.entry','resume');
		            } elseif ($data['cmi.core.lesson_status'] == 'completed' || $data['cmi.core.lesson_status'] == 'passed') {
		                gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.entry','');
		            }
		        } else {
		            // save data to the 'scormvars' table
		            gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], $varname, $varvalue);
		        }
		    }
		} else {
			foreach ($data as $varname => $varvalue) {
				if ($varname == 'cmi.entry'){
		        	if ($data['cmi.exit'] == 'suspend' && ($data['cmi.completion_status'] != 'completed' )) {
		        		gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.entry','resume');
		        	}
		        	else if($data['cmi.completion_status'] == 'completed') {
		        		gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.entry','');
		        	}
		        } else {
		    		// save data to the 'scormvars' table
		    		gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], $varname, $varvalue);
		    	}
			}
		}
		echo json_encode("Commit Success");
		exit();
	}

	function scorm_finish(){

		$params = $_REQUEST['params'];
		$data = $params['data'];
		$scorm_version = $params['scorm_version'];

		if ($scorm_version == '1.2') {
		    // find existing value of cmi.core.lesson_status
		    $lessonstatus = trim(gb_get_scorm_data_key_value($data['cmi.core.student_id'], $data['cmi.core.content_id'],$data['cmi.core.registration_id'], 'cmi.core.lesson_status') );

		    // if it's 'not attempted', change it to 'completed'
		    if ($lessonstatus == 'not attempted') {
		    	gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.lesson_status', 'completed');
		    }
		    // has a mastery score been specified in the IMS manifest file?
		    $masteryscore = gb_get_scorm_data_key_value($data['cmi.core.student_id'], $data['cmi.core.content_id'],$data['cmi.core.registration_id'], 'adlcp:masteryscore');
		    $masteryscore *= 1;
		    // ------------------------------------------------------------------------------------
		    // set cmi.core.entry based on the value of cmi.core.exit

		    // clear existing value
		    gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.entry', '');

		    // new entry value depends on exit value
		    $exit = gb_get_scorm_data_key_value($data['cmi.core.student_id'], $data['cmi.content_id'],$data['cmi.core.registration_id'], 'cmi.core.exit');
		    if ( ($exit === 'suspend' && $lessonstatus != 'completed') || ($exit === 'suspend' && $lessonstatus != 'passed') ) {
		        gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.entry', 'resume');
		    } elseif (($exit == 'suspend' && $lessonstatus == 'completed') || ($exit == 'suspend' && $lessonstatus == 'passed')) {
		        gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.entry', '');
		    }
		    if ($masteryscore) {
		        // yes - so read the score
		        $rawscore = gb_get_scorm_data_key_value($data['cmi.core.student_id'], $data['cmi.core.content_id'],$data['cmi.core.registration_id'], 'cmi.core.score.raw');
		        $rawscore *= 1;
		    }
		    // ------------------------------------------------------------------------------------
		    // process changes to cmi.core.total_time

		    // read cmi.core.total_time from the 'scormvars' table
		    $totaltime = gb_get_scorm_data_key_value($data['cmi.core.student_id'], $data['cmi.core.content_id'],$data['cmi.core.registration_id'], 'cmi.core.total_time');

		    // convert total time to seconds
		    $time = explode(':', $totaltime);
		    $totalseconds = $time[0]*60*60 + $time[1]*60 + $time[2];

		    // read the last-set cmi.core.session_time from the 'scormvars' table
		    $sessiontime = gb_get_scorm_data_key_value($data['cmi.core.student_id'], $data['cmi.core.content_id'],$data['cmi.core.registration_id'], 'cmi.core.session_time');

		    // no session time set by SCO - set to zero
		    if (!$sessiontime) {
		        $sessiontime = "00:00:00";
		    }

		    // convert session time to seconds
		    $time = explode(':', $sessiontime);
		    $sessionseconds = $time[0]*60*60 + $time[1]*60 + $time[2];

		    // new total time is ...
		    $totalseconds += $sessionseconds;

		    // break total time into hours, minutes and seconds
		    $totalhours = intval($totalseconds / 3600);
		    $totalseconds -= $totalhours * 3600;
		    $totalminutes = intval($totalseconds / 60);
		    $totalseconds -= $totalminutes * 60;

		    // reformat to comply with the SCORM data model
		    $totaltime = sprintf("%04d:%02d:%02d", $totalhours, $totalminutes, $totalseconds);

		    // save new total time to the 'scormvars' table
		    gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.total_time', $totaltime);

		    // delete the last session time
		    gb_set_scorm_data($data['cmi.core.student_id'], $data['cmi.core.content_id'], $data['cmi.core.registration_id'], 'cmi.core.session_time', '');
		} else {
		    // find existing value of cmi.completion_status
		    $lessonstatus = trim(gb_get_scorm_data_key_value($data['cmi.learner_id'], $data['cmi.content_id'],$data['cmi.registration_id'], 'cmi.completion_status')); 
		    // if it's 'not attempted', change it to 'completed'
		    if ($lessonstatus == 'not attempted') {
		    	gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.completion_status', 'completed');
		    }
		    // has a mastery score been specified in the IMS manifest file?
		    $masteryscore = gb_get_scorm_data_key_value($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.scaled_passing_score');
		    $masteryscore *= 1;
		    
		    
		    // ------------------------------------------------------------------------------------
		    // set cmi.core.entry based on the value of cmi.core.exit
		    
		    // clear existing value
		    gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.entry', '');
		    
		    // new entry value depends on exit value
		    $exit = gb_get_scorm_data_key_value($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.exit');
		    if (($exit === 'suspend' && $lessonstatus != 'completed') || ($exit === 'suspend' && $lessonstatus != 'passed')) {
		        gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.entry', 'resume');
		    } 
		    
		    elseif (($exit == 'suspend' && $lessonstatus == 'completed') || ($exit == 'suspend' && $lessonstatus == 'passed')) {
		        gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.entry', '');
		    }
		    if ($masteryscore) {
		    
		        // yes - so read the score
		        $rawscore = gb_get_scorm_data_key_value($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.score.raw');
		        $rawscore *= 1;
		    }
		    // ------------------------------------------------------------------------------------
		    // process changes to cmi.core.total_time
		    
		    // read cmi.core.total_time from the 'scormvars' table
		    $totaltime = gb_get_scorm_data_key_value($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.total_time');
		    
		    // convert total time to seconds
		    $time = explode(':', $totaltime);
		    $totalseconds = $time[0]*60*60 + $time[1]*60 + $time[2];
		    
		    // read the last-set cmi.core.session_time from the 'scormvars' table
		    $sessiontime = gb_get_scorm_data_key_value($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.session_time');
		    
		    // no session time set by SCO - set to zero
		    if (! $sessiontime) {
		        $sessiontime = "00:00:00";
		    }
		    
		    // convert session time to seconds
		    $time = explode(':', $sessiontime);
		    $sessionseconds = $time[0]*60*60 + $time[1]*60 + $time[2];
		    
		    // new total time is ...
		    $totalseconds += $sessionseconds;
		    
		    // break total time into hours, minutes and seconds
		    $totalhours = intval($totalseconds / 3600);
		    $totalseconds -= $totalhours * 3600;
		    $totalminutes = intval($totalseconds / 60);
		    $totalseconds -= $totalminutes * 60;
		    
		    // reformat to comply with the SCORM data model
		    $totaltime = sprintf("%04d:%02d:%02d", $totalhours, $totalminutes, $totalseconds);
		    
		    // save new total time to the 'scormvars' table
		    gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.total_time', $totaltime);
		    
		    // delete the last session time
		    gb_set_scorm_data($data['cmi.learner_id'], $data['cmi.content_id'], $data['cmi.registration_id'], 'cmi.session_time', '');
		}
		// ------------------------------------------------------------------------------------
		echo json_encode("Finish Success");
		exit();
	}

	function grassblade_scorm_shortcode_atts($shortcode_atts, $attr) {

		if(!empty($shortcode_atts["id"]) && in_array($shortcode_atts["version"], array("scorm1.2", "scorm2004"))) {
			$content_id = $shortcode_atts["id"];
			$shortcode_atts["src"] = admin_url('admin-ajax.php').'?action=grassblade_scorm&content_id='.$content_id;
		}
		return $shortcode_atts;
	}

} // end of grassblade_scorm class

$gb_scorm = new grassblade_scorm();