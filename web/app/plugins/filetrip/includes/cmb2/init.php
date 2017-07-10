<?php
/**
 * Plugin Name:  FILETRIP_CMB2 (beta)
 * Plugin URI:   https://github.com/WebDevStudios/FILETRIP_CMB2
 * Description:  FILETRIP_CMB2 will create metaboxes and forms with custom fields that will blow your mind.
 * Author:       WebDevStudios
 * Author URI:   http://webdevstudios.com
 * Contributors: WebDevStudios (@webdevstudios / webdevstudios.com)
 *               Justin Sternberg (@jtsternberg / dsgnwrks.pro)
 *               Jared Atchison (@jaredatch / jaredatchison.com)
 *               Bill Erickson (@billerickson / billerickson.net)
 *               Andrew Norcross (@norcross / andrewnorcross.com)
 *
 * Version:      2.0.0.6
 *
 * Text Domain:  cmb2
 * Domain Path:  languages
 *
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/************************************************************************
                  You should not edit the code below
                  (or any code in the included files)
                  or things might explode!
*************************************************************************/
if ( ! class_exists( 'filetrip_cmb_bootstrap_200beta', false ) ) {
	/**
	 * Check for newest version of FILETRIP_CMB
	 */
	class filetrip_cmb_bootstrap_200beta {

		/**
		 * Current version number
		 * @var   string
		 * @since 1.0.0
		 */
		const VERSION = '2.0.0.6';

		/**
		 * Current version hook priority
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 2.0.0
		 */
		const PRIORITY = 9999;

		public static $single = null;

		public static function go() {
			if ( null === self::$single ) {
				self::$single = new self();
			}
			return self::$single;
		}

		private function __construct() {
			add_action( 'init', array( $this, 'include_cmb' ), self::PRIORITY );
		}

		public function include_cmb() {
			if ( ! class_exists( 'FILETRIP_CMB2', false ) ) {
				if ( ! defined( 'FILETRIP_CMB2_VERSION' ) ) {
					define( 'FILETRIP_CMB2_VERSION', self::VERSION );
				}
				$this->l10ni18n();
				require_once 'FILETRIP_CMB2.php';
			}
		}

		/**
		 * Load FILETRIP_CMB text domain
		 * @since  2.0.0
		 */
		public function l10ni18n() {
			$loaded = load_plugin_textdomain( 'cmb2', false, '/languages/' );
			if ( ! $loaded ) {
				$loaded = load_muplugin_textdomain( 'cmb2', '/languages/' );
			}
			if ( ! $loaded ) {
				$loaded = load_theme_textdomain( 'cmb2', '/languages/' );
			}

			if ( ! $loaded ) {
				$locale = apply_filters( 'plugin_locale', get_locale(), 'cmb2' );
				$mofile = dirname( __FILE__ ) . '/languages/cmb2-'. $locale .'.mo';
				load_textdomain( 'cmb2', $mofile );
			}
		}

	}
	filetrip_cmb_bootstrap_200beta::go();

} // class exists check
