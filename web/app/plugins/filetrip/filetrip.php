<?php
/**
 * Filetrip
 *
 * @package   filetrip
 * @author    Abdulrhman Elbuni
 * @link      http://www.itechflare.com/
 * @copyright 2016-2017 iTechFlare
 *
 * @wordpress-plugin
 *
 * Plugin Name: FileTrip
 * Plugin URI:  http://www.itechflare.com/
 * Description: FileTrip is a smart Media Platform for Wordpress. FileTrip makes it easy to distribute files from WordPress to the Cloud on autopilot. When users upload files, Filetrip sends copies to multiple destinations of your choice.
 * Version:     2.0.7
 * Author:      Abdulrhman Elbuni
 * Author URI:  http://www.itechflare.com/
 * Text Domain: filetrip-plugin
 * Domain Path: /languages
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (function_exists('ini_set')) {
// This line is to configure PHP to allow using memory for image processing operation
    ini_set('memory_limit', '-1');
}

/* ----------------------------------------------------
 * Constant
 * ----------------------------------------------------
 */

/* Current Directory Path */
define('ITECH_FILETRIP_PLUGIN_DIR_PATH', __DIR__ . '/');//plugin_dir_path( __FILE__ ));
/* Current Directory URI */
define('ITECH_FILETRIP_PLUGIN_URL', plugins_url( '', __FILE__));
/* Includes Directory */
define('ITECH_FILETRIP_INCLUDES_DIR', __DIR__ .'/includes/');
/* Functions Directory */
define('ITECH_FILETRIP_FUNCTIONS_DIR', __DIR__ .'/functions/');
/* Core Extension Library */
define('ITECH_FILETRIP_LIB_EXTENSION_DIR', ITECH_FILETRIP_INCLUDES_DIR . 'lib-extension/');
/* Extension Directory */
define('ITECH_EXTENSION_DIRECTORY', __DIR__ .'/extensions/');

/* ----------------------------------------------------
 * Generate Random security key for backup
 * ----------------------------------------------------
 */
$fileTripRandomKey = ABSPATH . @microtime();
defined('AUTH_KEY') && $fileTripRandomKey .= AUTH_KEY;
defined('SECURE_AUTH_KEY') && $fileTripRandomKey .= SECURE_AUTH_KEY;
defined('NONCE_SALT') && $fileTripRandomKey .= NONCE_SALT;
$fileTripRandomKey .= @microtime();
define('FILETRIP_SECURE_KEY', md5($fileTripRandomKey));
// clean
unset($fileTripRandomKey);

/* ----------------------------------------------------
 * File Includes
 * ----------------------------------------------------
 */

/* Require Constant */
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . '/classes/class-filetrip-constants.php';

/**
 * Include Function
 */
/* paths & misc */
require_once ITECH_FILETRIP_FUNCTIONS_DIR . 'filetrip-path.php';
/* Advance meta boxes */
require_once ITECH_FILETRIP_FUNCTIONS_DIR . 'filetrip-activate.php';

// Top dependencies
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/settings-api/class.settings-api.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'cmb2/filetrip-meta.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/functions.php';

// FileTrip Interfaces
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/interfaces/filetrip-channel-interface.php';

// Filetrip Core Classes
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/class-filetrip-settings.php';
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/class-filetrip-upload-list-table.php';
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/admin-notice-helper/admin-notice-helper.php';
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/class-filetrip-channel-utility.php';
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/class-filetrip-uploader-recorder.php';
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/class-filetrip-upload-distributer.php';

/* iTechFlare core extension's platform loader */
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'itechflare-core/itechflare.php';

// Required files for registering the post type and taxonomies.
require_once ITECH_FILETRIP_LIB_EXTENSION_DIR . 'classes/class-filetrip-type-registrations.php';

require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/backup-core.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-backup.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-backdrop.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-services.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-email.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-notices.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-requirements.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-schedule.php';
require_once ITECH_FILETRIP_INCLUDES_DIR . 'arfaly-lib/arfaly-backup/class-schedules.php';

// require Initiator
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'filetrip-init.php';

// Core Filetrip Class
require_once ITECH_FILETRIP_INCLUDES_DIR . 'class.filetrip_uploader.php';

// Main plugin entry
$filetrip_uploader_obj = new Filetrip_Uploader;