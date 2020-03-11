<?php
class PV_XAPI extends NSS_XAPI {
	
	function __construct($endpoint = null, $user = null, $pass = null, $version = "0.95") {
		parent::__construct($endpoint, $user, $pass, $version);
	}
	
	function SendPageView($actor = null, $title = null)
	{
			if(empty($actor))
			return;
			
			$url = $this->current_page_url();

		    $remote_addr = isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');

			$statements = array(array(
					"actor" => $actor,
					"verb" =>  array(
						"id" =>  "http://adlnet.gov/expapi/verbs/experienced",
						"display" =>  array(
							"en-US" =>  "experienced"
						)
					),
					"context" =>  array(
						"extensions" =>  array(
							"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
								"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
								"user-ip" =>   $remote_addr,
								"user-port" => $_SERVER['REMOTE_PORT'],
							),
						)
					),
					"object" =>  array(
						"id" =>  $url,
						"definition" =>  array(
							"name" =>  array(
								"en-US" =>  $title
							)
						),
						"objectType" =>  "Activity"
					)
				));
			
			if(isset($_SERVER['HTTP_REFERER']))
			$statements[0]["context"]["extensions"]["http://nextsoftwaresolutions.com/xapi/extensions/referer"] = $_SERVER['HTTP_REFERER'];

			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && isset($_SERVER['REMOTE_ADDR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR'])
			$statements[0]["context"]["extensions"]["http://nextsoftwaresolutions.com/xapi/extensions/user-info"]["user-x-forwarded"] = $_SERVER['REMOTE_ADDR'];
	
			return $this->SendStatements($statements);
	}
	function current_page_url() {
		$pageURL = 'http';
		if( isset($_SERVER["HTTPS"]) ) {
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return trim($pageURL);
	}
}
