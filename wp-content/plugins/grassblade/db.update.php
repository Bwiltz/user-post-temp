<?php

class GrassBlade_DB {

	function __construct() {
		if(is_admin())
		add_action( 'plugins_loaded', array($this, 'check_n_upgrade_db'));
	}
	function get_version() {
		return get_option("grassblade_version");
	}
	function update_version() {
		update_option("grassblade_version", GRASSBLADE_VERSION);
	}
	function check_n_upgrade_db() {
		$current_db_version = $this->get_version();
		if(version_compare($current_db_version, GRASSBLADE_VERSION) < 0)
			$this->upgrade_db();
		if(version_compare($current_db_version, GRASSBLADE_VERSION) < 0)
			$this->upgrade_scorm_db();
	}
	function upgrade_db() {
		global $wpdb;
		$table_name = $wpdb->prefix . "grassblade_completions";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			content_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			status varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
			percentage float DEFAULT NULL,
			score float DEFAULT NULL,
			timespent int(11) DEFAULT NULL,
			statement text COLLATE utf8_unicode_ci,
			timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
			) $charset_collate";
		dbDelta($sql);
		$this->update_version();
	}
	function upgrade_scorm_db() {
		global $wpdb;
		$table_name = $wpdb->prefix . "grassblade_scorm_data";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			content_id int(11) NOT NULL,
			user_id int(11) NOT NULL,
			registration_id varchar(255) DEFAULT NULL,
			var_key varchar(255) NOT NULL,
			var_value text NOT NULL,
			timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
			) $charset_collate";
		dbDelta($sql);
		$this->update_version();
	}
}
