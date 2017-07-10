<?php
/**
 * Core Functions
 */
if (!function_exists('itf_filetrip_wp_plugin_extension_core')) :

	/**
	 * @return \iTechFlare\WP\Plugin\FileTrip\iTechFlareCore
	 */
	function itf_filetrip_wp_plugin_extension_core()
	{
		static $itf_wp;
		if (!$itf_wp) {
			/**
			 * \iTechFlare\WP\Plugin\FileTrip\iTechFlareCore
			 */
			$itf_wp = \iTechFlare\WP\Plugin\FileTrip\iTechFlareCore::getInstance();
			// doing init
			do_action('itf_filetrip_admin_loaded_extension', $itf_wp);
		}
		return $itf_wp;
	}
endif;

/**
 * Plugins Loaded Load
 * Load The Core
 */
add_action('plugins_loaded', 'itf_filetrip_wp_plugin_extension_core');
