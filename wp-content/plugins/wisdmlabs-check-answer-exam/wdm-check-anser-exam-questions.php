<?php

/**
 * Plugin Name: Retake Question
 * Plugin URI: https://wisdmlabs.com
 * Description: Retake question until answer is right.
 * Version: 1.0.0
 * Author: WisdmLabs
 * Author URI: https://wisdmlabs.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wdm_cae
 * Domain Path: /languages
 */

/**
 * namespace: wdmcheckanswerexam
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Setup Constants
 */

if (! defined('WDM_CAE')) {
    define('WDM_CAE', '1.0.0');
}

if (! defined('WDM_CAE_PLUGIN_DIR')) {
    define('WDM_CAE_PLUGIN_DIR', plugin_dir_path(__FILE__) . '/');
}

if (! defined('WDM_CAE_PLUGIN_URL')) {
    define('WDM_CAE_PLUGIN_URL', plugin_dir_url(__FILE__) . '/');
}

require WDM_CAE_PLUGIN_DIR . 'includes/class-ld-dependency-check.php';
wdmcheckanswerexam\Ld_Dependency_Check::get_instance()->set_message(__('Check Answer Exam by WisdmLabs requires the following plugin(s) %s be active: ', 'WDM_CAE'));

// To include files, but mind sequence.
$include_arr = array(
    'admin',
    'public'
   );

foreach ($include_arr as $dir) {
    include_once WDM_CAE_PLUGIN_DIR . $dir . '/main.php';
}

