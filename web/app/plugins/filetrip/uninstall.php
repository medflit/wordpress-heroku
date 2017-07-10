<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

if ( ! defined( 'FILETRIP_BKP_REQUIRED_WP_VERSION' ) ) {
	define( 'FILETRIP_REQUIRED_WP_VERSION', '4.2' );
}

// Don't activate on old versions of WordPress
global $wp_version;

if ( version_compare( $wp_version, FILETRIP_REQUIRED_WP_VERSION, '<' ) ) {
	return;
}

if ( ! defined( 'ITECH_FILETRIP_PLUGIN_DIR_PATH' ) ) {
	define( 'ITECH_FILETRIP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

// Load the schedules
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/arfaly-lib/arfaly-backup/backup-core.php';
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/arfaly-lib/arfaly-backup/class-backup.php';
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/arfaly-lib/arfaly-backup/class-services.php';
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/arfaly-lib/arfaly-backup/class-schedule.php';
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/arfaly-lib/arfaly-backup/class-schedules.php';
require_once ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/lib-extension/classes/class-filetrip-constants.php';

$schedules = FILETRIP_BKP_Schedules::get_instance();

// Cancel all the schedules and delete all the backups
foreach ( $schedules->get_schedules() as $schedule ) {
	$schedule->cancel( true );
}

// Remove the backups directory
filetrip_bkp_rmdirtree( filetrip_bkp_path() );

// Remove all the options
foreach ( array( 'filetrip_settings', 'filetrip_backup_setting', 'filetrip_mime_setting', 'filetrip_dropbox_setting' , 'filetrip_google_drive_setting', 'filetrip_ftp_setting', 'filetrip_bkp_enable_support', 'filetrip_bkp_plugin_version', 'filetrip_bkp_path', 'filetrip_bkp_default_path', 'filetrip_bkp_upsell' ) as $option ) {
	delete_option( $option );
}

// Delete all transients
foreach ( array( 'filetrip_error_transient', 'filetrip_bkp_plugin_data', 'filetrip_bkp_directory_filesizes', 'filetrip_bkp_directory_filesize_running' ) as $transient ) {
	delete_transient( $transient );
}
