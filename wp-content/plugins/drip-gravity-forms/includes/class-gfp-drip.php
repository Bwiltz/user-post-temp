<?php
/* @package   GFP_Drip\GFP_Drip
 * @author    Naomi C. Bush for gravity+ <support@gravityplus.pro>
 * @copyright 2016 gravity+
 * @license   GPL-2.0+
 * @since     2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class GFP_Drip
 *
 * Main plugin class
 *
 * @since  2.0.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFP_Drip {

	/**
	 * Gravity Forms Add-On object
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @var GFP_Drip_Addon
	 */
	private $addon = null;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function construct() {
	}

	/**
	 * Load WordPress functions
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function run() {

		register_activation_hook( GFP_DRIP_FILE, array( 'GFP_Drip', 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

	}

	/**
	 * Redirect to plugin page on activation
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public static function activate() {

		if ( class_exists( 'GFP_Drip' ) ) {

			GFP_Drip::set_redirect();

		}

	}

	/**
	 * Set redirect flag
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @uses   set_transient()
	 *
	 * @return void
	 */
	public static function set_redirect() {

		set_transient( 'gfp_drip_redirect', true, HOUR_IN_SECONDS );

	}


	/**
	 * Create GF Add-On
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function plugins_loaded() {

		$this->load_textdomain();

		if ( class_exists( 'GFForms' ) ) {

			if ( ! class_exists( 'GFFeedAddOn' ) ) {

				GFForms::include_feed_addon_framework();

			}

			$this->addon = new GFP_Drip_Addon( array(
				                                   'version'                    => GFP_DRIP_CURRENT_VERSION,
				                                   'min_gf_version'             => GFP_DRIP_MIN_GF_VERSION,
				                                   'plugin_slug'                => 'gravityformsdrip',
				                                   'path'                       => plugin_basename( GFP_DRIP_FILE ),
				                                   'full_path'                  => GFP_DRIP_FILE,
				                                   'title'                      => 'Gravity Forms Drip Add-On',
				                                   'short_title'                => 'Drip',
				                                   'url'                        => 'https://getdrip.com',
				                                   'capabilities'               => array(
					                                   'gravityforms_drip_plugin_settings',
					                                   'gravityforms_drip_form_settings',
					                                   'gravityforms_drip_uninstall'
				                                   ),
				                                   'capabilities_settings_page' => array( 'gravityforms_drip_plugin_settings' ),
				                                   'capabilities_form_settings' => array( 'gravityforms_drip_form_settings' ),
				                                   'capabilities_uninstall'     => array( 'gravityforms_drip_uninstall' )
			                                   ) );

		}

	}

	/**
	 * Load language files
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function load_textdomain() {

		$gfp_drip_lang_dir = dirname( plugin_basename( GFP_DRIP_FILE ) ) . '/languages/';
		$gfp_drip_lang_dir = apply_filters( 'gfp_drip_language_dir', $gfp_drip_lang_dir );

		$locale = apply_filters( 'plugin_locale', get_locale(), 'drip-gravity-forms' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'drip-gravity-forms', $locale );

		$mofile_local  = $gfp_drip_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/drip-gravity-forms/' . $mofile;

		if ( file_exists( $mofile_global ) ) {

			load_textdomain( 'drip-gravity-forms', $mofile_global );

		} elseif ( file_exists( $mofile_local ) ) {

			load_textdomain( 'drip-gravity-forms', $mofile_local );

		} else {

			load_plugin_textdomain( 'drip-gravity-forms', false, $gfp_drip_lang_dir );

		}
	}


	/**
	 * Return GF Add-On object
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return GFP_Drip_Addon
	 */
	public function get_addon_object() {

		return $this->addon;

	}

}