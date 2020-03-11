<?php 

class grassblade_xapi {

	function __construct() {

		add_filter( 'grassblade_content_versions', array($this,'add_xapi_versions'),10, 1);

		add_filter( 'grassblade_process_upload', array($this,'process_xapi_upload'),20, 3);
	}

	/**
	 * Add xAPI Versions to the content version List.
	 *
	 * @param array $versions List of existing versions.
	 *
	 * @return array $versions with xapi versions.
	 */
	function add_xapi_versions($versions) {

		$xapi_versions = array(
					'1.0' => '1.0',
					'0.95' => '0.95',
					'0.9' => '0.9'
				);

		return array_merge($versions,$xapi_versions);
	}

	/**
	 * Add xAPI Versions to the content version List.
	 *
	 * @param array $params.
	 * @param obj $post.
	 * @param array $upload with required index $upload['content_path'] and $upload['content_url'].
	 *
	 * @return array $params with xapi versions.
	 */
	function process_xapi_upload($params, $post , $upload) {

		if (empty($params['process_status']) && isset($upload['content_path']) && is_dir($upload["content_path"])) {

			$tincanxml_subdir = $this->get_tincanxml($upload['content_path']);
						
			if(empty($tincanxml_subdir))
			$tincanxml_file = $upload['content_path'].DIRECTORY_SEPARATOR."tincan.xml";
			else
			$tincanxml_file = $upload['content_path'].DIRECTORY_SEPARATOR.$tincanxml_subdir.DIRECTORY_SEPARATOR."tincan.xml";

			$nonxapi_file = $upload['content_path'].DIRECTORY_SEPARATOR."player.html"; // Check if No tincan.xml Articulate Studio File
			$nonxapi_file2 = $upload['content_path'].DIRECTORY_SEPARATOR."story.html"; // Check if No tincan.xml Articulate Storyline File
			$nonxapi_file3 = $upload['content_path'].DIRECTORY_SEPARATOR."index.html"; // Check if No tincan.xml Captivate File
			$nonxapi_file4 = $upload['content_path'].DIRECTORY_SEPARATOR."presentation.html"; // Check if No tincan.xml Articulate Studio 13 File
			$nonxapi_file5 = $upload['content_path'].DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR."index.html"; // Check if No tincan.xml Articulate Rise File
			
			if(file_exists($tincanxml_file))
			{
				$tincanxmlstring = trim(file_get_contents($tincanxml_file));
				$tincanxml = simplexml_load_string($tincanxmlstring);
				if(!empty($tincanxml->activities->activity->launch))
				{
					$launch_file = (string)  $tincanxml->activities->activity->launch;
					if(empty($post->post_title)) {
						$content_name = (string)  $tincanxml->activities->activity->name;
						if(!empty($content_name))
						{
							$post->post_title = $content_name;
							global $wpdb;
							$wpdb->update($wpdb->posts, array("post_title" => $content_name), array("ID" => $post_id));
						}
					}
					$upload['original_activity_id'] = isset($tincanxml->activities->activity['id'])? $tincanxml->activities->activity['id']:"";
					if(empty($upload['activity_id']))
					$upload['activity_id'] = $upload['original_activity_id'];
				}
				else
					return array("response" => 'error', "info" => "XML Error:  Launch file reference not found in tincan.xml");
				
				$upload['launch_path'] = dirname($tincanxml_file).DIRECTORY_SEPARATOR.$launch_file;
				
				if(empty($tincanxml_subdir))
				$upload['src'] =  $upload['content_url']."/".$launch_file;
				else
				$upload['src'] =  $upload['content_url']."/".$tincanxml_subdir."/".$launch_file;
				
				if(!file_exists($upload['launch_path']))
					return array("response" => 'error', "info" => 'Error: <i>'.$upload['launch_path'].'</i>. Launch file not found in tincan package');
				
				if(isset($upload['version']) && $upload['version'] == "none")
				$upload['version'] = "";

				$upload["content_type"] = "xapi";
				$upload['process_status'] = 1;
			}
			else if(file_exists($nonxapi_file)) //Articulate Studio  Non-TinCan Support
			{
				$upload['src'] =  $upload['content_url']."/player.html";
				$upload['launch_path'] =  dirname($nonxapi_file).DIRECTORY_SEPARATOR."player.html";
				//$upload['notxapi'] = true;
				$upload['version'] = "none";
				$upload['process_status'] = 1;
				$upload["content_type"] = "not_xapi";
			}
			else if(file_exists($nonxapi_file2)) //Articulate Storyline Non-TinCan Support
			{
				$upload['src'] =  $upload['content_url']."/story.html";
				$upload['launch_path'] =  dirname($nonxapi_file2).DIRECTORY_SEPARATOR."story.html";
				//$upload['notxapi'] = true;
				$upload['version'] = "none";
				$upload['process_status'] = 1;
				$upload["content_type"] = "not_xapi";
			}
			else if(file_exists($nonxapi_file3)) //Captivate Non-TinCan Support
			{
				$upload['src'] =  $upload['content_url']."/index.html";
				$upload['launch_path'] =  dirname($nonxapi_file3).DIRECTORY_SEPARATOR."index.html";
				//$upload['notxapi'] = true;
				$upload['version'] = "none";
				$upload['process_status'] = 1;
				$upload["content_type"] = "not_xapi";
			}
	        else if(file_exists($nonxapi_file4)) //Articulate Studio 13
	        {
                $upload['src'] =  $upload['content_url']."/presentation.html";
                $upload['launch_path'] =  dirname($nonxapi_file4).DIRECTORY_SEPARATOR."presentation.html";
                //$upload['notxapi'] = true;
                $upload['version'] = "none";
                $upload['process_status'] = 1;
				$upload["content_type"] = "not_xapi";
	        }
	        else if(file_exists($nonxapi_file5)) //Articulate Rise
			{
				$upload['src'] =  $upload['content_url']."/content/index.html";
				$upload['launch_path'] =  dirname($nonxapi_file5).DIRECTORY_SEPARATOR."index.html";
				//$upload['notxapi'] = true;
				$upload['version'] = "none";
				$upload['process_status'] = 1;
				$upload["content_type"] = "not_xapi";
			}

			foreach($upload as $k=>$v)
				$params[$k] = addslashes($v);
		}

		if(empty($params['process_status'])) {
			if(isset($params["src"]))
				unset($params["src"]);
			if(isset($params["launch_path"]))
				unset($params["launch_path"]);
		}
		return $params;
	}

	function get_tincanxml($dir) {
		$tincanxml_file = $dir.DIRECTORY_SEPARATOR."tincan.xml";
		
		if(file_exists($tincanxml_file))
			return "";
		else
		{
			$dirlist = scandir($dir);
			foreach($dirlist as $d)
			{
				if($d != "." && $d != "..")
				{
					$tincanxml_file = $dir.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR."tincan.xml";
					if(file_exists($tincanxml_file))
						return $d;
				}
			}
		}
		return 0;
	}

} // end of grassblade_xapi class

$gb_xapi = new grassblade_xapi();