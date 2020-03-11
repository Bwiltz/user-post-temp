<?php
	/*
	*
	* GrassBlade Block Creation Codes - Right Now There are there blocks
	* 1. To add xAPI content - Function - xapi_content_block & JS - xapi-block.js
	* 2. To add LeaderBoard - Function - leaderboard_block & JS - gb-leaderboard.js
	* 3. To add User Score - Function - userscore_block & JS - gb-user-score.js
	*
	*/

class grassblade_guttenberg {

	function __construct() {
		// Register Blocks.
		add_action('init', array($this, 'register_editor_blocks') );
		// Register Assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_editor_assets' ) );
		add_filter( 'block_categories', array($this,'block_category'), 10, 2);

		add_action('save_post', array($this, 'update_xapi_content_blocks_meta' ) );
	}

	/**
	 * Register Editor Blocks.
	 *
	 */
	function register_editor_blocks() {
		// Blocks.
		$this->xapi_content_block();
		$this->leaderboard_block();
		$this->userscore_block();
		$this->user_report_block();
	}

	/**
	 * Register Editor Assets.
	 *
	 */
	function register_editor_assets() {
		$gb_block_data = $this->get_block_data();

		wp_enqueue_script(
		    'grassblade_blocks',
		    plugin_dir_url(__FILE__) . 'blocks.js',
		    array('jquery', 'wp-blocks','wp-editor','wp-element'),
		    GRASSBLADE_VERSION
		);

		wp_localize_script('grassblade_blocks','gb_block_data', $gb_block_data);
	}

	function get_block_data(){

		$post_content = array();
		$xapi_contents = get_posts("post_type=gb_xapi_content&orderby=post_title&posts_per_page=-1");

		foreach ($xapi_contents as $xapi_content) { 

			$completion_tracking = grassblade_xapi_content::is_completion_tracking_enabled($xapi_content->ID);
			$temp = array(
							'id' => $xapi_content->ID,
						  	'post_title' => $xapi_content->post_title,
						  	'completion_tracking' => $completion_tracking,
						 );

			array_push($post_content,$temp);

		} // end of for each 

		global $wp_roles;

	    $all_roles = $wp_roles->roles;
	    $roles = apply_filters('grassblade_block_roles', $all_roles);

		$arrayOfValues = array(
		    'admin_url'     	=> admin_url(),
		    'post_content' 		=> $post_content,
		    'roles'  			=> $roles,
		    'xapi_content_title'=> __("xAPI Content", "grassblade"),
		    'xapi_content_desc'	=> __("You can add your xAPI Content using the dropdown below.", "grassblade"),
			'Add_to_Page' 		=> __("Add to Page", "grassblade"),
			'Add_New' 			=> __("Add New", "grassblade"),
			'Select_Content' 	=> __("Select Content", "grassblade"),
			'Edit' 				=> __("Edit", "grassblade"),
			'tracking_disable' 	=> __("Completion Tracking Disabled", "grassblade"),
			'tracking_enable' 	=> __("Completion Tracking Enabled", "grassblade"),
			'leaderboard_title'	=> __("LeaderBoard", "grassblade"),
		    'leaderboard_desc'	=> __("You can Add Content Leader-Board for xAPI content.", "grassblade"),
		    'Select_Role'		=> __("Select Role", "grassblade"),
		    'All_Role'			=> __("All Roles", "grassblade"),
		    'Score'				=> __("Score", "grassblade"),
		    'Percentage'		=> __("Percentage", "grassblade"),
		    'Content'			=> __("Content", "grassblade"),
		    'Role'				=> __("Role", "grassblade"),
		    'Note'				=> __("Note", "grassblade"),
		    'Role_Desc'			=> __("Who can see the leaderboard?", "grassblade"),
		    'Score_Type'		=> __("Score Type", "grassblade"),
		    'Limit'				=> __("Limit", "grassblade"),
		    'userscore_title' 	=> __("User Score", "grassblade"),
		    'userscore_desc'	=> __("This will show user's Score based on your selection below.", "grassblade"),
		    'User_Score'		=> __("User Score", "grassblade"),
		    'All_Content'		=> __("All Content", "grassblade"),
		    'No_Selection'		=> __("No Selection", "grassblade"),
		    'Total_Score'		=> __("Total Score", "grassblade"),
		    'Average_Percentage'=> __("Average Percentage", "grassblade"),
		    'Badgeos_Points'	=> __("Badgeos Points", "grassblade"),
		    'Label'				=> __("Label", "grassblade"),
		    'xAPI_Content'		=> __("xAPI Content", "grassblade"),
		    'Add'				=> __("Add", "grassblade"),
		    'add_shortcode_desc'=> __("Or, use this Shortcode", "grassblade"),
		    'user_report_title'=> __("User Report", "grassblade"),
		    'user_report_desc'	=> __("You can add User Report for xAPI courses.", "grassblade"),
		    'BG_color'			=> __("Background Color", "grassblade"),
		);

		return $arrayOfValues;
	} // end of get_block_data

	function block_category( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'grassblade-blocks',
					'title' => __( 'GrassBlade xAPI Companion Blocks', 'grassblade' ),
				),
			)
		);
	} // end of block_category function


	function xapi_content_block() {
	  	register_block_type( 'grassblade/xapi-content', array(
	        'editor_script' => 'xapi_content_block',
	        'render_callback' => array($this,'xapi_block_render_callback'),
	        'attributes' => array(
	                'content_id' => array(
	                    'type' => 'string',
	                ),
	                'check_completion' => array(
	                    'type' => 'string',
	                ),
	                'className'         => array(
						'type' => 'string',
					)
	            ),
	    ) );
	} // end of xapi_content_block

	function xapi_block_render_callback( $attributes = array(), $content ) {
	    if(empty($attributes["content_id"]))
	    {
	    	return "<div style='color:red'>".__("Please select the xAPI Content from Block settings on the right.", "grassblade")."</div>";
	    }

	    $className = isset($attributes["className"]) ? $attributes["className"] : '';

    	$current_post = get_post();
    	$display_status = apply_filters("gb_xapi_block_access", true, $current_post, $attributes, $content);
    	if( $display_status ) {
    		return grassblade(array("id" => $attributes["content_id"], "class" => $className));
    	} else {
    		return null;
    	}
	} // end of xapi_block_render_callback

	function update_xapi_content_blocks_meta($post_id) { 		
		$content_post = get_post($post_id);
		delete_post_meta($post_id,  "show_xapi_content_blocks");
		
		if ( has_blocks( $content_post->post_content ) ) {
		    $blocks = parse_blocks( $content_post->post_content );

		    $this->save_blocks($blocks,$post_id);
		} // end of if 		
	} // end of update_post_meta_xapicontent

	function save_blocks($blocks,$post_id)
	{
	    $new_block = array();
	    foreach($blocks as $key => $value)
	    {
	        if (!empty($value["innerBlocks"]))
	        {
	           unset($blocks[$key]);
	           $new_block[$key] = $this->save_blocks($value["innerBlocks"],$post_id);
	        }
	        else
	        {
	            if ($value["blockName"] == 'grassblade/xapi-content') {
					add_post_meta($post_id, 'show_xapi_content_blocks', $value["attrs"]["content_id"]);
		    	} // end of if 
	            unset($blocks[$key]);
	        }
	    }

	    return $new_block;
	}

	function leaderboard_block() {

	  	register_block_type( 'grassblade/leaderboard', array(
	        'editor_script' => 'leaderboard_block',
	        'render_callback' => array($this,'leaderboard_block_render_callback'),
	        'attributes' => array(
	                'content_id' => array(
	                    'type' => 'string',
	                ),
	                'role' => array(
	                    'type' => 'string',
	                ),
	                'score' => array(
	                    'type' => 'string',
	                ),
	                'limit' => array(
	                    'type' => 'string',
	                ),
	                'className'         => array(
						'type' => 'string',
					)
	            ),
	    ) );

	} // end of leaderboard_block

	function leaderboard_block_render_callback( $attributes = array(), $content ) {
	    if(empty($attributes["content_id"]))
	    {
	    	return "<div style='color:red'>".__("Please select the xAPI Content from Block settings to create Leaderboard.", "grassblade")."</div>";
	    }
	    else
	    {	
	    	$className = isset($attributes["className"]) ? $attributes["className"] : '';
	    	$role = isset($attributes["role"]) ? $attributes["role"] : 'all';
			$score = isset($attributes["score"]) ? $attributes["score"] : 'score';
			$limit = isset($attributes["limit"]) ? $attributes["limit"] : 20;
	    	$table = gb_leaderboard(array("id" => $attributes["content_id"], "allow" => $role, "score" => $score, "limit" => $limit, "class" => $className));

	    	if( empty($table) && isset($_REQUEST['context']) && $_REQUEST['context'] == 'edit' )
	    		$table = grassblade_leaderboard_table(array(array("user_id" => get_current_user_id(), "total" => 100, "total_timespent" => 100, "status" => __("Passed", "grassblade"),"timestamp" => date("Y-m-d H:i:s") )));
	    	
	    	return $table;
	    }
	} // end of leaderboard_block_render_callback

	function userscore_block() {
	  	register_block_type( 'grassblade/userscore', array(
	        'editor_script' => 'userscore_block',
	        'render_callback' => array($this,'userscore_block_render_callback'),
	        'attributes' => array(
	                'content_id' => array(
	                    'type' => 'string',
	                ),
	                'show' => array(
	                    'type' => 'string',
	                ),
	                'add' => array(
	                    'type' => 'string',
	                ),
	                'label' => array(
	                    'type' => 'string',
	                ),
	                'className'         => array(
						'type' => 'string',
					)
	            ),
	    ) );
	} // end of userscore_block

	function userscore_block_render_callback( $attributes = array(), $content ) {

		if (isset($attributes["content_id"])) {
			if ($attributes["content_id"] == '') {
				$content_id =  null;
			} else {
				$content_id =  $attributes["content_id"];
			}
			
		} else {
			$content_id =  null;
		}

		$className = isset($attributes["className"]) ? $attributes["className"] : '';
    	$show = isset($attributes["show"]) ? $attributes["show"] : 'total_score';
    	
		$add = isset($attributes["add"]) ? $attributes["add"] : null;

		$label = isset($attributes["label"]) ? $attributes["label"] : 'User Score';

    	$userscore = grassblade_xapi_content::user_score( array("show" => $show, "add" => $add, "content_id" => $content_id));
		$label = strip_tags($label, "<b><p><div><span>");

    	return '<div class="'.$className.' grassblade_userscore">'.$label.' '.$userscore.'</div>';

	} // end of userscore_block_render_callback

	function user_report_block() {
		register_block_type( 'grassblade/user-report', array(
	        'editor_script' => 'user_report_block',
	        'render_callback' => array($this,'user_report_block_render_callback'),
	        'attributes' => array(
	                'bg_color' => array(
	                    'type' => 'string',
	                ),
	                'className'         => array(
						'type' => 'string',
					)
	            ),
	    ) );

	} // end of user_report_block

	function user_report_block_render_callback( $attributes = array(), $content ) {
		$className = isset($attributes["className"]) ? $attributes["className"] : '';
		$bg_color = isset($attributes["bg_color"]) ? $attributes["bg_color"] : '';

		$grassblade_user_report = new grassblade_user_report();
		$profile = $grassblade_user_report->user_report(array("bg_color" => $bg_color, "class" => $className));

		return $profile;
	} // end of user_report_block_render_callback
}

if(function_exists( 'register_block_type' ))
new grassblade_guttenberg();