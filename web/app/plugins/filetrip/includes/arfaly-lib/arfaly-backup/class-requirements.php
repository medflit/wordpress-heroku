<?php

/**
 * A singleton to handle the registering, unregistering
 * and storage of individual requirements
 */
class FILETRIP_BKP_Requirements {

	/**
	 * The array of requirements
	 *
	 * Should be of the format array( (string) group => __CLASS__ );
	 * @var array
	 */
	private static $requirements = array();


	/**
	 * Get the array of registered requirements
	 *
	 * @param bool $group
	 * @return array
	 */
	public static function get_requirements( $group = false ) {

		$requirements = $group ? self::$requirements[ $group ] : self::$requirements;

		ksort( $requirements );

		return array_map( array( 'self', 'instantiate' ), $requirements );

	}

	/**
	 * Get the requirement groups
	 *
	 * @return array
	 */
	public static function get_requirement_groups() {
		return array_keys( self::$requirements );
	}

	/**
	 * Register a new requirement
	 *
	 * @param        $class
	 * @param string $group
	 * @return WP_Error
	 */
	public static function register( $class, $group = 'misc' ) {

		if ( ! class_exists( $class ) ) {
			return new WP_Error( 'invalid argument', 'Argument 1 for ' . __METHOD__ . ' must be a valid class' );
		}

		self::$requirements[$group][] = $class;

	}

	/**
	 * Instantiate the individual requirement classes
	 *
	 * @access private
	 * @param string $class
	 * @return array An array of instantiated classes
	 */
	private static function instantiate( $class ) {

		if ( ! class_exists( $class ) ) {
			return new WP_Error( 'invalid argument', 'Argument 1 for ' . __METHOD__ . ' must be a valid class' );
		}

		$$class = new $class;

		return $$class;

	}

}

/**
 * An abstract requirement class, individual requirements should
 * extend this class
 */
abstract class FILETRIP_BKP_Requirement {

	/**
	 * @return mixed
	 */
	abstract protected function test();

	/**
	 * @return mixed
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * @return mixed|string
	 */
	public function result() {

		$test = $this->test();

		if ( is_string( $test ) && $test )
			return $test;

		if ( is_bool( $test ) || empty( $test ) ) {

			if ( $test ) {
				return 'Yes';
			}

			return 'No';

		}

		return var_export( $test, true );

	}

	public function raw_result() {
		return $this->test();
	}

}

/**
 * Class FILETRIP_BKP_Requirement_Zip_Archive
 */
class FILETRIP_BKP_Requirement_Zip_Archive extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'ZipArchive';

	/**
	 * @return bool
	 */
	protected function test() {

		if ( class_exists( 'ZipArchive' ) ) {
			return true;
		}

		return false;

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Zip_Archive', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Directory_Iterator_Follow_Symlinks
 *
 * Tests whether the FOLLOW_SYMLINKS class constant is available on Directory Iterator
 */
class FILETRIP_BKP_Requirement_Directory_Iterator_Follow_Symlinks extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'DirectoryIterator FOLLOW_SYMLINKS';

	/**
	 * @return bool
	 */
	protected function test() {

		if ( defined( 'RecursiveDirectoryIterator::FOLLOW_SYMLINKS' ) ) {
			return true;
		}

		return false;

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Directory_Iterator_Follow_Symlinks', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Zip_Command
 *
 * Tests whether the zip command is available and if it is what path it's available at
 */
class FILETRIP_BKP_Requirement_Zip_Command_Path extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'zip command';

	/**
	 * @return string
	 */
	protected function test() {

		$hm_backup = new FILETRIP_Backup;

		return $hm_backup->get_zip_command_path();

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Zip_Command_Path', 'Server' );

/**
 * Class FILETRIP_BKP_Requirement_Mysqldump_Command
 *
 * Tests whether the zip command is available and if it is what path it's available at
 */
class FILETRIP_BKP_Requirement_Mysqldump_Command_Path extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'mysqldump command';

	/**
	 * @return string
	 */
	protected function test() {

		$hm_backup = new FILETRIP_Backup;

		return $hm_backup->get_mysqldump_command_path();

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Mysqldump_Command_Path', 'Server' );

/**
 * Class FILETRIP_BKP_Requirement_PHP_User
 */
class FILETRIP_BKP_Requirement_PHP_User extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'User';

	/**
	 * @return string
	 */
	protected function test() {

		if ( ! FILETRIP_Backup::is_shell_exec_available() ) {
			return '';
		}

		return shell_exec( 'whoami' );

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_User', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_PHP_Group
 */
class FILETRIP_BKP_Requirement_PHP_Group extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Group[s]';

	/**
	 * @return string
	 */
	protected function test() {

		if ( ! FILETRIP_Backup::is_shell_exec_available() ) {
			return '';
		}

		return shell_exec( 'groups' );

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_Group', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_PHP_Version
 */
class FILETRIP_BKP_Requirement_PHP_Version extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Version';

	/**
	 * @return string
	 */
	protected function test() {
		return PHP_VERSION;
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_Version', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Cron_Array
 */
class FILETRIP_BKP_Requirement_Cron_Array extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Cron Array';

	/**
	 * @return bool|mixed
	 */
	protected function test() {

		$cron = get_option( 'cron' );

		if ( ! $cron ) {
			return false;
		}

		return $cron;

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Cron_Array', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_Cron_Array
 */
class FILETRIP_BKP_Requirement_Language extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Language';

	/**
	 * @return bool|mixed
	 */
	protected function test() {

		// Since 4.0
		$language = get_option( 'WPLANG' );

		if ( $language ) {
			return $language;
		}

		if ( defined( 'WPLANG' ) && WPLANG ) {
			return WPLANG;
		}

		return 'en_US';

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Language', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_Safe_Mode
 */
class FILETRIP_BKP_Requirement_Safe_Mode extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Safe Mode';

	/**
	 * @return bool
	 */
	protected function test() {
		return FILETRIP_Backup::is_safe_mode_active();
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Safe_Mode', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Shell_Exec
 */
class FILETRIP_BKP_Requirement_Shell_Exec extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Shell Exec';

	/**
	 * @return bool
	 */
	protected function test() {
		return FILETRIP_Backup::is_shell_exec_available();
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Shell_Exec', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Memory_Limit
 */
class FILETRIP_BKP_Requirement_PHP_Memory_Limit extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Memory Limit';

	/**
	 * @return string
	 */
	protected function test() {
		return @ini_get( 'memory_limit' );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_Memory_Limit', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Backup_Path
 */
class FILETRIP_BKP_Requirement_Backup_Path extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Backup Path';

	/**
	 * @return string
	 */
	protected function test() {
		return filetrip_bkp_path();
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Backup_Path', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_Backup_Path_Permissions
 */
class FILETRIP_BKP_Requirement_Backup_Path_Permissions extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Backup Path Permissions';

	/**
	 * @return string
	 */
	protected function test() {
		return substr( sprintf( '%o', fileperms( filetrip_bkp_path() ) ), - 4 );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Backup_Path_Permissions', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_WP_CONTENT_DIR
 */
class FILETRIP_BKP_Requirement_WP_CONTENT_DIR extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'WP_CONTENT_DIR';

	/**
	 * @return string
	 */
	protected function test() {
		return WP_CONTENT_DIR;
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_WP_CONTENT_DIR', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_WP_CONTENT_DIR_Permissions
 */
class FILETRIP_BKP_Requirement_WP_CONTENT_DIR_Permissions extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'WP_CONTENT_DIR Permissions';

	/**
	 * @return string
	 */
	protected function test() {
		return substr( sprintf( '%o', fileperms( WP_CONTENT_DIR ) ), - 4 );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_WP_CONTENT_DIR_Permissions', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_ABSPATH
 */
class FILETRIP_BKP_Requirement_ABSPATH extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'ABSPATH';

	/**
	 * @return string
	 */
	protected function test() {
		return ABSPATH;
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_ABSPATH', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_Backup_Root_Path
 */
class FILETRIP_BKP_Requirement_Backup_Root_Path extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Backup Root Path';

	/**
	 * @return string
	 */
	protected function test() {

		$hm_backup = new FILETRIP_Backup();

		return $hm_backup->get_root();

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Backup_Root_Path', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_Calculated_Size
 */
class FILETRIP_BKP_Requirement_Calculated_Size extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Calculated size of site';

	/**
	 * @return string
	 */
	protected function test() {

		$backup_sizes = array();

		$schedules = FILETRIP_BKP_Schedules::get_instance();

		foreach ( $schedules->get_schedules() as $schedule ) {
			if ( $schedule->is_site_size_cached() ) {
				$backup_sizes[ $schedule->get_id() ] = $schedule->get_formatted_site_size();
			}
		}

		return $backup_sizes;

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Calculated_Size', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_WP_Cron_Test_Response
 */
class FILETRIP_BKP_Requirement_WP_Cron_Test extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'WP Cron Test Failed';

	/**
	 * @return mixed
	 */
	protected function test() {
		return (bool) get_option( 'filetrip_bkp_wp_cron_test_failed' );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_WP_Cron_Test', 'Site' );

/**
 * Class FILETRIP_BKP_Requirement_PHP_API
 */
class FILETRIP_BKP_Requirement_PHP_API extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Interface';

	/**
	 * @return string
	 */
	protected function test() {
		return php_sapi_name();
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_API', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_Server_Software
 */
class FILETRIP_BKP_Requirement_Server_Software extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Server';

	/**
	 * @return bool
	 */
	protected function test() {

		if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) )
			return $_SERVER['SERVER_SOFTWARE'];

		return false;

	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Server_Software', 'Server' );

/**
 * Class FILETRIP_BKP_Requirement_Server_OS
 */
class FILETRIP_BKP_Requirement_Server_OS extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'OS';

	/**
	 * @return string
	 */
	protected function test() {
		return PHP_OS;
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Server_OS', 'Server' );

/**
 * Class FILETRIP_BKP_Requirement_PHP_Disable_Functions
 */
class FILETRIP_BKP_Requirement_PHP_Disable_Functions extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'Disabled Functions';

	/**
	 * @return string
	 */
	protected function test() {
		return @ini_get( 'disable_functions' );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_Disable_Functions', 'PHP' );

/**
 * Class FILETRIP_BKP_Requirement_PHP_Open_Basedir
 */
class FILETRIP_BKP_Requirement_PHP_Open_Basedir extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'open_basedir';

	/**
	 * @return string
	 */
	protected function test() {
		return @ini_get( 'open_basedir' );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_PHP_Open_Basedir', 'PHP' );

/* CONSTANTS */

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_PATH
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_PATH extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_PATH';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_PATH' ) ? FILETRIP_BKP_PATH : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_PATH', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ROOT
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ROOT extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_ROOT';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_ROOT' ) ? FILETRIP_BKP_ROOT : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ROOT', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_MYSQLDUMP_PATH
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_MYSQLDUMP_PATH extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_MYSQLDUMP_PATH';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_MYSQLDUMP_PATH' ) ? FILETRIP_BKP_MYSQLDUMP_PATH : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_MYSQLDUMP_PATH', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ZIP_PATH
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ZIP_PATH extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_ZIP_PATH';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_ZIP_PATH' ) ? FILETRIP_BKP_ZIP_PATH : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ZIP_PATH', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_CAPABILITY
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_CAPABILITY extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_CAPABILITY';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_CAPABILITY' ) ? FILETRIP_BKP_CAPABILITY : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_CAPABILITY', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_EMAIL
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_EMAIL extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_EMAIL';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_EMAIL' ) ? FILETRIP_BKP_EMAIL : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_EMAIL', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE' ) ? FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_ATTACHMENT_MAX_FILESIZE', 'constants' );

/**
 * Class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_EXCLUDE
 */
class FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_EXCLUDE extends FILETRIP_BKP_Requirement {

	/**
	 * @var string
	 */
	var $name = 'FILETRIP_BKP_EXCLUDE';

	/**
	 * @return string
	 */
	protected function test() {
		return defined( 'FILETRIP_BKP_EXCLUDE' ) ? FILETRIP_BKP_EXCLUDE : '';
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Define_FILETRIP_BKP_EXCLUDE', 'constants' );

class FILETRIP_BKP_Requirement_Active_Plugins extends FILETRIP_BKP_Requirement {

	var $name = 'Active Plugins';

	protected function test(){
		return get_option( 'active_plugins' );
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Active_Plugins', 'Site' );

class FILETRIP_BKP_Requirement_Home_Url extends FILETRIP_BKP_Requirement {

	var $name = 'Home URL';

	protected function test(){
		return home_url();
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Home_Url', 'Site' );

class FILETRIP_BKP_Requirement_Site_Url extends FILETRIP_BKP_Requirement {

	var $name = 'Site URL';

	protected function test() {
		return site_url();
	}

}

FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Site_Url', 'Site' );

class FILETRIP_BKP_Requirement_Plugin_Version extends FILETRIP_BKP_Requirement {
	var $name = 'Plugin Version';

	protected function test() {
		return FILETRIP_BKP_VERSION;
	}
}
FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Plugin_Version', 'constants' );

class FILETRIP_BKP_Requirement_Max_Exec extends FILETRIP_BKP_Requirement {

	var $name = 'Max execution time';

	protected function test(){
		return @ini_get( 'max_execution_time' );
	}
}
FILETRIP_BKP_Requirements::register( 'FILETRIP_BKP_Requirement_Max_Exec', 'PHP' );
