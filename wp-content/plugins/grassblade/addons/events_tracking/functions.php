<?php

class grassblade_events_tracking {

	/**
	 * Stores the list of the all events.
	 *
	 * @var Array
	 */
	protected $events_list;

	function __construct() {
		// Initialise events list
		$this->events_list = $this->get_events_list();

		add_action( 'admin_menu', array($this,'eventstracking_menu'), 1);
		add_filter( 'grassblade_add_scripts_on_page', array($this, 'add_to_scripts') );
		add_action( 'wp', array($this,'check_pageview') );

		add_action( 'wp_login', array($this,'user_login'), 10, 2);
		add_action( 'clear_auth_cookie', array($this,'user_logout'), 10);
		add_action( 'transition_post_status', array($this,'new_post'), 10, 3 );
		add_action( 'post_updated', array($this,'post_updation'), 10, 3 );
		add_action( 'user_register', array($this,'register_new_user'), 10, 1 );
		add_action( 'delete_user', array($this,'unregistered_user'), 10, 1 );
		add_action( 'comment_post', array($this,'new_comment'), 10, 3);

		require_once(dirname(__FILE__)."/../nss_xapi.class.php");
		require_once(dirname(__FILE__)."/pv_xapi.class.php");
	}

	function add_to_scripts($grassblade_add_scripts_on_page) {
		$grassblade_add_scripts_on_page[] = "events-tracking-settings";
		return $grassblade_add_scripts_on_page;
	}

	/**
	 *
	 * Add Events Tracking Setting to the menu.
	 *
	 */

	function eventstracking_menu() {
		add_submenu_page("grassblade-lrs-settings", __("Events Tracking", "grassblade"), __("Events Tracking", "grassblade"),'manage_options','events-tracking-settings', array($this, 'events_tracking_menupage') );
	}

	function events_tracking_menupage(){
		//must check that the user has the required capability 
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.') );
	    }

		// See if the user has posted us some information
	    // If they did, this hidden field will be set to 'Y'
	    if( isset($_POST[ "update_GrassBladeSettings" ]) ) {
	        // Save the posted value in the database
			foreach($this->events_list as $key => $event) {
				if(isset( $_POST[$key]))
				update_option( $key, $_POST[$key]);
				else
				update_option( $key, null);
			}
	        // Put an settings updated message on the screen
	        ?>
			<div class="updated"><p><strong><?php _e('settings saved.', 'grassblade' ); ?></strong></p></div>
			<?php
			$this->events_list = $this->get_events_list();
		}
	    ?>
	    <style>
			.grayblock {
				border: solid 1px #ccc;
				background: #eee;
				padding: 1px 8px;
				width: 30%;
				margin-left: 50px;
				margin-top: 10px;
			}
		</style>
	    <div class="wrap">
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<h2>
					<img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', dirname(dirname(__FILE__))); ?>"/>
					GrassBlade Events Tracking Settings
				</h2>
				<div id="grassblade_settings_form">
					<b><?php echo __("Enable/Disable Tracking:","grassblade"); ?></b>
					<?php
					foreach ($this->events_list as $key => $event) {
						if($event["display"]) { ?>
						<div style="margin-top:12px !important;">
							<input name="<?php echo $key; ?>" type="checkbox" style="margin:0 15px;" <?php if($event['checked']) echo "checked"; ?> /> <?php echo $event['title']; ?>
						</div>
						<?php 
							if($event["sub_event"]) { 
								echo $this->get_sub_event($key);
							}
						}
					} ?>
					<br><br>
					<input type="submit" class="button-primary" name="update_GrassBladeSettings" value="<?php _e('Update Settings', 'grassblade') ?>" />
				</div>
			</form>
			<br><br>
			<?php include(dirname(__FILE__)."/help.php"); ?>
		</div>
	    <?php
	} // end of Events Tracking Function

	/**
	 * Get Events Lists.
	 *
	 *
	 * @return array $events_list Events List.
	 */
	function get_events_list() {
		$events_list = array();

		$setting_keys = array(
						'grassblade_pageviews_all', 
						'grassblade_pageviews_usecatagories',  
						'grassblade_pageviews_usetags',
						'grassblade_new_post', 
						'grassblade_post_updation',
						'grassblade_user_login', 
						'grassblade_user_logout', 
						'grassblade_register_new_user', 
						'grassblade_unregistered_user',
						'grassblade_send_enrolled',
						'grassblade_send_unenrolled',
						'grassblade_new_comment',
					);

		foreach ($setting_keys as $key) {
			$title = $this->get_event_title($key);
			$default = ( strpos($key, "pageviews") == false || $key == "grassblade_pageviews_all" )? true:false; 
			$checked = get_option($key, $default);
			$display = true;
			$sub_event = false;
			if ($key == 'grassblade_pageviews_usecatagories' || $key == 'grassblade_pageviews_usetags') {
				if (get_option('grassblade_pageviews_all', true)) {
					$display = false;
				}
				if ($key == 'grassblade_pageviews_usecatagories' && get_option('grassblade_pageviews_usecatagories')){
					$sub_event = true;
					$events_list['grassblade_pageviews_catagories'] = array('display' => false);
				}

				if ($key == 'grassblade_pageviews_usetags' && get_option('grassblade_pageviews_usetags')){
					$sub_event = true;
					$events_list['grassblade_pageviews_tags'] = array('display' => false);
				}
			}
			$events_list[$key] = array('title' => $title, 'checked' => $checked,'display'=> $display,'sub_event'=> $sub_event);
		}
//echo "<pre style='margin:auto;'>";print_r($events_list);echo "</pre>";

		return $events_list;
	} // end of get events list

	/**
	 * Get Event Title.
	 *
	 * @param string $key Event Key.
	 *
	 * @return string $event_title Event Title.
	 */

	function get_event_title($key){

		switch ($key) {
			case 'grassblade_pageviews_all':
				return __('PageViews of All Pages', 'grassblade');
				break;
			case 'grassblade_pageviews_usecatagories':
				return __('PageViews on Specific Categories', 'grassblade');
				break;
			case 'grassblade_pageviews_catagories':
				return __('PageViews on Specific Categories', 'grassblade');
				break;
			case 'grassblade_pageviews_usetags':
				return __('PageViews on Specific Tags', 'grassblade');
				break;
			case 'grassblade_pageviews_tags':
				return __('PageViews on Specific Tags', 'grassblade');
				break;
			case 'grassblade_user_login':
				return __('User Login', 'grassblade');
				break;
			case 'grassblade_user_logout':
				return __('User Logout', 'grassblade');
				break;
			case 'grassblade_new_post':
				return __('New Post Creation', 'grassblade');
				break;
			case 'grassblade_post_updation':
				return __('Post Updation', 'grassblade');
				break;
			case 'grassblade_register_new_user':
				return __('User Registration', 'grassblade');
				break;
			case 'grassblade_unregistered_user':
				return __('User Deletion', 'grassblade');
				break;
			case 'grassblade_new_comment':
				return __('New Comment', 'grassblade');
				break;
			case 'grassblade_send_enrolled':
				return __('User Enrollment in Course', 'grassblade');
				break;
			case 'grassblade_send_unenrolled':
				return __('User Unenrollment from Course', 'grassblade');
				break;
			default:
				return '';
				break;
		}

	}

	function get_sub_event($key){
		if($key == 'grassblade_pageviews_usecatagories') {
			$grassblade_pageviews_catagories = get_option('grassblade_pageviews_catagories');

			$args = array(
						'type'                     => 'post',
						'child_of'                 => 0,
						'parent'                   => '',
						'orderby'                  => 'name',
						'order'                    => 'ASC',
						'hide_empty'               => 0,
						'hierarchical'             => 1,
						'exclude'                  => '',
						'include'                  => '',
						'number'                   => '',
						'taxonomy'                 => apply_filters( 'grassblade_trackable_taxonomies', array('category') ),
						'pad_counts'               => false
					); 
			$categorylist = get_categories($args );
		?>
		<div class="grayblock">
			<h3><b>Select Categories</b></h3>
			<?php echo $this->hierarchy($categorylist, $grassblade_pageviews_catagories); ?>
		</div>
		<?php 
		}
		if($key == 'grassblade_pageviews_usetags') {
			$posttags = get_terms("post_tag", array('hide_empty' => false)); 
			$grassblade_pageviews_tags =  get_option('grassblade_pageviews_tags');
		?>
		<div class="grayblock">
			<h3><b>Select Tags</b></h3>
			<?php 
				$taginputs = "<ul>";
				foreach($posttags as $tag)
				{
					$checked = !empty($grassblade_pageviews_tags[$tag->term_id])? "CHECKED":"";
					$inputbox = '<li><input name="grassblade_pageviews_tags['.$tag->term_id.']" type="checkbox" style="margin:0 5px" '.$checked.'> '.$tag->name.'</li>';
					$taginputs .= $inputbox;
				}
				$taginputs .= "</li>";
				echo $taginputs;
			?>
		</div>
		<?php
		}
	} // end of get_sub_event

	function hierarchy($categories, $grassblade_pageviews_catagories){
		$catpool = $categories;
		
		$hierarchy = array();
		
		$num = count($catpool);
		for($i = 0; $i < $num; $i++)
		{
			$categories_withcatid[$catpool[$i]->cat_ID] = $catpool[$i];
			
			for($j = 0; $j < $num; $j++)
			{
				$catid = $catpool[$j]->cat_ID;
				$parent = $catpool[$j]->category_parent;
				$hierarchy[$parent][$catid] = 1;
			}
		}

		return  $this->hierarchy_rec(0, $hierarchy, $categories_withcatid, $grassblade_pageviews_catagories);
	}

	function hierarchy_rec($find, $hierarchy,$categories, $grassblade_pageviews_catagories) {
		if(empty($categories[$find]->term_id))
			$inputbox = '';
		else
		{
			$checked = !empty($grassblade_pageviews_catagories[$categories[$find]->term_id])? "CHECKED":"";
			$inputbox = '<input name="grassblade_pageviews_catagories['.$categories[$find]->term_id.']" type="checkbox" style="margin:0 5px" '.$checked.'> '.$categories[$find]->name;
		}
		if(empty($hierarchy[$find]))
			return $inputbox;
		else
		{
			$ret = "";
			foreach($hierarchy[$find] as $k => $v)
			{
				$ret .= "<li>".$this->hierarchy_rec($k, $hierarchy,$categories, $grassblade_pageviews_catagories)."</li>";
			}
			
			if(empty($categories[$find]->term_id))
			return "<ul>".$ret."</ul>";
			else
			return $inputbox."<div style='position:relative; left: 30px;top:5px;'><ul>".$ret."</ul></div>";
		}
	}

	function check_pageview(){
		global $post;

		$grassblade_pageviews_all = get_option('grassblade_pageviews_all');
		$grassblade_pageviews_usecatagories = get_option('grassblade_pageviews_usecatagories');
	    $grassblade_pageviews_catagories = get_option('grassblade_pageviews_catagories');
		$grassblade_pageviews_usetags = get_option('grassblade_pageviews_usetags');
	    $grassblade_pageviews_tags = get_option('grassblade_pageviews_tags');
		
		$grassblade_settings = grassblade_settings();

	    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
	    $grassblade_tincan_user = $grassblade_settings["user"];
	    $grassblade_tincan_password = $grassblade_settings["password"];
		$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];
		$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0.0");
		//$title = trim(get_bloginfo('name')." ".wp_title('', false));
		$title = trim(wp_title('|', false, 'right'));
		$title = apply_filters("gb_pageviews_title", $title, $post);

		if(empty($actor))
		return;
		
		if(!is_singular()) //Exit without sending page view for Category and Tag pages.
		return;
		
		$id = $post->ID;
		
		if(empty($grassblade_pageviews_all)) //If not for all pages check further
		{
			if($grassblade_pageviews_usecatagories) //If categories enabled check further
			{
			//Returns All Term Items for "my_term"
			$taxonomies = apply_filters('grassblade_trackable_taxonomies', 
											array('category')
										);
			
			$categories = wp_get_post_terms($id, $taxonomies, array("fields" => "all"));
				//$categories = get_the_category($id);
				//echo "<pre>";
				//print_r($categories);
				if(!empty($categories->errors)) {
					grassblade_debug($categories);
				}
				else
				if((is_object($categories ) || is_array($categories)))
				foreach($categories as $category)
				{
					$cats[$category->term_id] = 1;
				}
				//print_r($cats);
				//print_r($grassblade_pageviews_catagories);
				if(is_object($grassblade_pageviews_catagories ) || is_array($grassblade_pageviews_catagories))
				foreach($grassblade_pageviews_catagories as $category=>$v)
				{
					if(!empty($cats[$category]))
						$pv = 1;
				}
			}
			
			if($grassblade_pageviews_usetags) //If tags enabled check for tags
			{
				$tags = wp_get_post_tags($id);
				
				if(is_object($tags ) || is_array($tags))
				foreach($tags as $tag)
				{
					$tagsarray[$tag->term_id] = 1;
				}			
				if(is_object($grassblade_pageviews_tags ) || is_array($grassblade_pageviews_tags))
				foreach($grassblade_pageviews_tags as $tag=>$v)
				{
					if(!empty($tagsarray[$tag]))
						$pv = 1;
				}		
			}
			
			if(empty($pv))
			{	return;}
		}
		$grassblade_tincan_version = $grassblade_settings["version"];
		if($grassblade_tincan_version >= "1.0")
			$version = "1.0.0";
		else
			$version = "0.95";

		$xapi = new PV_XAPI($grassblade_tincan_endpoint,$grassblade_tincan_user, $grassblade_tincan_password, $version);
		$xapi->SendPageView($actor, $title);

	}

	/**
	 * Send User Login Statement.
	 *
	 *
	 * @param string $user_login Username.
	 * @param obj $user User Object.
	 *
	 */
	function user_login($user_login, $user){
		
		if(get_option('grassblade_user_login', 1)) {
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

			$blog_url = get_bloginfo('wpurl');
			$blog_name = get_bloginfo('name');
			$blog_desc = get_bloginfo('description');
			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
							);
			//User Login
			$xapi->set_verb('logged-in');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_object($blog_url, $blog_name, $blog_desc, '','Activity');
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 *
	 * Send User Logout Statement.
	 *
	 */
	function user_logout() {

		$current_user = wp_get_current_user();
		
		if( !empty($current_user->ID) && get_option('grassblade_user_logout', 1)) {
			$grassblade_settings = grassblade_settings();

		    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
		    $grassblade_tincan_user = $grassblade_settings["user"];
		    $grassblade_tincan_password = $grassblade_settings["password"];
			$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

			$user = $current_user;

			$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
			$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

			if(empty($actor))
			{
				grassblade_debug("No Actor. Shutting Down.");
				return;
			}

			$blog_url = get_bloginfo('wpurl');
			$blog_name = get_bloginfo('name');
			$blog_desc = get_bloginfo('description');
			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
							);

			$xapi->set_verb('logged-out');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_object($blog_url, $blog_name, $blog_desc, '','Activity');
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 * Send Publish New Post Statement.
	 *
	 *
	 * @param string $new_status Post Current Status.
	 * @param string $old_status.
	 * @param obj $post Post Object.
	 *
	 */
	function new_post($new_status, $old_status, $post) {
		
		if(get_option('grassblade_new_post', 1) && 'publish' === $new_status && 'publish' !== $old_status) {
			
			$grassblade_settings = grassblade_settings();

		    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
		    $grassblade_tincan_user = $grassblade_settings["user"];
		    $grassblade_tincan_password = $grassblade_settings["password"];
			$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

			$user = get_userdata($post->post_author);

			$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
			$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

			if(empty($actor))
			{
				grassblade_debug("No Actor. Shutting Down.");
				return;
			}

			$post_url = get_permalink($post->ID);
			$post_title = $post->post_title;
			$post_desc = substr($post->post_content,0,150).' ...';

			$object_extensions = array(
								"http://www.nextsoftwaresolutions.com/xapi/extensions/details"=> array("ID" => $post->ID, "post_type" => $post->post_type, "post_name" => $post->post_name, "post_parent" => $post->post_parent, "post_author" => $post->post_author, "post_status" => $post->post_status, "old_status" => $old_status ),
							);

			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
							);

			$xapi->set_verb('created');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_object($post_url, $post_title, $post_desc, '','Activity');
			$xapi->set_object_extensions($object_extensions);
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}// end of if
	} 

	/**
	 * Send Post Updation Statement.
	 *
	 *
	 * @param int $post_ID Post ID.
	 * @param obj $post_after Current Post Object.
	 * @param obj $post_before Before Post Object.
	 *
	 */
	function post_updation($post_ID, $post_after, $post_before){

		if(get_option('grassblade_post_updation', 1) && $post_before->post_status === 'publish' && $post_after->post_status != 'trash') {
			$grassblade_settings = grassblade_settings();

		    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
		    $grassblade_tincan_user = $grassblade_settings["user"];
		    $grassblade_tincan_password = $grassblade_settings["password"];
			$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

			$user = wp_get_current_user();

			$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
			$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

			if(empty($actor))
			{
				grassblade_debug("No Actor. Shutting Down.");
				return;
			}

			$post_changes = array();
			foreach ($post_after as $key => $value) {
				if ($post_after->$key != $post_before->$key) {
					if ($key == 'post_content') {
						$post_changes[$key] = 'changed';
					} else if ($key == 'post_modified') {
						continue;
					} else {
						$post_changes[$key] = array('to' => $post_after->$key, 'from' => $post_before->$key );
					}
				} // end of if
			} // end of foreach

			if( empty($post_changes) || count($post_changes) == 1 && isset($post_changes["post_modified_gmt"]) )
				return;

			$post_url = get_post_permalink($post_ID);
			$post_title = $post_after->post_title;
			$post_desc = substr($post_after->post_content,0,150).'...';
			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
								"http://www.nextsoftwaresolutions.com/xapi/extensions/changed"=> $post_changes,
							);

			$xapi->set_verb('updated');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_object($post_url, $post_title, $post_desc, '','Activity');
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 * Send Register New User Statement.
	 *
	 *
	 * @param int $user_id Registered User ID.
	 *
	 */
	function register_new_user($user_id){

		if(get_option('grassblade_register_new_user', 1)) {
			$grassblade_settings = grassblade_settings();

		    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
		    $grassblade_tincan_user = $grassblade_settings["user"];
		    $grassblade_tincan_password = $grassblade_settings["password"];
			$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

			$user = get_userdata($user_id);
			$current_user = wp_get_current_user();

			$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
			$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

			if(empty($actor))
			{
				grassblade_debug("No Actor. Shutting Down.");
				return;
			}

			$blog_url = get_bloginfo('wpurl');
			$blog_name = get_bloginfo('name');
			$blog_desc = get_bloginfo('description');
			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
								"http://nextsoftwaresolutions.com/xapi/extensions/details" =>  array("ID" => $user->ID, "username" => $user->user_login, "roles" => $user->roles, "site" => get_site_url(null, '')),
							);
			if(!empty($current_user)) {
				$name = (!empty($current_user->user_firstname) || !empty($current_user->user_lastname))? $current_user->user_firstname. " ".$current_user->user_lastname:$current_user->user_login;
				$context_extensions["https://w3id.org/xapi/acrossx/extensions/by-whom"] = array('user_id' => $current_user->ID,'name' => $name);
			}
			$xapi->set_verb('joined');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_object($blog_url, $blog_name, $blog_desc, '','Activity');
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 * Send Delete User Statement.
	 *
	 *
	 * @param int $user_id Deleted User ID.
	 *
	 */
	function unregistered_user($user_id) {

		if(get_option('grassblade_unregistered_user', 1)) {
			$grassblade_settings = grassblade_settings();

		    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
		    $grassblade_tincan_user = $grassblade_settings["user"];
		    $grassblade_tincan_password = $grassblade_settings["password"];
			$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

			$user = get_userdata($user_id);
			$current_user = wp_get_current_user();

			$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
			$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

			if(empty($actor))
			{
				grassblade_debug("No Actor. Shutting Down.");
				return;
			}

			$blog_url = get_bloginfo('wpurl');
			$blog_name = get_bloginfo('name');
			$blog_desc = get_bloginfo('description');
			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
							);
			if(!empty($current_user)) {
				$name = (!empty($current_user->user_firstname) || !empty($current_user->user_lastname))? $current_user->user_firstname. " ".$current_user->user_lastname:$current_user->user_login;
				$context_extensions["https://w3id.org/xapi/acrossx/extensions/by-whom"] = array('user_id' => $current_user->ID,'name' => $name);
			}
			$xapi->set_verb('left');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_object($blog_url, $blog_name, $blog_desc, '','Activity');
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 * Send New Comment Statement.
	 *
	 *
	 * @param int $comment_ID.
	 * @param int|string $comment_approved.
	 * @param array $commentdata.
	 *
	 */
	function new_comment($comment_ID, $comment_approved, $commentdata) {

		if(get_option('grassblade_new_comment', 1)) {
			$grassblade_settings = grassblade_settings();

		    $grassblade_tincan_endpoint = $grassblade_settings["endpoint"];
		    $grassblade_tincan_user = $grassblade_settings["user"];
		    $grassblade_tincan_password = $grassblade_settings["password"];
			$grassblade_tincan_track_guest = $grassblade_settings["track_guest"];

			$user = get_userdata($commentdata['user_id']);

			$xapi = new NSS_XAPI($grassblade_tincan_endpoint, $grassblade_tincan_user, $grassblade_tincan_password);
			$actor = grassblade_getactor($grassblade_tincan_track_guest, "1.0", $user);

			if(empty($actor))
			{
				grassblade_debug("No Actor. Shutting Down.");
				return;
			}

			$commented_post = get_post($commentdata['comment_post_ID']);
			$post_url = get_post_permalink($commentdata['comment_post_ID']);
			$post_title = $commented_post->post_title;
			$post_desc = substr($commented_post->post_content,0,150);
			$context_extensions = array(
								"http://nextsoftwaresolutions.com/xapi/extensions/user-info" =>  array(
									"user-agent" =>  $_SERVER['HTTP_USER_AGENT'],
									"user-ip" =>   $_SERVER['REMOTE_ADDR'],
									"user-port" => $_SERVER['REMOTE_PORT'],
								),
								"http://nextsoftwaresolutions.com/xapi/extensions/referer" =>  (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']:""),
							);
			$result = array('response' => $commentdata['comment_content']);

			$xapi->set_verb('commented');
			$xapi->set_actor_by_object($actor);
			$xapi->set_context_extensions($context_extensions);
			$xapi->set_result_by_object($result);	
			$xapi->set_object($post_url, $post_title, $post_desc, '','Activity');
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 * Send Course enrollment Statement.
	 *
	 *
	 * @param int $user_id.
	 * @param int $course_id.
	 *
	 */
	static function send_enrolled($user_id, $course_id){

		if(get_option('grassblade_send_enrolled', 1)) {

			if (empty($user_id) || empty($course_id)) {
				return;
			}

			$user = get_userdata($user_id);
			$course = get_post($course_id);

			if (empty($user->ID) || empty($course->ID)) {
				return;
			}

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
			$context_extensions = array();
			$current_user = wp_get_current_user();

			if(!empty($current_user)) {
				$name = (!empty($current_user->user_firstname) || !empty($current_user->user_lastname))? $current_user->user_firstname. " ".$current_user->user_lastname:$current_user->user_login;
				$context_extensions["https://w3id.org/xapi/acrossx/extensions/by-whom"] = array('user_id' => $current_user->ID,'name' => $name);
			}
			
			$course_url = grassblade_post_activityid($course_id);
			$course_title = $course->post_title;

			$xapi->set_verb('enrolled');
			$xapi->set_actor_by_object($actor);
			$xapi->set_object($course_url, $course_title, '', '','Activity');

			if(!empty($context_extensions))
			$xapi->set_context_extensions($context_extensions);
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

	/**
	 * Send Course unenrollment Statement.
	 *
	 *
	 * @param int $user_id.
	 * @param int $course_id.
	 *
	 */
	static function send_unenrolled($user_id,$course_id) {

		if(get_option('grassblade_send_unenrolled', 1)) {
			if (empty($user_id) || empty($course_id)) {
				return;
			}
			
			$user = get_userdata($user_id);
			$course = get_post($course_id);

			if (empty($user->ID) || empty($course->ID)) {
				return;
			}

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
			$context_extensions = array();
			$current_user = wp_get_current_user();

			if(!empty($current_user)) {
				$name = (!empty($current_user->user_firstname) || !empty($current_user->user_lastname))? $current_user->user_firstname. " ".$current_user->user_lastname:$current_user->user_login;
				$context_extensions["https://w3id.org/xapi/acrossx/extensions/by-whom"] = array('user_id' => $current_user->ID,'name' => $name);
			}

			$course_url = grassblade_post_activityid($course_id);
			$course_title = $course->post_title;

			$xapi->set_verb('unenrolled');
			$xapi->set_actor_by_object($actor);
			$xapi->set_object($course_url, $course_title, '', '','Activity');

			if(!empty($context_extensions))
			$xapi->set_context_extensions($context_extensions);
			$statement = $xapi->build_statement();
			
			$ret = $xapi->SendStatements(array($statement));
		}
	} 

} // end of class

$gbet = new grassblade_events_tracking();

