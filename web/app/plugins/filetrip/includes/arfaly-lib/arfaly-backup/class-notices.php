<?php

/**
 * Class FILETRIP_BKP_Notices
 */
class FILETRIP_BKP_Notices {

	/**
	 * @var
	 */
	private static $_instance;

	/**
	 *
	 */
	private function __construct() {}

	/**
	 * @return FILETRIP_BKP_Notices
	 */
	public static function get_instance() {

		if ( ! ( self::$_instance instanceof FILETRIP_BKP_Notices ) ) {
			self::$_instance = new FILETRIP_BKP_Notices();
		}
		return self::$_instance;
	}

	/**
	 * @param string $context
	 * @param array $messages
	 *
	 * @return mixed|void
	 */
	public function set_notices( $context, array $messages ) {

		$all_notices = get_option( 'filetrip_bkp_notices' );

		$all_notices[ $context ] = $messages;

		update_option( 'filetrip_bkp_notices', $all_notices );

		return get_option( 'filetrip_bkp_notices' );
	}

	/**
	 * Fetch the notices for the context.
	 * All notices by default.
	 *
	 * @param string $context
	 *
	 * @return array|mixed|void
	 */
	public function get_notices( $context = '' ) {

		if ( $all_notices = get_option( 'filetrip_bkp_notices' ) ) {

			if ( 0 < trim( strlen( $context ) ) ) {
				return $all_notices[ $context ];
			}

			return $all_notices;
		}

		return array();

	}

	/**
	 * Delete all notices from the DB.
	 */
	public function clear_all_notices() {
		return delete_option( 'filetrip_bkp_notices' );
	}
}
