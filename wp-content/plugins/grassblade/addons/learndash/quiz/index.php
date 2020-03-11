<?php
/*
1. attempted: action = wp_pro_quiz_load_quiz_data, quizId = pro_quiz_id
2. On Clicking "Check" Answer
action:ld_adv_quiz_pro_ajax
func:checkAnswers
data[quizId]:9
data[responses]:{"78":{"response":{"0":false,"1":true,"2":false}}}


78 :{c:false
	e:{AnswerMessage:"<p>Wrong! The sky is blue!</p>↵"
		c:[1, 0, 0]
		possiblePoints:1
		r:[false, true, false]
		type:"single"
		}
	p:0
	s:{}
}

3. On Automatically Completion due to time end:

action:ld_adv_quiz_pro_ajax
func:checkAnswers
data[quizId]:9
data[responses]:{"77":{"response":{"0":false,"1":false,"2":false,"3":false}},"79":{"response":{"0":"","1":"","2":"","3":"","4":"","5":"","6":"","7":""}},"80":{"response":""},"81":{"response":{"0":"5b6ba13f79129a74a3e819b78e36b922","1":"155fa09596c7e18e50b58eb7e0c6ccb4","2":"09b15d48a1514d8209b192a8b8f34e48","3":"f542eae1949358e25d8bfeefe5b199f1","4":"6e79ed05baec2754e25b4eac73a332d2"}},"82":{"response":{"0":-1}}}

77:{c: false, p: 0, s: {}, e: {type: "multiple", r: [false, false, false, false], c: [1, 0, 1, 1],…}}
79:{c: false, p: 0, s: {0: false, 1: false, 2: false, 3: false, 4: false, 5: false, 6: false, 7: false},…}
80:{c: false, p: 0, s: {}, e: {type: "free_answer", r: "", c: ["yellow"],…}}
81:{c: false, p: 0, s: {},…}
82:{c: false, p: 0, s: {}, e: {type: "matrix_sort_answer",…}}



3. Completed/Passed/Failed:
action:wp_pro_quiz_completed_quiz
quiz:1724
quizId:9
results:{"77":{"time":0,"points":0,"correct":0,"data":{"0":0,"1":0,"2":0,"3":0},"possiblePoints":2},"78":{"time":83,"points":0,"correct":0,"data":{"0":0,"1":1,"2":0},"possiblePoints":1},"79":{"time":0,"points":0,"correct":0,"data":{"0":"","1":"","2":"","3":"","4":"","5":"","6":"","7":""},"possiblePoints":8},"80":{"time":0,"points":0,"correct":0,"data":{"0":""},"possiblePoints":1},"81":{"time":0,"points":0,"correct":0,"data":["5b6ba13f79129a74a3e819b78e36b922","155fa09596c7e18e50b58eb7e0c6ccb4","09b15d48a1514d8209b192a8b8f34e48","f542eae1949358e25d8bfeefe5b199f1","6e79ed05baec2754e25b4eac73a332d2"],"possiblePoints":1},"82":{"time":0,"points":0,"correct":0,"data":[-1],"possiblePoints":1},"comp":{"points":0,"correctQuestions":0,"quizTime":180,"result":0,"cats":{}}}
timespent:180
forms[24]:
forms[25]:
forms[26]:
forms[27]:
forms[28]:
NOTE: Passed/Failed is already processed using learndash_quiz_completed trigger? 
*/
class grassblade_learndash_quiz {
	public $debug = false;
	public $NSS_CMI_XAPI;
	public $quiz_actor;

	function __construct() {
		//Loaded only when ajax is running

		if(!class_exists("NSS_CMI_XAPI_V2"))
		include_once(dirname(__FILE__)."/../../nss_cmi_xapi.class.php");
		
		add_action("init", array($this, "init"));
		add_action('wp_ajax_wp_pro_quiz_load_quiz_data', array($this, 'attempted'), 0, 9);
		add_action("ldadvquiz_answered", array($this, "answered"), 10, 3);
	}
	function init() {

	    $grassblade_tincan_endpoint = get_option( 'grassblade_tincan_endpoint' );
		$grassblade_tincan_user = get_option('grassblade_tincan_user');
		$grassblade_tincan_password = get_option('grassblade_tincan_password');
		$grassblade_tincan_track_guest = get_option('grassblade_tincan_track_guest');		
		
		$this->NSS_CMI_XAPI = new NSS_CMI_XAPI_V2($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
		
		$this->quiz_actor = grassblade_getactor($grassblade_tincan_track_guest);
		$this->log(array("Actor" => $this->quiz_actor));
		
		if(empty($this->quiz_actor))
		{
			$this->log("No Actor. Shutting Down.");
			return;
		}
	}
	function attempted() {
		$pro_quiz_id = (int)$_POST['quizId'];
		$userId = get_current_user_id();
		
		$quizMapper = new WpProQuiz_Model_QuizMapper();

		$quiz = $quizMapper->fetch($pro_quiz_id);
		$quiz_title = $quiz->getName();

		if(function_exists("learndash_get_quiz_id_by_pro_quiz_id")) 
		{
			$ld_quiz_id = learndash_get_quiz_id_by_pro_quiz_id( $pro_quiz_id );
			$ld_quiz = get_post($ld_quiz_id);
			$quiz_id_url = grassblade_post_activityid($ld_quiz_id); 
	
			$course_id = grassblade_learndash_get_course_id($ld_quiz_id);
			$course = get_post($course_id);
			$grouping_id_url = grassblade_post_activityid($course_id); 
			$grouping_title = $course->post_title;
			$grouping_type = 'http://adlnet.gov/expapi/activities/course';
		}
		else {
			$quiz_id_url = str_replace("https://", "http://", site_url())."?ld_pro_quiz_id=".$pro_quiz_id; 
			$grouping_id_url = $quiz_id_url;
			$grouping_title = $quiz_title;
			$grouping_type = 'http://adlnet.gov/expapi/activities/assessment';
		}	

		//Statement Object
		$this->NSS_CMI_XAPI->new_statement();
		$this->NSS_CMI_XAPI->set_actor_by_object($this->quiz_actor);
		$this->NSS_CMI_XAPI->set_verb("attempted");
		$this->NSS_CMI_XAPI->set_object($quiz_id_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment');

		//Context: Set Context Parent and Group. 
		$this->NSS_CMI_XAPI->set_parent($quiz_id_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment','Activity');
		if( !empty($grouping_id_url) )
		$this->NSS_CMI_XAPI->set_grouping($grouping_id_url, $grouping_title, $grouping_title, $grouping_type,'Activity');
		
		$this->NSS_CMI_XAPI->build_statement();
		$this->NSS_CMI_XAPI->new_statement();

		if(!empty($this->NSS_CMI_XAPI->statements))
		{
			$ret = $this->NSS_CMI_XAPI->SendStatements($this->NSS_CMI_XAPI->statements);
			$this->NSS_CMI_XAPI->statements = array();
		}
	}
	function log($msg) { 
		if ( isset( $_GET['debug'] ) || ! empty( $this->debug ) ) {
			
			$original_log_errors = ini_get( 'log_errors' );
			$original_error_log  = ini_get( 'error_log' );
			ini_set( 'log_errors', true );
			ini_set( 'error_log', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'debug.log' );

			global $processing_id;

			if ( empty( $processing_id ) ) {
				$processing_id = time();
			}

			error_log( "[$processing_id] " . print_r( $msg, true ) );
			//Comment This line to stop logging debug messages.

			ini_set( 'log_errors', $original_log_errors );
			ini_set( 'error_log', $original_error_log );
		}
	}
	function answered($results, $quiz, $questionModels) {
		$this->log($results);
		$pro_quiz_id = $quiz->getId();

		//TO DO: Uncomment this after testing
	//	if(empty($results) || empty($pro_quiz_id) || empty($this->quiz_actor))
	//		return;

		$quiz_title = $quiz->getName();

		if(function_exists("learndash_get_quiz_id_by_pro_quiz_id")) 
		{
			$ld_quiz_id = learndash_get_quiz_id_by_pro_quiz_id( $pro_quiz_id );
			$ld_quiz = get_post($ld_quiz_id);
			$quiz_id_url = grassblade_post_activityid($ld_quiz_id); 
	
			$course_id = grassblade_learndash_get_course_id($ld_quiz_id);
			$course = get_post($course_id);
			$grouping_id_url = grassblade_post_activityid($course_id); 
			$grouping_title = $course->post_title;
			$grouping_type = 'http://adlnet.gov/expapi/activities/course';
		}
		else {
			$quiz_id_url = str_replace("https://", "http://", site_url())."?ld_pro_quiz_id=".$pro_quiz_id; 
			$grouping_id_url = $quiz_id_url;
			$grouping_title = $quiz_title;
			$grouping_type = 'http://adlnet.gov/expapi/activities/assessment';
		}	

		foreach ($results as $question_id => $r) {
			//Statement Object
			$this->NSS_CMI_XAPI->new_statement();
			$this->NSS_CMI_XAPI->set_actor_by_object($this->quiz_actor);
			$this->NSS_CMI_XAPI->set_verb("answered");

			//Context: Set Context Parent and Group. 
			$this->NSS_CMI_XAPI->set_parent($quiz_id_url, $quiz_title, $quiz_title, 'http://adlnet.gov/expapi/activities/assessment','Activity');
			if( !empty( $grouping_id_url ) )
			$this->NSS_CMI_XAPI->set_grouping($grouping_id_url, $grouping_title, $grouping_title, $grouping_type,'Activity');


			foreach ($questionModels as $questionModel) {
				if($questionModel->getId() == $question_id)
					break;
			}
			
			//print_r($questionModel);

			$question_id_url = $quiz_id_url."&id=".$question_id;
			$question = trim(strip_tags($questionModel->getQuestion()));
			
			//TO DO: OBJECT =
				//TO DO: OBJECT ID = Question ID URL

				//TO DO: OBJECT DEFINITION =
					//TO DO: OBJECT DEFINITION Name = question
					//TO DO: OBJECT DEFINITION DESCRIPTION = Can omit or find what to include in this. 
					//TO DO: OBJECT DEFINITION type = "http://adlnet.gov/expapi/activities/cmi.interaction"
					//TO DO: OBJECT DEFINITION interactionType = choice, sequencing, likert, matching, performance, true-false, fill-in, long-fill-in, numeric, other
					//TO DO: OBJECT DEFINITION correctResponsesPattern
					//TO DO: OBJECT DEFINITION choices | scale | source | target | steps

			$type = @$r["e"]["type"];
			if(empty($type) || !in_array($type, array("single", "free_answer", "multiple", "sort_answer", "matrix_sort_answer", "cloze_answer", "assessment_answer", "essay")))
			{
				$this->NSS_CMI_XAPI->set_object($question_id_url, $question, $question, 'http://adlnet.gov/expapi/activities/question','Activity');
			}
			else
			{
				switch ($type) {
					case 'free_answer':
						$correctResponse = @$r["e"]["c"][0];
						$response = @$r["e"]["r"];
						$this->NSS_CMI_XAPI->set_object_type_fill_in($question_id_url, $question, $correctResponse);
						break;
					
					case 'single':
					case 'multiple':
						$question_choices = $questionModel->getAnswerData();
						$choices = $choices_indexes = array();
						foreach ($question_choices as $answer_index => $answer) {
							$choice = strip_tags($answer->getAnswer());
							if(empty($choice))
							$choice = $answer_index;
							
							$index = $answer_index."-".substr(str_replace(array(",",":"," "), "", $choice), 0, 8);
							$index = preg_replace('~\x{00a0}~siu', '', $index);
							$index = preg_replace('/\s+/', '', $index);
							
							$choices[$index] = $choice;
							$choices_indexes[$answer_index] = $index;
						}
						$correctResponse = array();
						foreach ($r["e"]["c"] as $answer_index => $is_correct) {
							if($is_correct)
								$correctResponse[] = $choices_indexes[$answer_index];
						}
						$correctResponse = implode("[,]", $correctResponse);
						$response = array();
						foreach ($r["e"]["r"] as $answer_index => $is_correct) {
							if($is_correct)
								$response[] = $choices_indexes[$answer_index];
						}
						$response = implode("[,]", $response);
						$this->NSS_CMI_XAPI->set_object_type_choice($question_id_url, $question, $correctResponse, $choices);
						break;

					case 'sort_answer': //sequencing
						$question_choices = $questionModel->getAnswerData();
						$choices = $choices_indexes = array();
						foreach ($question_choices as $answer_index => $answer) {
							$choice = strip_tags($answer->getAnswer());
							if(empty($choice))
							$choice = $answer_index;

							$index = $answer_index."-".substr(str_replace(array(",",":"," "), "", $choice), 0, 8);
							$index = preg_replace('~\x{00a0}~siu', '', $index);
							$index = preg_replace('/\s+/', '', $index);
							
							$choices[$index] = $choice;
							$choices_indexes[$answer_index] = $index;
						}
						$correctResponse = array();
						foreach ($r["e"]["c"] as $answer_index => $is_correct) {
							if($is_correct)
								$correctResponse[] = $choices_indexes[$answer_index];
						}
						$correctResponse = implode("[,]", $correctResponse);
						$response = array();
						foreach ($r["e"]["r"] as $answer_index => $is_correct) {
							if($is_correct)
								$response[] = $choices_indexes[$answer_index];
						}
						$response = implode("[,]", $response);
						$this->NSS_CMI_XAPI->set_object_type_sequencing($question_id_url, $question, $correctResponse, $choices);
						break;

					case 'matrix_sort_answer': //matching
						$question_match_choices = $questionModel->getAnswerData();
						$source = $source_indexes = $target = $target_indexes = $correctResponse = array();
						foreach ($question_match_choices as $answer_index => $answer) {
							$source_e = strip_tags($answer->getAnswer());
							if(empty($source_e))
							$source_e = $answer_index;

							$source_index = $answer_index."S-".substr(str_replace(array(",",":"), "", $source_e), 0, 8);
							$source_index = preg_replace('~\x{00a0}~siu', '', $source_index);
							$source_index = preg_replace('/\s+/', '', $source_index);
							$source[$source_index] = (string) $source_e;
							$source_indexes[$answer_index] = (string) $source_index;
							
							$target_e = strip_tags($answer->getSortString());
							if(empty($target_e))
							$target_e = $answer_index;
							$target_e = preg_replace('~\x{00a0}~siu', '', $target_e);

							$target_index = $answer_index."T-".substr(str_replace(array(",",":"), "", $target_e), 0, 8);
							$target_index = preg_replace('/\s+/', '', $target_index);

							$target[$target_index] = (string) $target_e;
							$target_indexes[$answer_index] = (string) $target_index;
							
							$correctResponse[] = $source_index."[.]".$target_index;
						}

						$response = array();
						$responses_arr 	= @$r["e"]["r"];
						$correct_arr	= @$r["e"]["c"];

						foreach ($r["e"]["r"] as $answer_index => $hash) {
							$correct_index = array_search($hash, $correct_arr);
							if($correct_index !== false) {
								$response[] = $source_indexes[$answer_index]."[.]".$target_indexes[$correct_index];
							}
						}

						$correctResponse = implode("[,]", $correctResponse);
						$response = implode("[,]", $response);

						$this->NSS_CMI_XAPI->set_object_type_matching($question_id_url, $question, $correctResponse, $source, $target);
						break;

					case 'cloze_answer':
						$responses_arr 	= @$r["e"]["r"];
						$response = implode("[,]", $responses_arr);

						$correct_arr	= @$r["e"]["c"];
						foreach ($correct_arr as $index => $correct_val) {
							$correct_arr[$index] = implode("|", $correct_val);
						}
						$correctResponse = implode("[,]", $correct_arr);
						$this->NSS_CMI_XAPI->set_object_type_fill_in($question_id_url, $question, $correctResponse);
						break;

					case 'assessment_answer':
						$answerData = $questionModel->getAnswerData();
						$answer = $answerData[0]->getAnswer();
						preg_match_all( '#\{(.*?)\}#im', $answer, $matches );
						preg_match_all( '#\[(.*?)\]#im', @$matches[1][0], $options );
						
						if(empty($options) || !is_array($options))
							return;

						$first = trim(substr($answer, 0, strpos($answer, "{")));
						$last = trim(substr($answer, strpos($answer, "}") + 1));

						$choices = array();
						$i = 0;
						foreach ($options[1] as $key => $option) {
							if($i == 0) 
								$option = $option . " - " . $first;
							if($i == count($options[1]) - 1) 
								$option = $option . " - " . $last;

							$choices["likert_".$i] = $option;
							$i++;
						}
						if(!empty($r["p"]))
						$response = "likert_" . (intval($r["p"]) - 1);
						$this->NSS_CMI_XAPI->set_object_type_likert($question_id_url, $question, null, $choices);
						
						break;
					case 'essay': //long-fill-in
						$responses = json_decode(stripcslashes($_REQUEST["data"]["responses"]));
						$response = $responses->{$question_id}->response;
						$this->NSS_CMI_XAPI->set_object_type_long_fill_in($question_id_url, $question, null);

						$graded_status = @$r["e"]["graded_status"];
						if($graded_status == "not_graded")
						{
							unset($r["c"]);
							unset($r["p"]);
							unset($r["e"]["possiblePoints"]);
						}
					break;
					

					default:
						
						break;
				}
			}

			//Statement Result 
			$result = array();

			if(isset($response))
			{
				$result["response"] = $response;
			}
			if(isset($r["c"])) 
			$result["success"] = !empty($r["c"]);

			if(isset($r["p"]) || isset($r["e"]["possiblePoints"])) {
					$result["score"] = array();

					//TO DO: Check if Minimum Points for each question = 0? 
					$result["score"]["min"] = 0;

					if(isset($r["e"]["possiblePoints"]))
					$result["score"]["max"] = $r["e"]["possiblePoints"];
			}

			if(isset($r["p"])) {
				$result["score"]["raw"] = $r["p"];
				
				if(isset($result["score"]["max"]) && isset($result["score"]["min"]) && ($result["score"]["max"] - $result["score"]["min"]) != 0 ) {
					$result["score"]["scaled"] = number_format( $r["p"] / ($result["score"]["max"] - $result["score"]["min"]), 2);
				}
			}

			$this->NSS_CMI_XAPI->set_result_by_object($result);
			$this->NSS_CMI_XAPI->build_statement();
			$this->NSS_CMI_XAPI->new_statement();
			
			$this->log($result);
			$this->log(json_encode($result));
		}

		if(!empty($this->NSS_CMI_XAPI->statements)) {
			$ret = $this->NSS_CMI_XAPI->SendStatements($this->NSS_CMI_XAPI->statements);
			$this->NSS_CMI_XAPI->statements = array();
		}
	}
}