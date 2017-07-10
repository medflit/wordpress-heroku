<?php
/*
Plugin Name: ItechFlare Core
Plugin URI: https://itechflare.com
Description: iTechFlare Core Plugin
Version: 1.0.0
Author: itechflare
Author URI: https://itechflare.com
License: GPLv2 or later
Text Domain: itf_wp
*/

require_once __DIR__ . '/Libs/CoreLoader.php';

/**
 * Example Injectable Direct
 * this is available only use as plugin
 * on theme it will must be check as direct execute
 * Please check existences of `itf_wp_plugin_extension_core`
 * all of extension will be available as pre render after flare core init
 */
