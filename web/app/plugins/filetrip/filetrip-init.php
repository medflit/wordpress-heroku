<?php
/**
 * Check If Class Exists
 */
if (!class_exists('Filetrip_Post_Type_Registrations')) {
    return;
}

/* ----------------------------------------------------
 * Instantiate registration class, so we can add it as
 * a dependency to main plugin class.
 * ----------------------------------------------------
 */
$filetrip_post_type_registrations = new Filetrip_Post_Type_Registrations;

// Register callback that is fired when the plugin is activated.
register_activation_hook( __FILE__, 'itf_install_filetrip_database' );
register_activation_hook( __FILE__, 'activate_filetrip_plugin' );
// Register callback that is fired when the plugin is deactivated.
register_deactivation_hook(__FILE__, 'filetrip_bkp_deactivate');

// You must register post_status to avoid capabilities error message in Edit Media
register_post_status(
    Filetrip_Constants::POST_STATUS,
    array(
        'label'                     => _x( 'Filetrip', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Filetrip <span class="count">(%s)</span>', 'Filetrip <span class="count">(%s)</span>' ),
    )
);

/*
 * Initialise registrations for post-activation requests.
 */
$filetrip_post_type_registrations->init();

/*
 * Enforcing FileTrip Backups to be saved inside of Upload folder
 */
$upload_dir = wp_upload_dir();
$path = untrailingslashit( get_option( 'filetrip_bkp_default_path' ) );

// If the backups dir can't be created in WP_CONTENT_DIR then fallback to uploads
if (empty($path)
    || (
        (! is_dir($path) && ! wp_is_writable(dirname($path)))
        || (is_dir($path) && ! wp_is_writable($path))
    )
    && false === strpos($path, $upload_dir['basedir'])
) {
    $upload_dir = wp_upload_dir();
    $path = FILETRIP_Backup::conform_dir( trailingslashit( $upload_dir['basedir'] ) . 'filetrip-' . substr( FILETRIP_SECURE_KEY, 0, 10 ) . '-backups') ;
    update_option( 'filetrip_bkp_default_path', $path );
}

define('FILETRIP_BKP_PATH',$path);


// ************************* Gravity Forms Integration ***************
//require ITECHFILETRIPPLGUINURI . 'includes/gravity-form/class-gf-field-arfalyupload.php';

// ======================= CUSTOM COONE =======================================================================
// FOR_CUSTOM_USE:: require ITECHFILETRIPPLGUINURI . 'extensions/contact-info/contact-info.php';
// FOR_CUSTOM_USE:: add_action( 'plugins_loaded', array( 'FiletripContactInfoBuilder', 'getInstance' ) );
// Add caldera post processor
// require ITECHFILETRIPPLGUINURI . 'extensions/customers/coone/filetrip-form-processor.php';
// add_action( 'plugins_loaded', array( 'FiletripFormProcessor', 'getInstance' ) );
// ======================= CUSTOM COONE =======================================================================

// ********************************************************************

//filetrip_schedule_hook_run('1420449159');