<?php
/*
 * @package   GFP_Drip\GFP_Drip_Loader
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 * @copyright 2016 gravity+
 * @license   GPL-2.0+
 * @since     2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Loader
 *
 * Adapted from WP Metadata API UI
 *
 * @since  2.0.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFP_Drip_Loader {

	private static $_autoload_classes = array(
		'GFP_Drip'            => 'class-gfp-drip.php',
		'GFP_Drip_Addon'      => 'class-addon.php',
		'GFP_Drip_API_Logger' => 'api/class-drip-api-logger.php',
		'GetDrip_WP_API'      => 'api/class-getdrip-wp-api.php',
	);

	static function load() {

		spl_autoload_register( array( __CLASS__, '_autoloader' ) );

	}

	/**
	 * @param string $class_name
	 * @param string $class_filepath
	 *
	 * @return bool Return true if it was registered, false if not.
	 */
	static function register_autoload_class( $class_name, $class_filepath ) {

		if ( ! isset( self::$_autoload_classes[ $class_name ] ) ) {

			self::$_autoload_classes[ $class_name ] = $class_filepath;

			return true;

		}

		return false;

	}

	/**
	 * @param string $class_name
	 */
	static function _autoloader( $class_name ) {

		if ( isset( self::$_autoload_classes[ $class_name ] ) ) {

			$filepath = self::$_autoload_classes[ $class_name ];

			/**
			 * @todo This needs to be made to work for Windows...
			 */
			if ( '/' == $filepath[ 0 ] ) {

				require_once( $filepath );

			} else {

				require_once( dirname( __FILE__ ) . "/{$filepath}" );

			}

		}

	}
}