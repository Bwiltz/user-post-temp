<?php
/**
 * @wordpress-plugin
 * Plugin Name: Gravity Forms Drip Add-On
 * Plugin URI: http://kb.getdrip.com/integrations/integrating-drip-and-gravity-forms/
 * Description: Send Gravity Forms submissions to Drip
 * Version: 2.1.1
 * Author: gravity+ for Drip
 * Author URI: https://www.getdrip.com/
 * Text Domain: drip-gravity-forms
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package   GFP_Drip
 * @version   2.1.1
 * @author    Naomi C. Bush for gravity+ <support@gravityplus.pro>
 * @license   GPL-2.0+
 * @link      https://gravityplus.pro
 * @copyright 2014-2016 gravity+
 *
 * last updated: September 15, 2016
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GFP_DRIP_CURRENT_VERSION', '2.1.1' );

/**
 * Minimum Gravity Forms version allowed
 *
 * @since  2.0.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
define( 'GFP_DRIP_MIN_GF_VERSION', '1.9' );

define( 'GFP_DRIP_FILE', __FILE__ );
define( 'GFP_DRIP_PATH', plugin_dir_path( __FILE__ ) );
define( 'GFP_DRIP_URL', plugin_dir_url( __FILE__ ) );
define( 'GFP_DRIP_SLUG', plugin_basename( dirname( __FILE__ ) ) );

//Load all of the necessary class files for the plugin
require_once( 'includes/class-loader.php' );
GFP_Drip_Loader::load();

$gravityformsdrip = new GFP_Drip();
$gravityformsdrip->run();
