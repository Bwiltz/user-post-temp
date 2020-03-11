<?php


class NSS_CMI_XAPI_V2 extends NSS_XAPI{
	public $interactionType;
	
	function __construct($endpoint = null, $user = null, $pass = null, $version = "0.95") {
		parent::__construct($endpoint, $user, $pass, $version);
	}
	
	public function set_object_type_choice($id, $question, $correctResponsesPattern, $choices, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "choice";
		
		$correctResponsesPattern = is_array($correctResponsesPattern)? $correctResponsesPattern: array($correctResponsesPattern);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		
		$check = true;
		$setids = false;
		foreach($choices as $id=>$choice)
		{
			if($check) {
				if(!is_string($id) && $id == 0) {
					echo $id." is 0";
					$setids = true;
				}
				$check = false;
			}
			
			if($setids && !is_string($id))
			$id = $choice;
			
			$c[] = array('id' => $id,
						'description' => array(
							'en-US' => $choice
						));
		}
		$object['definition']['choices'] = $c;
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_likert($id, $question, $correctResponsesPattern, $choices, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "likert";
		
		if(!is_null($correctResponsesPattern)) {
			$correctResponsesPattern = is_array($correctResponsesPattern)? $correctResponsesPattern: array($correctResponsesPattern);
			$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		}
		$check = true;
		$setids = false;
		foreach($choices as $id=>$choice)
		{
			if($check) {
				if(!is_string($id) && $id == 0) {
					echo $id." is 0";
					$setids = true;
				}
				$check = false;
			}
			
			if($setids && !is_string($id))
			$id = $choice;
			
			$c[] = array('id' => $id,
						'description' => array(
							'en-US' => $choice
						));
		}
		$object['definition']['scale'] = $c;
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_sequencing($id, $question, $correctResponsesPattern, $choices, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "sequencing";
		
		$correctResponsesPattern = is_array($correctResponsesPattern)? $correctResponsesPattern: array($correctResponsesPattern);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		
		$check = true;
		$setids = false;
		foreach($choices as $id=>$choice)
		{
			if($check) {
				if(!is_string($id) && $id == 0) {
					echo $id." is 0";
					$setids = true;
				}
				$check = false;
			}
			
			if($setids && !is_string($id))
			$id = $choice;
			
			$c[] = array('id' => $id,
						'description' => array(
							'en-US' => $choice
						));
		}
		$object['definition']['choices'] = $c;
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_matching($id, $question, $correctResponsesPattern, $source, $target, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "matching";
		
		$correctResponsesPattern = is_array($correctResponsesPattern)? $correctResponsesPattern: array($correctResponsesPattern);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		

		$check = true;
		$setids = false;
		$source_arr = array();
		foreach($source as $id=>$s)
		{
			if($check) {
				if(!is_string($id) && $id == 0) {
					echo $id." is 0";
					$setids = true;
				}
				$check = false;
			}
			
			if($setids && !is_string($id))
			$id = $s;
			
			$source_arr[] = array('id' => $id,
						'description' => array(
							'en-US' => $s
						));
		}
		
		$check = true;
		$setids = false;
		$target_arr = array();
		foreach($target as $id=>$t)
		{
			if($check) {
				if(!is_string($id) && $id == 0) {
					echo $id." is 0";
					$setids = true;
				}
				$check = false;
			}
			
			if($setids && !is_string($id))
			$id = $t;
			
			$target_arr[] = array('id' => $id,
						'description' => array(
							'en-US' => $t
						));
		}
		$object['definition']['source'] = $source_arr;
		$object['definition']['target'] = $target_arr;
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_true_false($id, $question, $correctResponse, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "true-false";
		$correctResponsesPattern = is_array($correctResponse)? $correctResponse: array($correctResponse);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_fill_in($id, $question, $correctResponse, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "fill-in";
		if(!is_null($correctResponse)) {
		$correctResponsesPattern = is_array($correctResponse)? $correctResponse: array($correctResponse);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		}
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_long_fill_in($id, $question, $correctResponse, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "long-fill-in";
		if(!is_null($correctResponse)) {
		$correctResponsesPattern = is_array($correctResponse)? $correctResponse: array($correctResponse);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		}
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_object_type_other($id, $question, $correctResponse, $objectType = 'Activity') {
		$object = $this->build_object($id, $question, $question, "http://adlnet.gov/expapi/activities/cmi.interaction", $objectType);
		$object['definition']['interactionType'] = "other";
		if(!is_null($correctResponse)) {
		$correctResponsesPattern = is_array($correctResponse)? $correctResponse: array($correctResponse);
		$object['definition']['correctResponsesPattern'] = $correctResponsesPattern;
		}
		$this->{'object'} = $object;
		return $this->{'object'};
	}
	public function set_result_by_response($response) {
		$result = $this->result;
		$result['response'] = $response;
		$result['success'] = false;

		if(strpos($this->{'object'}['definition']['correctResponsesPattern'][0], "[,]"))
		{
			if($this->{'object'}['definition']['correctResponsesPattern'][0] == $response)
				$result['success'] = true;
		}
		else
		{
			$correctResponses =  explode("[,]", $this->{'object'}['definition']['correctResponsesPattern'][0]);
			
			$responses = explode("[,]", $response);
			
			$diff = array_diff($correctResponses, $responses);
			$this->debug(array('correctResponses' => $correctResponses, 'responses' => $responses, 'diff' => $diff, 'empty_diff' => empty($diff) ));
			
			if(empty($diff))
				$result['success'] = true;
		}
			
		$this->result = $result;
		return $this->result;
	}
}
/*
$cmi = new NSS_CMI_XAPI();
echo "<pre>";
$cmi->set_verb('answered');
$cmi->set_actor('Pankaj', 'pankaj.visitme@gmailc.om');
$cmi->set_parent('id', 'name', 'description', 'type','oT');
$cmi->set_grouping('id', 'name', 'description', 'type','oT');
$choices =array('three'=>'one','2.2'=>'two','3.3'=>'three','four');
$choices =array('one','two','three','four');
print_r($choices);
$cmi->set_object_type_choice('id', "How many nose does a person have?", 'one', $choices,'oT');
$cmi->set_result_by_object(array('score' => array('raw' => 30, 'scaled'=> 0.30, 'min'=> 0, 'max'=> 100), 'success' => false));
$cmi->set_result_by_response('two');

$cmi->build_statement();
print_r($cmi->json_print(json_encode($cmi->statements)));
*/
