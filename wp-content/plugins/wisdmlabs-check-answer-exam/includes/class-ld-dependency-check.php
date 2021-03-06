<?php

namespace wdmcheckanswerexam;

/**
 * Set up LearnDash Dependency Check
 */

if (!class_exists('Ld_Dependency_Check')) {
    final class Ld_Dependency_Check
    {

        private static $instance;
        
        
        /**
         * The displayed message shown to the user on admin pages.
         */
        private $admin_notice_message = '';

        /**
         * The array of plugin) to check Should be key => label paird. The label can be anything to display
         */
        private $plugins_to_check = array(
            'sfwd-lms/sfwd_lms.php' =>  '<a href="http://learndash.com">LearnDash LMS</a>'
        );

        /**
         * Array to hold the inactive plugins. This is populated during the
         * admin_init action via the function call to check_inactive_plugin_dependency()
         */
        private $plugins_inactive = array();
        
        
        /**
         * LearnDash_ProPanel constructor.
         */
        public function __construct()
        {
            add_action('plugins_loaded', array( $this, 'plugins_loaded' ), 1);
        }

        public static function get_instance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            }

            return static::$instance;
        }
        
        /**
         * LearnDash_ProPanel constructor.
         */
        public function check_dependency_results()
        {
            if (empty($this->plugins_inactive)) {
                return true;
            }
            
            return false;
        }

        /**
         * callback function for the admin_init action
         */
        function plugins_loaded()
        {
            $this->check_inactive_plugin_dependency();
        }

        /**
         * Function called during the admin_init process to check if required plugins
         * are present and active. Handles regular and Multisite checks.
         */
        function check_inactive_plugin_dependency($set_admin_notice = true)
        {

            if (!empty($this->plugins_to_check)) {
                if (!function_exists('is_plugin_active')) {
                    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                }

                foreach ($this->plugins_to_check as $plugin_key => $plugin_label) {
                    if (!is_plugin_active($plugin_key)) {
                        if (is_multisite()) {
                            if (!is_plugin_active_for_network($plugin_key)) {
                                $this->plugins_inactive[$plugin_key] = $plugin_label;
                            }
                        } else {
                            $this->plugins_inactive[$plugin_key] = $plugin_label;
                        }
                    }
                }

                if (( !empty($this->plugins_inactive) ) && ( $set_admin_notice )) {
                    add_action('admin_notices', array( $this, 'notify_user_learndash_required' ));
                }
            }

            return $this->plugins_inactive;
        }

        /**
         * Function to set custom admin motice message
         */
        function set_message($message = '')
        {
            if (!empty($message)) {
                $this->admin_notice_message = $message;
            }
        }

        /**
         * Notify user that LearnDash is required.
         */
        public function notify_user_learndash_required()
        {
            if (( !empty($this->admin_notice_message) ) && ( !empty($this->plugins_inactive) )) {
                $admin_notice_message = sprintf($this->admin_notice_message, implode(', ', $this->plugins_inactive));
                if (!empty($admin_notice_message)) {
                    ?>
                    <div class="notice notice-error ld-notice-error is-dismissible">
                        <p><?php echo $admin_notice_message; ?></p>
                    </div>
                    <?php
                }
            }
        }

        // End of functions
    }
}
