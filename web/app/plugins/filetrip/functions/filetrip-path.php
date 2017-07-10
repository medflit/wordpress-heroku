<?php

/**
 * Returning Protected Directory to being Delete
 *
 * @return array
 */
function fileTripGetProtectedDirectory()
{
    // cache static to increase performance
    static $basePath;

    if (!defined('WP_PLUGIN_DIR') || !defined('WPMU_PLUGIN_DIR')) {
        wp_plugin_directory_constants();
    }

    if (!is_array($basePath)) {
        $basePath = array(
            'BASE' => realpath(ABSPATH) ?: ABSPATH,
            'ADMIN' => realpath(ABSPATH . 'wp-admin') ?: wp_normalize_path(BASEPATH . 'wp-admin'),
            'INCLUDE' => realpath(ABSPATH . WPINC) ?: wp_normalize_path(BASEPATH . WPINC),
            'CONTENT' => realpath(WP_CONTENT_DIR) ?: WP_CONTENT_DIR,
            'PLUGIN' => realpath(WP_PLUGIN_DIR) ?: WP_PLUGIN_DIR,
            'MU_PLUGIN' => realpath(WPMU_PLUGIN_DIR) ?: WPMU_PLUGIN_DIR,
            'THEME' => realpath(WP_CONTENT_DIR . '/themes') ?: WP_CONTENT_DIR . '/themes',
        );
    }

    return $basePath;
}

/**
 * Check if Path is Allowed To change
 *
 * @param string $path
 * @param bool  $allowThemeOrPluginRoot set to true if allowed on add in theme / plugin directory
 * @return bool
 */
function fileTripIsAllowDirectory($path, $allowThemeOrPluginRoot = false)
{
    if (!is_string($path) || trim($path) == '') {
        return false;
    }

    $path = wp_normalize_path($path);
    // if path is empty or on Root system
    if (!$path || $path == '/') {
        return false;
    }

    $path = is_dir($path) && realpath($path) ? realpath($path) : untrailingslashit($path);

    /**
     * @var array
     */
    $pathProtected = fileTripGetProtectedDirectory();
    // does not allow
    if (in_array($path, $pathProtected)
        // on include
        || strpos($path, $pathProtected['INCLUDE']) === 0
        // on admin
        || strpos($path, $pathProtected['ADMIN']) === 0
    ) {
        return false;
    }
    if (!$allowThemeOrPluginRoot) {
        // not allowed for direct
        $isInvalid = in_array(
            dirname($path),
            array(
                $pathProtected['MU_PLUGIN'],
                $pathProtected['PLUGIN'],
                $pathProtected['THEME']
            )
        );
        if (!$isInvalid) {
            return false;
        }
    }

    return true;
}