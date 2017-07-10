<?php

namespace iTechFlare\WP\iTechFlareExtension\SupportCenter\Patcher;

class Silphium_Patcher {

	/**
	 * The one, true instance.
	 */
	public static $instance = null;

	/**
	 * The class constructor.
	 * This is a singleton class so please use the ::get_instance() method instead.
	 */
	private function __construct() {

		if ( is_admin() ) {
			new Silphium_Patcher_Apply_Patch();
			new Silphium_Patcher_Admin_Screen();
		}

	}

	/**
	 * Get the one true instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new silphium_Patcher();
		}
		return self::$instance;
	}

}
