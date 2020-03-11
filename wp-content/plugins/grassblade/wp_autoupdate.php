<?php

class nss_plugin_updater
{
    /**
     * The plugin current version
     * @var string
     */
    public $current_version;

    /**
     * The plugin remote update path
     * @var string
     */
    public $update_path;

    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     * @var string
     */
    public $plugin_slug;

    /**
     * Plugin name (plugin_file)
     * @var string
     */
    public $slug;

	public $code;
    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct($update_path, $plugin_slug)
    {

		// Set the class public variables
        //$this->update_path = $update_path;
		$this->plugin_slug = $plugin_slug;
        $this->current_version = $this->get_plugin_data()->Version;
        
		list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);
		$code = $this->code = $this->slug;
		
		$license = get_option('nss_plugin_license_'.$code);
		$licenseemail = get_option('nss_plugin_license_email_'.$code);
		$this->update_path = $update_path.'?pluginupdate='.$code.'&licensekey='.urlencode($license).'&licenseemail='.urlencode($licenseemail).'&nsspu_wpurl='.urlencode(get_bloginfo('wpurl')).'&nsspu_admin='.urlencode(get_bloginfo('admin_email')).'&current_version='.$this->current_version;
		
        $this->time_to_recheck();
        
		//Add Menu
		add_action('admin_menu', array(&$this, 'nss_plugin_license_menu'), 10);
			
        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'check_info'), 20, 3);

        add_action("in_admin_header", array(&$this, 'check_notice'));    

        add_action("in_plugin_update_message-grassblade/grassblade.php", array($this, "in_plugin_update_message"), 10, 2);
    }
    function in_plugin_update_message($plugin_data, $response) {
        if(!empty($response->upgrade_notice)) {
            ?>
            <span style="display: block;">
                <?php echo $response->upgrade_notice; ?>
            </span><?php
        }
    }
    function check_notice() {
        //add_action( 'admin_notices', array(&$this, 'admin_notice'));           
        if(!empty($_REQUEST["page"]) && $_REQUEST["page"] == "nss_plugin_license-".$this->code."-settings") {
            $this->check_update();
        }
        $license = get_option("nss_plugin_remote_license_".$this->slug);
        if(isset($license["value"]) && empty($license["value"]))
        {
         add_action( 'admin_notices', array(&$this, 'admin_notice'));           
        }
        $license_info = get_option("nss_plugin_info_".$this->slug);
        if(!empty($license_info->expiry) && strtotime($license_info->expiry) < time() + 8 * 86400 && strtotime($license_info->expiry) > time() ) 
        {
            add_action( 'admin_notices', array(&$this, 'admin_notice_expiring'));
        }
        if(!empty($license_info->expiry) && strtotime($license_info->expiry) < time() - 1 * 86400 ) 
        {
            add_action( 'admin_notices', array(&$this, 'admin_notice'));
        }
    }
    function time_to_recheck() {
        $nss_plugin_check = get_option("nss_plugin_check_".$this->slug);
        if(empty($nss_plugin_check) || ( !empty($_REQUEST["pluginupdate"]) && $_REQUEST["pluginupdate"] == $this->code ) || !empty($_GET["force-check"]) || $nss_plugin_check <= time() - 12 * 60 * 60  || !empty($_REQUEST["page"]) && $_REQUEST["page"] == "nss_plugin_license-".$this->code."-settings") {
            $this->reset();
            return true;
        }
        else
            return false;
    }
    function reset() {
        delete_option("nss_plugin_remote_version_".$this->slug);
        delete_option("nss_plugin_remote_license_".$this->slug);
        delete_option("nss_plugin_info_".$this->slug);
        update_option("nss_plugin_check_".$this->slug, time());
    }
	function admin_notice() {
        $current_screen = get_current_screen();
        if ( ( 'grassblade_page_nss_plugin_license-'.$this->code.'-settings' != $current_screen->id ) && ( 'dashboard' != $current_screen->id ) ) {
    		$licensepage = get_admin_url(null,'admin.php?page=nss_plugin_license-'.$this->code.'-settings');
            ?>
            <div class="notice notice-error below-h2 is-dismissible">
                <p><?php echo 'The license for <strong>'. $this->get_plugin_data()->Name .'</strong> is invalid or incomplete. <a href="'. $licensepage .'">Click Here</a> to update your license details, or <a href="http://www.nextsoftwaresolutions.com/grassblade-xapi-companion/" target="_blank">buy</a> one now.'; ?></p>
            </div>
            <?php
        }
    }
    function admin_notice_expiring() {
        $current_screen = get_current_screen();
        if ( ( 'grassblade_page_nss_plugin_license-'.$this->code.'-settings' != $current_screen->id ) && ( 'dashboard' != $current_screen->id ) ) {
        
            $license_info = get_option("nss_plugin_info_".$this->slug);
            $expiry = $license_info->expiry;
            $days_left = intVal((strtotime($expiry) - strtotime(date("Y-m-d")))/86400);
            $days_str = empty($days_left)? "today":sprintf("in %d day", $days_left);
            $days_str .= ($days_left > 1)? "s":"";
            $license = get_option('nss_plugin_license_'.$this->code);
            $renewal_url = "https://www.nextsoftwaresolutions.com/product/grassblade-xapi-companion-renewal/?attribute_renewal=".$license;
            ?>
            <div class="notice notice-error below-h2 is-dismissible">
                <p><?php echo "Your <b>".$this->get_plugin_data()->Name."</b> license will expire $days_str. <a href='".$renewal_url."'>Renew Now!</a>"; ?></p>
            </div>
            <?php
        }
    }	
	function invalid_current_license() {
		add_action( 'admin_notices', array(&$this, 'admin_notice'));
		deactivate_plugins( $this->plugin_slug );
	}
	function get_plugin_data() {
		if(!function_exists('get_plugin_data'))
		include_once( ABSPATH.'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'plugin.php');

		return (object) get_plugin_data(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$this->plugin_slug);
	}
    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     */
    public function check_update($transient = null)
    {
        if (empty($transient->checked)) {
           // return $transient;
        }
	//print_r($transient);
        if(!$this->time_to_recheck())
        {
            $remote_version = get_option("nss_plugin_remote_version_".$this->slug);
            $license = get_option("nss_plugin_remote_license_".$this->slug);
        }

        // Get the remote version
        if(empty($remote_version)) {
            $info = $this->getRemote_information();
            $remote_version = $info->new_version;
            update_option( 'nss_plugin_remote_version_'.$this->slug, $remote_version );
            update_option( 'nss_plugin_info_'.$this->slug, $info);
        }
        else
            $info = get_option("nss_plugin_info_".$this->slug);

        if(empty($license)) {
            $value = $this->getRemote_license();
            $license = array("value" => $value);
            update_option("nss_plugin_remote_license_".$this->slug, $license);
        }
	
        if(empty($license))
		$this->getRemote_current_license();
        
        if(empty($license["value"]))
        add_action( 'admin_notices', array(&$this, 'admin_notice'));
            
        // If a newer version is available, add the update
        if (version_compare($this->current_version, $remote_version, '<')) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->update_path;
            $obj->package = $this->update_path;
            $obj->tested    = $info->tested;
            if(!empty($info->gb_update_message))
            $obj->upgrade_notice = strip_tags($info->gb_update_message,'<br><b><strong><div><span><a>');
            $obj->icons = array(
                                    "default" => plugins_url('img/icon_64x64.png', __FILE__),
                                    "1x" => plugins_url('img/icon_64x64.png', __FILE__),
                                    "2x" => plugins_url('img/icon_128x128.png', __FILE__)
                                );
            $transient->response[$this->plugin_slug] = $obj;
        }
        ///var_dump($transient);
        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function check_info($false, $action, $arg)
    {
		if(empty($arg) || empty($arg->slug) || empty($this->slug))
		return $false;
		
        if ($arg->slug === $this->slug) {
        
            if(!$this->time_to_recheck())
            {
                $info = get_option("nss_plugin_info_".$this->slug);
                if(!empty($info))
                   return $info; 
            }
            $information = $this->getRemote_information();
            update_option("nss_plugin_info_".$this->slug, $information);
            return $information;
        }
        return $false;
    }

    /**
     * Return the remote version
     * @return string $remote_version
     */
    public function getRemote_version()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'version')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }

    /**
     * Get information about the remote version
     * @return bool|object
     */
    public function getRemote_information()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'info')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return maybe_unserialize($request['body']);
        }
        return false;
    }

    /**
     * Return the status of the plugin licensing
     * @return boolean $remote_license
     */
    public function getRemote_license()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'license')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            if($request['body'] == "false" || $request['body'] == "not_found" || empty($request['body']))
                add_action( 'admin_notices', array(&$this, 'admin_notice'));
            return $request['body'];
         }
        //add_action( 'admin_notices', array(&$this, 'admin_notice'));
        return true;
    }
    
    public function getRemote_current_license()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'current_license')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            if($request['body'] == "not_found")
                $this->invalid_current_license();
            return $request['body'];
         }
        //$this->invalid_current_license();
        return true;
    }   
	
	function nss_plugin_license_menu() {
		add_submenu_page("grassblade-lrs-settings", __("License","grassblade"), "License",'manage_options','nss_plugin_license-'.$this->code.'-settings', array(&$this, 'nss_plugin_license_menupage'));
	}

	function nss_plugin_license_menupage()
	{
		$code = $this->code;
	   //must check that the user has the required capability 
		if (!current_user_can('manage_options'))
		{
		  wp_die( __('You do not have sufficient permissions to access this page.','grassblade') );
		}

		// Read in existing option value from database
		$license = get_option('nss_plugin_license_'.$code);
		$email = get_option('nss_plugin_license_email_'.$code);

		// See if the user has posted us some information
		// If they did, this hidden field will be set to 'Y'
		if( isset($_POST[ "update_nss_plugin_license_".$code ]) ) {
			// Read their posted value
			$license = $_POST['nss_plugin_license_'.$code];
			$email = $_POST['nss_plugin_license_email_'.$code];
		
			// Save the posted value in the database
			update_option( 'nss_plugin_license_'.$code, $license);
			update_option( 'nss_plugin_license_email_'.$code, $email);
            $this->reset();
            $this->check_update(array());

            ?>
            <script> window.location = window.location; </script>
            <?php

			// Put a settings updated message on the screen

	?>
	<?php

		}
        $domain = str_replace(array("http://", "https://"), "", get_bloginfo("url"));
        $license = get_option('nss_plugin_license_'.$code);
        $email = get_option('nss_plugin_license_email_'.$code);
        if(!empty($license) && !empty($email)) {
            $license_status = get_option("nss_plugin_remote_license_".$this->slug);
            if(isset($license_status["value"]))
            $license_status = $license_status["value"];
            else
            $license_status = $this->getRemote_license();
        }
	?>
	<style>
	.grayblock {
		border: solid 1px #ccc;
		background: #eee;
		padding: 1px 8px;
		width: 30%;
	}
	</style>
	<div class=wrap>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<h2><?php echo __("License Settings", "grassblade"); ?></h2>
    <br>
    <?php 
        if(!isset($_POST[ "update_nss_plugin_license_".$code ]) ) {
         if(empty($license_status) || $license_status == "false" || $license_status == "not_found") { ?>
        <div class="notice notice-error">
            <p><?php echo sprintf(__("Please enter a valid license or %s one now.", "grassblade"),"<a href='http://www.nextsoftwaresolutions.com/grassblade-xapi-companion/' target='_blank'>".__("buy", "grassblade")."</a>" ); ?></p>
        </div>
    <?php } else { ?>
            <div class="notice notice-success">
                <p><?php _e("Your license is valid."); ?></p>
            </div>
    <?php } 
        }
    ?>

	<h3><?php _e("Domain", "grassblade"); ?>:</h3>
    <input name="nss_plugin_license_domain_<?php echo $code; ?>" style="min-width:30%" value="<?php echo $domain; ?>" disabled="disabled"/>
    <h3><?php _e("Email", "grassblade"); ?>:</h3>
	<input name="nss_plugin_license_email_<?php echo $code; ?>" style="min-width:30%" value="<?php echo   _e(apply_filters('format_to_edit',$email), 'nss_plugin_updater') ?>" />
	<h3><?php _e("License Key", "grassblade"); ?>:</h3>
	<input name="nss_plugin_license_<?php echo $code; ?>" style="min-width:30%" value="<?php echo   _e(apply_filters('format_to_edit',$license), 'nss_plugin_updater') ?>" />

	<div class="submit">
	<input type="submit" name="update_nss_plugin_license_<?php echo $code; ?>" value="<?php _e('Update License', 'nss_plugin_updater') ?>" class="button button-primary"/></div>
	</form>

	<br><br><br><br>
	<div id="nss_license_footer">
	<?php do_action($code."-nss_license_footer"); ?>
	</div>

	</div>
	<?php
	}

}
