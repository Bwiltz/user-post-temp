<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WP_Plugin_Install_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php' );
}

class grassblade_addon_plugins extends WP_Plugin_Install_List_Table {

	function __construct() {

		add_action( 'admin_menu', array($this,'addon_plugins_menu'), 4);
	}

	/**
	 *
	 * Add Addon Plugins to the menu.
	 *
	 */

	function addon_plugins_menu() {
		add_submenu_page("grassblade-lrs-settings", __("Add-ons", "grassblade"), __("Add-ons", "grassblade"),'manage_options','grassblade-addons', array($this, 'addon_plugins_menupage') );
	}

	function addon_plugins_menupage(){
		//must check that the user has the required capability 
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.') );
	    }
	    ?>
	    <div class="wrap">
			<h2>
				<img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', dirname(dirname(__FILE__))); ?>"/>
				GrassBlade Add-ons
			</h2>
			<br>
			<div  class="wp-list-table widefat">
				<div id="the-list">
					<?php $this->get_grassblade_addon_plugins();
						  parent::display_rows(); 
					?>
				</div>
			</div>
		</div>
	    <?php
	} 

	function get_grassblade_addon_plugins(){

		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$paged = $this->get_pagenum();

		$installed_plugins = $this->get_installed_plugins();

		$args = array(
			'page' => $paged,
			'per_page' => 30,
			'fields' => array(
				'last_updated' => true,
				'icons' => true,
				'active_installs' => true,
			),

			// Send the locale and installed plugin slugs to the API so it can provide context-sensitive results.
			'locale' => get_user_locale(),
			'installed_plugins' => array_keys( $installed_plugins ),
		);

		$args['author'] = sanitize_title_with_dashes( 'liveaspankaj' );

		$api = plugins_api( 'query_plugins', $args );

		if ( is_wp_error( $api ) ) {
			$this->error = $api;
			return;
		}

		$grassblade_plugins = $api->plugins;

		if ( $this->orderby ) {
			uasort( $grassblade_plugins, array( $this, 'order_callback' ) );
		}

		$this->set_pagination_args( array(
			'total_items' => $api->info['results'],
			'per_page' => $args['per_page'],
		) );

		if ( isset( $api->info['groups'] ) ) {
			$this->groups = $api->info['groups'];
		}

		if ( $installed_plugins ) {
			$js_plugins = array_fill_keys(
				array( 'all', 'search', 'active', 'inactive', 'recently_activated', 'mustuse', 'dropins' ),
				array()
			);

			$js_plugins['all'] = array_values( wp_list_pluck( $installed_plugins, 'plugin' ) );
			$upgrade_plugins   = wp_filter_object_list( $installed_plugins, array( 'upgrade' => true ), 'and', 'plugin' );

			if ( $upgrade_plugins ) {
				$js_plugins['upgrade'] = array_values( $upgrade_plugins );
			}

			wp_localize_script( 'updates', '_wpUpdatesItemCounts', array(
				'plugins' => $js_plugins,
				'totals'  => wp_get_update_data(),
			) );
		}

		$this->items = $grassblade_plugins;
	}

} // end of class 

$gbap = new grassblade_addon_plugins();