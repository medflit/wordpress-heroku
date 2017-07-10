<?php

/**
 * Setup the plugin defaults on activation
 */
function filetrip_bkp_activate() {

	// loads the translation files
	load_plugin_textdomain( 'filetrip_bkp', false, FILETRIP_BKP_PLUGIN_LANG_DIR );

	// Don't activate on old versions of WordPress
	global $wp_version;

	if ( version_compare( $wp_version, FILETRIP_BKP_REQUIRED_WP_VERSION, '<' ) ) {

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( __FILE__ );

		if ( isset( $_GET['action'] ) && ( 'activate' === $_GET['action'] || 'error_scrape' === $_GET['action'] ) ) {
			wp_die( sprintf( __( 'BackUpWordPress requires WordPress version %s or greater.', 'filetrip-plugin' ), FILETRIP_BKP_REQUIRED_WP_VERSION ), __( 'BackUpWordPress', 'filetrip-plugin' ), array( 'back_link' => true ) );
		}
	}

	// Run deactivate on activation in-case it was deactivated manually
	filetrip_bkp_deactivate();

}

function clear_all_crons( $hook ) {
    $crons = _get_cron_array();
    if ( empty( $crons ) ) {
        return;
    }
    foreach( $crons as $timestamp => $cron ) {
        if ( ! empty( $cron[$hook] ) )  {
            unset( $crons[$timestamp][$hook] );
        }
    }
    _set_cron_array( $crons );
}

function filetrip_bkp_backups_number( $schedule ) {

	$number = count( $schedule->get_backups() );

	if ( 0 === $number ) {
		$output = sprintf( __( 'No backups completed', 'filetrip-plugin' ) );
	} else {
		$output = sprintf( __( 'Backup completed', 'filetrip-plugin' ) );
	}

	echo apply_filters( 'filetrip_bkp_backups_number', $output, $number );
}

function filetrip_bkp_human_get_type( $type, FILETRIP_BKP_Scheduled_Backup $schedule = null ) {

	if ( strpos( $type, 'complete' ) !== false )
		return __( 'Database and Files', 'filetrip-plugin' );

	if ( strpos( $type, 'file' ) !== false )
		return __( 'Files', 'filetrip-plugin' );

	if ( strpos( $type, 'database' ) !== false )
		return __( 'Database', 'filetrip-plugin' );

	if ( ! is_null( $schedule ) )
		return filetrip_bkp_human_get_type( $schedule->get_type() );

	return __( 'Legacy', 'filetrip-plugin' );

}

function filetrip_bkp_get_backup_row( $file, FILETRIP_BKP_Scheduled_Backup $schedule ) {

	$encoded_file = urlencode( base64_encode( $file ) );
	$offset       = get_option( 'gmt_offset' ) * 3600;

	?>

	<tr class="filetrip_bkp_manage_backups_row">

		<th scope="row">
			<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), @filemtime( $file ) + $offset ) ); ?>
		</th>

		<td class="code">
			<?php echo esc_html( size_format( @filesize( $file ) ) ); ?>
		</td>

		<td><?php echo esc_html( filetrip_bkp_human_get_type( $file, $schedule ) ); ?></td>

		<td>

			<?php if (  filetrip_bkp_is_path_accessible( filetrip_bkp_path() )  ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'filetrip_bkp_backup_archive' => $encoded_file, 'filetrip_bkp_schedule_id' => $schedule->get_id(), 'action' => 'filetrip_bkp_request_download_backup' ), admin_url( 'admin-post.php' ) ), 'filetrip_bkp_download_backup', 'filetrip_bkp_download_backup_nonce' ) ); ?>" class="download-action"><?php _e( 'Download', 'filetrip-plugin' ); ?></a> |
			<?php endif; ?>

			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'filetrip_bkp_backup_archive' => $encoded_file, 'filetrip_bkp_schedule_id' => $schedule->get_id(), 'action' => 'filetrip_bkp_request_delete_backup' ), admin_url( 'admin-post.php' ) ), 'filetrip_bkp_delete_backup', 'filetrip_bkp_delete_backup_nonce' ) ); ?>" class="delete-action"><?php _e( 'Delete', 'filetrip-plugin' ); ?></a>

		</td>
        <td>
          <?php 
			// Apply filter to retrieve active channels 
			$registeredChannels = array();
			$registeredChannels = apply_filters('itf/filetrip/filter/register/channels', $registeredChannels);

			$query_s = admin_url(Filetrip_Constants::FILETRIP_DISTRIBUTOR_PAGE);
    		$query_s .= '&source='.Filetrip_Constants::Transfer_Type('backup').'&backup_archive='.$encoded_file.'&';

			foreach($registeredChannels as $channel){
				// Add security nonce to the query
				$query_ext = 'security='.wp_create_nonce($channel['security']).'&file_size='.@filesize( $file ).'&channel=' . $channel['channel_key'];
				echo arfaly_get_icon($channel['channel_key'],null,'20',$query_s.$query_ext, 'Send Backup to ' . $channel['channel_name']);
			}

          ?>
        </td>
	</tr>

<?php }

/**
 * Cleanup on plugin deactivation
 *
 * Removes options and clears all cron schedules
 */
function filetrip_bkp_deactivate() {

	// Clean up the backups directory
	filetrip_bkp_cleanup();

	$schedules = FILETRIP_BKP_Schedules::get_instance();

	// Clear schedule crons
	foreach ( $schedules->get_schedules() as $schedule ) {
		$schedule->unschedule();
	}

	// Remove the directory filesize cache
	delete_transient( 'filetrip_bkp_directory_filesizes' );

}

function filetrip_bkp_get_settings_url() {

    $url = admin_url( Filetrip_Constants::OPTION_PAGE );

	Filetrip_BKP_schedules::get_instance()->refresh_schedules();

	if ( ! empty( $_REQUEST['filetrip_bkp_schedule_id'] ) && FILETRIP_BKP_schedules::get_instance()->get_schedule( sanitize_text_field( $_REQUEST['filetrip_bkp_schedule_id'] ) ) ) {
		$url = (add_query_arg( 'filetrip_bkp_schedule_id', sanitize_text_field( $_REQUEST['filetrip_bkp_schedule_id'] ), $url ));
	}

	return $url;

}

/**
 * Send the download file to the browser and then redirect back to the backups page
 */
function filetrip_bkp_request_download_backup() {

	check_admin_referer( 'filetrip_bkp_download_backup', 'filetrip_bkp_download_backup_nonce' );

	if ( ! file_exists( sanitize_text_field( base64_decode( $_GET['filetrip_bkp_backup_archive'] ) ) )  ) {
		return;
	}

	$url = str_replace( Filetrip_Backup::conform_dir( Filetrip_Backup::get_home_path() ), home_url(), trailingslashit( dirname( sanitize_text_field( base64_decode( $_GET['filetrip_bkp_backup_archive'] ) ) ) ) ) . urlencode( pathinfo( sanitize_text_field( base64_decode( $_GET['filetrip_bkp_backup_archive'] ) ), PATHINFO_BASENAME ) );

	global $is_apache;

	if ( $is_apache ) {

		// Force the .htaccess to be rebuilt
		if ( file_exists( filetrip_bkp_path() . '/.htaccess' ) )
			unlink( filetrip_bkp_path() . '/.htaccess' );

		filetrip_bkp_path();

		$url = esc_url(add_query_arg( 'key', FILETRIP_SECURE_KEY, $url ));

	}

	wp_safe_redirect( $url, 303 );

	die;

}
add_action( 'admin_post_filetrip_bkp_request_download_backup', 'filetrip_bkp_request_download_backup' );

/**
 * Delete the backup and then redirect back to the backups page
 */
function filetrip_bkp_request_delete_backup() {

	check_admin_referer( 'filetrip_bkp_delete_backup', 'filetrip_bkp_delete_backup_nonce' );

	$schedule = new FILETRIP_BKP_Scheduled_Backup( sanitize_text_field( urldecode( $_GET['filetrip_bkp_schedule_id'] ) ) );

	$deleted = $schedule->delete_backup( sanitize_text_field( base64_decode( $_GET['filetrip_bkp_backup_archive'] ) ) );

	if ( is_wp_error( $deleted ) ) {
		wp_die( $deleted->get_error_message() );
	}

	wp_safe_redirect( filetrip_bkp_get_settings_url(), 303 );

	die;

}
add_action( 'admin_post_filetrip_bkp_request_delete_backup', 'filetrip_bkp_request_delete_backup' );

/**
 * Handles anything that needs to be
 * done when the plugin is updated
 */
function filetrip_bkp_update() {

	// Update from backUpWordPress 0.4.5
	if ( get_option( 'bkpwp_max_backups' ) ) {

		// Carry over the custom path
		if ( $legacy_path = get_option( 'bkpwppath' ) ) {
			update_option( 'filetrip_bkp_path', $legacy_path );
		}

		// Options to remove
		$legacy_options = array(
			'bkpwp_archive_types',
			'bkpwp_automail_from',
			'bkpwp_domain',
			'bkpwp_domain_path',
			'bkpwp_easy_mode',
			'bkpwp_excludelists',
			'bkpwp_install_user',
			'bkpwp_listmax_backups',
			'bkpwp_max_backups',
			'bkpwp_presets',
			'bkpwp_reccurrences',
			'bkpwp_schedules',
			'bkpwp_calculation',
			'bkpwppath',
			'bkpwp_status_config',
			'bkpwp_status'
		);

		foreach ( $legacy_options as $option ) {
			delete_option( $option );
		}

		global $wp_roles;

		$wp_roles->remove_cap( 'administrator', 'manage_backups' );
		$wp_roles->remove_cap( 'administrator', 'download_backups' );

		wp_clear_scheduled_hook( 'bkpwp_schedule_bkpwp_hook' );

	}

	// Version 1 to 2
	if ( get_option( 'filetrip_bkp_plugin_version' ) && version_compare( '2.0', get_option( 'filetrip_bkp_plugin_version' ), '>' ) ) {

		/**
		 * Setup a backwards compatible schedule
		 */
		$legacy_schedule = new FILETRIP_BKP_Scheduled_Backup( 'backup' );

		// Backup type
		if ( ( defined( 'FILETRIP_BKP_FILES_ONLY' ) && FILETRIP_BKP_FILES_ONLY ) || get_option( 'filetrip_bkp_files_only' ) ) {
			$legacy_schedule->set_type( 'file' );
		}

		elseif ( ( defined( 'FILETRIP_BKP_DATABASE_ONLY' ) && FILETRIP_BKP_DATABASE_ONLY ) || get_option( 'filetrip_bkp_database_only' ) ) {
			$legacy_schedule->set_type( 'database' );
		}

		else {
			$legacy_schedule->set_type( 'complete' );
		}

		// Daily schedule time
		if ( defined( 'FILETRIP_BKP_DAILY_SCHEDULE_TIME' ) && FILETRIP_BKP_DAILY_SCHEDULE_TIME ) {
			$legacy_schedule->set_schedule_start_time( strtotime( FILETRIP_BKP_DAILY_SCHEDULE_TIME ) );
		}

		// Backup schedule
		$legacy_schedule->set_reoccurrence( get_option( 'filetrip_bkp_schedule_frequency', 'filetrip_bkp_daily' ) );

		// Automatic backups disabled?
		if ( ( defined( 'FILETRIP_BKP_DISABLE_AUTOMATIC_BACKUP' ) && FILETRIP_BKP_DISABLE_AUTOMATIC_BACKUP ) || get_option( 'filetrip_bkp_disable_automatic_backup' ) ) {
			$legacy_schedule->set_reoccurrence( 'manually' );
		}

		// Max backups
		if ( defined( 'FILETRIP_BKP_MAX_BACKUPS' ) && is_numeric( FILETRIP_BKP_MAX_BACKUPS ) ) {
			$legacy_schedule->set_max_backups( (int) FILETRIP_BKP_MAX_BACKUPS );
		}

		else {
			$legacy_schedule->set_max_backups( (int) get_option( 'filetrip_bkp_max_backups', 10 ) );
		}

		// Excludes
		if ( get_option( 'filetrip_bkp_excludes' ) ) {
			$legacy_schedule->set_excludes( get_option( 'filetrip_bkp_excludes' ) );
		}

		// Backup email
		if ( defined( 'FILETRIP_BKP_EMAIL' ) && is_email( FILETRIP_BKP_EMAIL ) ) {
			$legacy_schedule->set_service_options( 'FILETRIP_BKP_Email_Service', array( 'email' => FILETRIP_BKP_EMAIL ) );
		}

		elseif ( is_email( get_option( 'filetrip_bkp_email_address' ) ) ) {
			$legacy_schedule->set_service_options( 'FILETRIP_BKP_Email_Service', array( 'email' => get_option( 'filetrip_bkp_email_address' ) ) );
		}

		// Set the archive filename to what it used to be
		$legacy_schedule->set_archive_filename( implode( '-', array( get_bloginfo( 'name' ), 'backup', date( 'Y-m-d-H-i-s', current_time( 'timestamp' ) ) ) ) . '.zip' );

		$legacy_schedule->save();

		// Remove the legacy options
		foreach ( array( 'filetrip_bkp_database_only', 'filetrip_bkp_files_only', 'filetrip_bkp_max_backups', 'filetrip_bkp_email_address', 'filetrip_bkp_email', 'filetrip_bkp_schedule_frequency', 'filetrip_bkp_disable_automatic_backup' ) as $option_name ) {
			delete_option( $option_name );
		}

	}

	// Update from 2.2.4
	if ( get_option( 'filetrip_bkp_plugin_version' ) && version_compare( '2.2.5', get_option( 'filetrip_bkp_plugin_version' ), '>' ) ) {

		$schedules = FILETRIP_BKP_Schedules::get_instance();

		// Loop through all schedules and re-set the reccurrence to include filetrip_bkp_
		foreach ( $schedules->get_schedules() as $schedule ) {

			$reoccurrence = $schedule->get_reoccurrence();

			if ( $reoccurrence !== 'manually' && strpos( $reoccurrence, 'filetrip_bkp_' ) === false ) {
				$schedule->set_reoccurrence( 'filetrip_bkp_' . $schedule->get_reoccurrence() );
			}

			$schedule->save();

		}

	}

	// Update from 2.x to 3.0
	if ( get_option( 'filetrip_bkp_plugin_version' ) && version_compare( '2.0', get_option( 'filetrip_bkp_plugin_version' ), '>' ) ) {

		// Remove the plugin data cache
		delete_transient( 'filetrip_bkp_plugin_data' );

	}

	// Every update
	if ( get_option( 'filetrip_bkp_plugin_version' ) && version_compare( FILETRIP_BKP_VERSION, get_option( 'filetrip_bkp_plugin_version' ), '>' ) ) {

		filetrip_bkp_deactivate();

		// re-calcuate the backups directory and move to it.
		if ( ! defined( 'FILETRIP_BKP_PATH' ) ) {

			$old_path = filetrip_bkp_path();

			delete_option( 'filetrip_bkp_path' );
			delete_option( 'filetrip_bkp_default_path' );

			filetrip_bkp_path_move( $old_path, filetrip_bkp_path() );

		}

		// Force .htaccess to be re-written
		if ( file_exists( filetrip_bkp_path() . '/.htaccess' ) ) {
			unlink( filetrip_bkp_path() . '/.htaccess' );
		}

		// Force index.html to be re-written
		if ( file_exists( filetrip_bkp_path() . '/index.html' ) ) {
			unlink( filetrip_bkp_path() . '/index.html' );
		}

	}

	// Update the stored version
	if ( get_option( 'filetrip_bkp_plugin_version' ) !== FILETRIP_BKP_VERSION ) {
		update_option( 'filetrip_bkp_plugin_version', FILETRIP_BKP_VERSION );
	}

}

/**
 * Setup the default backup schedules
 */
function filetrip_bkp_setup_default_schedules() {
    /* **************** Daily and Weekly backup is been disabled ************** */
	/*
    $schedules = FILETRIP_BKP_Schedules::get_instance();

	if ( $schedules->get_schedules() )
		return;

	$database_daily = new FILETRIP_BKP_Scheduled_Backup( (string) time() );
	$database_daily->set_type( 'database' );
	$database_daily->set_schedule_start_time( filetrip_bkp_determine_start_time( 'filetrip_bkp_daily', array( 'hours' => '23', 'minutes' => '0' ) ) );
	$database_daily->set_reoccurrence( 'filetrip_bkp_daily' );
	$database_daily->set_max_backups( 7 );
	$database_daily->save();

	$complete_weekly = new FILETRIP_BKP_Scheduled_Backup( (string) ( time() + 1 ) );
	$complete_weekly->set_type( 'complete' );
	$complete_weekly->set_schedule_start_time( filetrip_bkp_determine_start_time( 'filetrip_bkp_weekly', array( 'day_of_week' => 'sunday', 'hours' => '3', 'minutes' => '0' ) ) );
	$complete_weekly->set_reoccurrence( 'filetrip_bkp_weekly' );
	$complete_weekly->set_max_backups( 3 );
	$complete_weekly->save();

	$schedules->refresh_schedules();

	function filetrip_bkp_default_schedules_setup_warning() {
		echo '<div id="filetrip_bkp-warning" class="updated fade"><p><strong>' . __( 'Filetrip has setup your default schedules.', 'filetrip-plugin' ) . '</strong> ' . __( 'By default Filetrip performs a daily backup of your database and a weekly backup of your database &amp; files. You can only modify the weekly backup in settings.', 'filetrip-plugin' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'filetrip_bkp_default_schedules_setup_warning' );
    
    */

}

//add_action( 'admin_init', 'filetrip_bkp_setup_default_schedules' );

/**
 * Return an array of cron schedules
 *
 * @param $schedules
 * @return array $reccurrences
 */
function filetrip_bkp_cron_schedules( $schedules ) {

	$schedules['filetrip_bkp_hourly']      = array( 'interval' => HOUR_IN_SECONDS, 'display' => __( 'Once Hourly', 'filetrip-plugin' ) );
	$schedules['filetrip_bkp_twicedaily']  = array( 'interval' => 12 * HOUR_IN_SECONDS, 'display' => __( 'Twice Daily', 'filetrip-plugin' ) );
	$schedules['filetrip_bkp_daily']       = array( 'interval' => DAY_IN_SECONDS, 'display' => __( 'Once Daily', 'filetrip-plugin' ) );
	$schedules['filetrip_bkp_weekly']      = array( 'interval' => WEEK_IN_SECONDS, 'display' => __( 'Once Weekly', 'filetrip-plugin' ) );
	$schedules['filetrip_bkp_fortnightly'] = array( 'interval' => 2 * WEEK_IN_SECONDS, 'display' => __( 'Once Biweekly', 'filetrip-plugin' ) );
	$schedules['filetrip_bkp_monthly']     = array( 'interval' => 30 * DAY_IN_SECONDS, 'display' => __( 'Once Monthly', 'filetrip-plugin' ) );

	return $schedules;
}

add_filter( 'cron_schedules', 'filetrip_bkp_cron_schedules' );

/**
 * Recursively delete a directory including
 * all the files and sub-directories.
 *
 * @param string $dir
 * @return bool
 * @return bool|WP_Error
 */
function filetrip_bkp_rmdirtree( $dir ) {

	if ( false !== strpos( FILETRIP_Backup::get_home_path(), $dir ) )
		return new WP_Error( 'filetrip_bkp_invalid_action_error', sprintf( __( 'You can only delete directories inside your WordPress installation', 'filetrip-plugin' ) ) );

	if ( is_file( $dir ) )
		@unlink( $dir );

	if ( ! is_dir( $dir ) || ! is_readable( $dir ) )
		return false;

	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ), RecursiveIteratorIterator::CHILD_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD );

	foreach ( $files as $file ) {

		if ( $file->isDir() )
			@rmdir( $file->getPathname() );

		else
			@unlink( $file->getPathname() );

	}

	@rmdir( $dir );

	return true;
}

/**
 * Get the path to the backups directory
 *
 * Will try to create it if it doesn't exist
 * and will fallback to default if a custom dir
 * isn't writable.
 */
function filetrip_bkp_path() {

	global $is_apache;

	$path = untrailingslashit( get_option( 'filetrip_bkp_path' ) );

	// Allow the backups path to be defined
	if ( defined( 'FILETRIP_BKP_PATH' ) && FILETRIP_BKP_PATH )
		$path = untrailingslashit( FILETRIP_BKP_PATH );

	// If the dir doesn't exist or isn't writable then use the default path instead instead
	if ( ( ! $path || ( is_dir( $path ) && ! wp_is_writable( $path ) ) || ( ! is_dir( $path ) && ! wp_is_writable( dirname( $path ) ) ) ) && $path !== filetrip_bkp_path_default() )
		$path = filetrip_bkp_path_default();

	// Create the backups directory if it doesn't exist
	if ( ! is_dir( $path ) && is_writable( dirname( $path ) ) )
		wp_mkdir_p( $path );

	// If the path has changed then cache it
	if ( get_option( 'filetrip_bkp_path' ) !== $path )
		update_option( 'filetrip_bkp_path', $path );

	// Protect against directory browsing by including a index.html file
	$index = $path . '/index.html';

	if ( ! file_exists( $index ) && wp_is_writable( $path ) )
		file_put_contents( $index, '' );

	$htaccess = $path . '/.htaccess';

	// Protect the directory with a .htaccess file on Apache servers
	if ( defined('FILETRIP_SECURE_KEY') && $is_apache && function_exists( 'insert_with_markers' ) && ! file_exists( $htaccess ) && wp_is_writable( $path ) ) {

		$contents[] = '# ' . sprintf( __( 'This %s file ensures that other people cannot download your backup files.', 'filetrip-plugin' ), '.htaccess' );
		$contents[] = '';
		$contents[] = '<IfModule mod_rewrite.c>';
		$contents[] = 'RewriteEngine On';
		$contents[] = 'RewriteCond %{QUERY_STRING} !key=' . FILETRIP_SECURE_KEY;
		$contents[] = 'RewriteRule (.*) - [F]';
		$contents[] = '</IfModule>';
		$contents[] = '';

		insert_with_markers( $htaccess, 'BackUpWordPress', $contents );

	}

	return FILETRIP_Backup::conform_dir( $path );

}

/**
 * Return the default backup path
 *
 * @return string path
 */
function filetrip_bkp_path_default() {

	$path = untrailingslashit( get_option( 'filetrip_bkp_default_path' ) );

	$content_dir = FILETRIP_Backup::conform_dir( trailingslashit( WP_CONTENT_DIR ) );

	$pos = strpos( $path, $content_dir );

	// no path set or current path doesn't match the database value
	if ( empty( $path ) || ( false === $pos ) || ( 0 !== $pos ) ) {

		$path = FILETRIP_Backup::conform_dir( trailingslashit( WP_CONTENT_DIR ) . 'filetrip-' . substr( FILETRIP_SECURE_KEY, 0, 10 ) . '-backups' );

		update_option( 'filetrip_bkp_default_path', $path );

	}

	$upload_dir = wp_upload_dir();

	// If the backups dir can't be created in WP_CONTENT_DIR then fallback to uploads
	if ( ( ( ! is_dir( $path ) && ! wp_is_writable( dirname( $path ) ) ) || ( is_dir( $path ) && ! wp_is_writable( $path ) ) ) && false === strpos( $path, $upload_dir['basedir'] ) ) {

		filetrip_bkp_path_move( $path, $path = FILETRIP_Backup::conform_dir( trailingslashit( $upload_dir['basedir'] ) . 'filetrip-' . substr( FILETRIP_SECURE_KEY, 0, 10 ) . '-backups' ) );

		update_option( 'filetrip_bkp_default_path', $path );

	}

	return $path;

}

/**
 * Move the backup directory and all existing backup files to a new
 * location
 *
 * @param string $from path to move the backups dir from
 * @param string $to   path to move the backups dir to
 * @return void
 */
function filetrip_bkp_path_move( $from, $to ) {

	if ( ! trim( untrailingslashit( trim( $from ) ) ) || ! trim( untrailingslashit( trim( $to ) ) ) )
		return;

	// Create the new directory if it doesn't exist
	if ( is_writable( dirname( $to ) ) && ! is_dir( $to ) )
		wp_mkdir_p( $to );

	// Bail if we couldn't
	if ( ! is_dir( $to ) || ! wp_is_writable( $to ) )
		return false;

	update_option( 'filetrip_bkp_path', $to );

	// Bail if the old directory doesn't exist
	if ( ! is_dir( $from ) )
		return false;

	// Cleanup before we start moving things
	filetrip_bkp_cleanup();

	// Move any existing backups
	if ( $handle = opendir( $from ) ) {

		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( 'zip' === pathinfo( $file, PATHINFO_EXTENSION ) )
				if ( ! @rename( trailingslashit( $from ) . $file, trailingslashit( $to ) . $file ) )
					copy( trailingslashit( $from ) . $file, trailingslashit( $to ) . $file );
		}

		closedir( $handle );

	}

	// Only delete the old directory if it's inside WP_CONTENT_DIR
	if ( false !==strpos( $from, WP_CONTENT_DIR ) )
		filetrip_bkp_rmdirtree( $from );

}

/**
 * Check if a backup is possible with regards to file
 * permissions etc.
 *
 * @return bool
 */
function filetrip_bkp_possible() {

	if ( ! wp_is_writable( filetrip_bkp_path() ) || ! is_dir( filetrip_bkp_path() ) ) {
		return false;
	}

	$test_backup = new FILETRIP_BKP_Scheduled_Backup( 'test_backup' );

	if ( ! is_readable( $test_backup->get_root() ) )
		return false;

	return true;
}

/**
 * Remove any non backup.zip files from the backups dir.
 *
 * @return void
 */
function filetrip_bkp_cleanup() {

	if ( defined( 'FILETRIP_BKP_PATH' ) && FILETRIP_BKP_PATH )
		return;

	$filetrip_bkp_path = filetrip_bkp_path();

	if ( ! is_dir( $filetrip_bkp_path ) )
		return;

	if ( $handle = opendir( $filetrip_bkp_path ) ) {

		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( ! in_array( $file, array( '.', '..', 'index.html' ) ) && 'zip' !== pathinfo( $file, PATHINFO_EXTENSION ) && false === strpos( $file, '-running' ) )
				filetrip_bkp_rmdirtree( trailingslashit( $filetrip_bkp_path ) . $file );
		}

		closedir( $handle );

	}

}

/**
 * Handles changes in the defined Constants
 * that users can define to control advanced
 * settings
 */
function filetrip_bkp_constant_changes() {

	// If a custom backup path has been set or changed
	if ( defined( 'FILETRIP_BKP_PATH' ) && FILETRIP_BKP_PATH && FILETRIP_Backup::conform_dir( FILETRIP_BKP_PATH ) !== ( $from = FILETRIP_Backup::conform_dir( get_option( 'filetrip_bkp_path' ) ) ) )
		filetrip_bkp_path_move( $from, FILETRIP_BKP_PATH );

	// If a custom backup path has been removed
	if ( ( ( defined( 'FILETRIP_BKP_PATH' ) && ! FILETRIP_BKP_PATH ) || ! defined( 'FILETRIP_BKP_PATH' ) && filetrip_bkp_path_default() !== ( $from = FILETRIP_Backup::conform_dir( get_option( 'filetrip_bkp_path' ) ) ) ) )
		filetrip_bkp_path_move( $from, filetrip_bkp_path_default() );

	// If the custom path has changed and the new directory isn't writable
	if ( defined( 'FILETRIP_BKP_PATH' ) && FILETRIP_BKP_PATH && ! wp_is_writable( FILETRIP_BKP_PATH ) && get_option( 'filetrip_bkp_path' ) === FILETRIP_BKP_PATH && is_dir( FILETRIP_BKP_PATH ) )
		filetrip_bkp_path_move( FILETRIP_BKP_PATH, filetrip_bkp_path_default() );

}

/**
 * Get the max email attachment filesize
 *
 * Can be overridden by defining FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE
 *
 * return int the filesize
 */
function filetrip_bkp_get_max_attachment_size() {

	$max_size = '10mb';

	if ( defined( 'FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE' ) && wp_convert_hr_to_bytes( FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE ) )
		$max_size = FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE;

	return wp_convert_hr_to_bytes( $max_size );

}

function filetrip_bkp_is_path_accessible( $dir ) {

	// Path is inaccessible
	if ( strpos( $dir, FILETRIP_Backup::get_home_path() ) === false ) {
		return false;
	}

	return true;
}

/**
 * List of schedules
 *
 * @return array
 */
function filetrip_bkp_get_cron_schedules() {

	$schedules = wp_get_schedules();

	// remove any schedule whose key is not prefixed with 'filetrip_bkp_'
	foreach ( $schedules as $key => $arr ) {
		if ( ! preg_match( '/^filetrip_bkp_/', $key ) )
			unset( $schedules[$key] );
	}

	return $schedules;
}

/**
 * @param string $type the type of the schedule
 * @param array $times {
 *     An array of time arguments. Optional.
 *
 *     @type int $minutes          The minute to start the schedule on. Defaults to current time + 10 minutes. Accepts
 *                                 any valid `date( 'i' )` output.
 *     @type int $hours            The hour to start the schedule on. Defaults to current time + 10 minutes. Accepts
 *                                 any valid `date( 'G' )` output.
 *     @type string $day_of_week   The day of the week to start the schedule on. Defaults to current time + 10 minutes. Accepts
 *                                 any valid `date( 'l' )` output.
 *     @type int $day_of_month     The day of the month to start the schedule on. Defaults to current time + 10 minutes. Accepts
 *                                 any valid `date( 'j' )` output.
 *     @type int $now              The current time. Defaults to `time()`. Accepts any valid timestamp.
 *
 * }
 * @return int $timestamp Returns the resulting timestamp on success and Int 0 on failure
 */
function filetrip_bkp_determine_start_time( $type, $times = array() ) {

	// Default to in 10 minutes
	if ( ! empty( $times['now'] ) ) {
		$default_timestamp = $times['now'] + 600;

	} else {
		$default_timestamp = time() + 600;
	}

	$default_times = array(
		'minutes'      => date( 'i', $default_timestamp ),
		'hours'        => date( 'G', $default_timestamp ),
		'day_of_week'  => date( 'l', $default_timestamp ),
		'day_of_month' => date( 'j', $default_timestamp ),
		'now'          => time()
	);

	$args = wp_parse_args( $times, $default_times );

	$schedule_start = '';

	$intervals = FILETRIP_BKP_Scheduled_Backup::get_cron_schedules();

	// Allow the hours and minutes to be overwritten by a constant
	if ( defined( 'FILETRIP_BKP_SCHEDULE_TIME' ) && FILETRIP_BKP_SCHEDULE_TIME ) {
		$hm = FILETRIP_BKP_SCHEDULE_TIME;
	}

	// The hour and minute that the schedule should start on
	else {
		$hm = $args['hours'] . ':' . $args['minutes'] . ':00';
	}

	switch ( $type ) {

		case 'filetrip_bkp_hourly' :
		case 'filetrip_bkp_daily' :
		case 'filetrip_bkp_twicedaily':

			// The next occurance of the specified time
			$schedule_start = $hm;
			break;

		case 'filetrip_bkp_weekly' :
		case 'filetrip_bkp_fortnightly' :

			// The next day of the week at the specified time
			$schedule_start = $args['day_of_week'] . ' ' . $hm;
			break;

		case 'filetrip_bkp_monthly' :

			// The occurance of the time on the specified day of the month
			$schedule_start = date( 'F', $args['now'] ) . ' ' . $args['day_of_month'] . ' ' . $hm;

			// If we've already gone past that day this month then we'll need to start next month
			if ( strtotime( $schedule_start, $args['now'] ) <= $args['now'] )
				$schedule_start = date( 'F', strtotime( '+ 1 month', $args['now'] ) )  . ' ' . $args['day_of_month'] . ' ' . $hm;

			// If that's still in the past then we'll need to jump to next year
			if ( strtotime( $schedule_start, $args['now'] ) <= $args['now'] )
				$schedule_start = date( 'F', strtotime( '+ 1 month', $args['now'] ) )  . ' ' . $args['day_of_month'] . ' ' . date( 'Y', strtotime( '+ 1 year', $args['now'] ) ) . ' ' . $hm;

			break;
		default :

			return 0;

			break;

	}

	$timestamp = strtotime( $schedule_start, $args['now'] );

	// Convert to UTC
	$timestamp -= get_option( 'gmt_offset' ) * 3600;

	// If the scheduled time already passed then keep adding the interval until we get to a future date
	while ( $timestamp <= $args['now'] ) {
		$timestamp += $intervals[ $type ]['interval'];
	}

	return $timestamp;

}

/**
 * Helper function for creating safe action URLs.
 *
 * @param string $action Callback function name.
 * @param array $query_args Additional GET params.
 *
 * @return string
 */
function filetrip_bkp_admin_action_url( $action, array $query_args = array() ) {

	$query_args = array_merge( $query_args, array( 'action' => 'filetrip_bkp_' . $action ) );

	return esc_url( wp_nonce_url( add_query_arg( $query_args, admin_url( 'admin-post.php' ) ), 'filetrip_bkp_' . $action, 'filetrip_bkp-' . $action . '_nonce' ) );
}

class mimetype { 
   function getType($filename) { 
      // get base name of the filename provided by user 
      $filename = basename($filename); 

      // break file into parts seperated by . 
      $filename = explode('.', $filename); 

      // take the last part of the file to get the file extension 
      $filename = $filename[count($filename)-1];    

      // find mime type 
      return $this->privFindType($filename); 
   } 

   function privFindType($ext) { 
      // create mimetypes array 
      $mimetypes = $this->privBuildMimeArray(); 
       
      // return mime type for extension 
      if (isset($mimetypes[$ext])) { 
         return $mimetypes[$ext]; 
      // if the extension wasn't found return octet-stream          
      } else { 
         return 'application/octet-stream'; 
      } 
          
   } 

   function privBuildMimeArray() { 
      return array( 
         "ez" => "application/andrew-inset", 
         "hqx" => "application/mac-binhex40", 
         "cpt" => "application/mac-compactpro", 
         "doc" => "application/msword", 
         "bin" => "application/octet-stream", 
         "dms" => "application/octet-stream", 
         "lha" => "application/octet-stream", 
         "lzh" => "application/octet-stream", 
         "exe" => "application/octet-stream", 
         "class" => "application/octet-stream", 
         "so" => "application/octet-stream", 
         "dll" => "application/octet-stream", 
         "oda" => "application/oda", 
         "pdf" => "application/pdf", 
         "ai" => "application/postscript", 
         "eps" => "application/postscript", 
         "ps" => "application/postscript", 
         "smi" => "application/smil", 
         "smil" => "application/smil", 
         "wbxml" => "application/vnd.wap.wbxml", 
         "wmlc" => "application/vnd.wap.wmlc", 
         "wmlsc" => "application/vnd.wap.wmlscriptc", 
         "bcpio" => "application/x-bcpio", 
         "vcd" => "application/x-cdlink", 
         "pgn" => "application/x-chess-pgn", 
         "cpio" => "application/x-cpio", 
         "csh" => "application/x-csh", 
         "dcr" => "application/x-director", 
         "dir" => "application/x-director", 
         "dxr" => "application/x-director", 
         "dvi" => "application/x-dvi", 
         "spl" => "application/x-futuresplash", 
         "gtar" => "application/x-gtar", 
         "hdf" => "application/x-hdf", 
         "js" => "application/x-javascript", 
         "skp" => "application/x-koan", 
         "skd" => "application/x-koan", 
         "skt" => "application/x-koan", 
         "skm" => "application/x-koan", 
         "latex" => "application/x-latex", 
         "nc" => "application/x-netcdf", 
         "cdf" => "application/x-netcdf", 
         "sh" => "application/x-sh", 
         "shar" => "application/x-shar", 
         "swf" => "application/x-shockwave-flash", 
         "sit" => "application/x-stuffit", 
         "sv4cpio" => "application/x-sv4cpio", 
         "sv4crc" => "application/x-sv4crc", 
         "tar" => "application/x-tar", 
         "tcl" => "application/x-tcl", 
         "tex" => "application/x-tex", 
         "texinfo" => "application/x-texinfo", 
         "texi" => "application/x-texinfo", 
         "t" => "application/x-troff", 
         "tr" => "application/x-troff", 
         "roff" => "application/x-troff", 
         "man" => "application/x-troff-man", 
         "me" => "application/x-troff-me", 
         "ms" => "application/x-troff-ms", 
         "ustar" => "application/x-ustar", 
         "src" => "application/x-wais-source", 
         "xhtml" => "application/xhtml+xml", 
         "xht" => "application/xhtml+xml", 
         "zip" => "application/zip", 
         "au" => "audio/basic", 
         "snd" => "audio/basic", 
         "mid" => "audio/midi", 
         "midi" => "audio/midi", 
         "kar" => "audio/midi", 
         "mpga" => "audio/mpeg", 
         "mp2" => "audio/mpeg", 
         "mp3" => "audio/mpeg", 
         "aif" => "audio/x-aiff", 
         "aiff" => "audio/x-aiff", 
         "aifc" => "audio/x-aiff", 
         "m3u" => "audio/x-mpegurl", 
         "ram" => "audio/x-pn-realaudio", 
         "rm" => "audio/x-pn-realaudio", 
         "rpm" => "audio/x-pn-realaudio-plugin", 
         "ra" => "audio/x-realaudio", 
         "wav" => "audio/x-wav", 
         "pdb" => "chemical/x-pdb", 
         "xyz" => "chemical/x-xyz", 
         "bmp" => "image/bmp", 
         "gif" => "image/gif", 
         "ief" => "image/ief", 
         "jpeg" => "image/jpeg", 
         "jpg" => "image/jpeg", 
         "jpe" => "image/jpeg", 
         "png" => "image/png", 
         "tiff" => "image/tiff", 
         "tif" => "image/tif", 
         "djvu" => "image/vnd.djvu", 
         "djv" => "image/vnd.djvu", 
         "wbmp" => "image/vnd.wap.wbmp", 
         "ras" => "image/x-cmu-raster", 
         "pnm" => "image/x-portable-anymap", 
         "pbm" => "image/x-portable-bitmap", 
         "pgm" => "image/x-portable-graymap", 
         "ppm" => "image/x-portable-pixmap", 
         "rgb" => "image/x-rgb", 
         "xbm" => "image/x-xbitmap", 
         "xpm" => "image/x-xpixmap", 
         "xwd" => "image/x-windowdump", 
         "igs" => "model/iges", 
         "iges" => "model/iges", 
         "msh" => "model/mesh", 
         "mesh" => "model/mesh", 
         "silo" => "model/mesh", 
         "wrl" => "model/vrml", 
         "vrml" => "model/vrml", 
         "css" => "text/css", 
         "html" => "text/html", 
         "htm" => "text/html", 
         "asc" => "text/plain", 
         "txt" => "text/plain", 
         "rtx" => "text/richtext", 
         "rtf" => "text/rtf", 
         "sgml" => "text/sgml", 
         "sgm" => "text/sgml", 
         "tsv" => "text/tab-seperated-values", 
         "wml" => "text/vnd.wap.wml", 
         "wmls" => "text/vnd.wap.wmlscript", 
         "etx" => "text/x-setext", 
         "xml" => "text/xml", 
         "xsl" => "text/xml", 
         "mpeg" => "video/mpeg", 
         "mpg" => "video/mpeg", 
         "mpe" => "video/mpeg", 
         "qt" => "video/quicktime", 
         "mov" => "video/quicktime", 
         "mxu" => "video/vnd.mpegurl", 
         "avi" => "video/x-msvideo", 
         "movie" => "video/x-sgi-movie", 
         "ice" => "x-conference-xcooltalk" 
      ); 
   } 
}

// Cron hook
  /**
* Function to run when the schedule cron fires
* @param $schedule_id
*/
function filetrip_schedule_hook_run( $schedule_id)
{
  require_once ABSPATH . 'wp-load.php';
  
  try{
    $arfaly = new Filetrip_Uploader;
    $arfaly->arfaly_init();
    
    FILETRIP_BKP_Schedules::get_instance()->refresh_schedules();
    $schedules = FILETRIP_BKP_Schedules::get_instance();
    $schedule  = $schedules->get_schedule( $schedule_id );

    if(empty($schedule))
      return;
    
    $schedule->run();
    
    $backup_files = $schedule->get_backups();

    $arfaly->_notify_admin($schedule);
    
    // Forward backup files to (Dropbox & Google Drive)
    if(!empty($backup_files)){
      $arfaly->send_backup_to_all_channels(array_shift($backup_files));

    }
  }catch(Exception $exp)
  {
    error_log('Error been caught'.print_r($exp, true)); 
  }
  return;
}

add_action( 'filetrip_bkp_schedule_hook', 'filetrip_schedule_hook_run' );