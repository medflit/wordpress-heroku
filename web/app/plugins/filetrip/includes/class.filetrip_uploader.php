<?php
/**
 * Class Filetrip_Uploader
 */
class Filetrip_Uploader
{
    public $settings;
    private $backup_settings;
    private $ftp_settings;

    static public $settings_slug = 'filetrip_settings';
    static public $backup_settings_slug = 'filetrip_backup_setting';
    static public $mime_settings_slug = 'filetrip_mime_setting';
    static public $g_post_type = 'filetrip';

    private $manage_permissions;
    private $post_type = 'filetrip';
    private $meta_prefix = Filetrip_Constants::METABOX_PREFIX;

    // Slug for query names that will be sent along transfer command
    const DROPBOX_SLUG = 'dropbox';
    const GOOGLE_DRIVE_SLUG = 'google-drive';
    const FTP_SLUG = 'ftp';
    const WORDPRESS_SLUG = 'wordpress';

    public function __construct()
    {
        // Init
        add_action('plugins_loaded', array( $this, 'arfaly_init' ));
    }

    public function arfaly_init()
    {
        // Clear any tmp files remained from pervious days
        $files = glob(ITECH_FILETRIP_PLUGIN_DIR_PATH.'uploads'.DIRECTORY_SEPARATOR.'*');

        if (is_array($files) && count($files) > 0) {
            foreach ($files as $file) {
                if (strpos($file, ('arfaly.part.'.date('d'))) === false) {
                    unlink($file);
                }
            }
        }
        // ******************** Delete section completed **********
        // ********************************************************
        //
        // To enable header usage for uploads
        ob_start();

        load_plugin_textdomain('filetrip-plugin', false, ITECH_FILETRIP_PLUGIN_DIR_PATH . '/languages/');

        // Add arfaly upload manage menu
        add_action('admin_menu', array($this, 'arfaly_add_menu_items'));
        add_action('admin_init', array($this ,'hook_new_media_columns'));

        // Ajax action list
        add_theme_support('post-thumbnails');

        // Error handling
        add_action('itech_error_caught', array($this, 'filetrip_error_handling'));
        add_action('admin_notices', array( $this, 'filetrip_admin_notice_handler'));

        // Ajax hooks
        add_action('wp_ajax_'.Filetrip_Constants::NONCE, array( $this, 'itech_submit_arfaly'));
        add_action('wp_ajax_nopriv_'.Filetrip_Constants::NONCE, array( $this, 'itech_submit_arfaly'));
        add_action('wp_ajax_approve_arfaly', array( $this, 'approve_media' ));
        add_action('wp_ajax_delete_arfaly', array( $this, 'delete_post' ));
        add_action('wp_ajax_immediate_backup', array( $this, 'ajax_immediate_backup' ));

        /*==========================================*/
        //               Custom actions
        /*==========================================*/
        // This should fire when a file gets uploaded successfully
        add_action('itf/filetrip/upload/successful', array( 'Filetrip_Uploader_Recorder', 'register_uploaded_file'), 10, 4);
        add_action('itf/filetrip/upload/session/started', array( $this, 'new_files_uploaded'));

        // Post action list
        //add_action( 'admin_post_send_backup_archive', array( $this, 'transfer_backup' ) );

        // Add shortcode
        add_shortcode('filetrip', array( $this, 'arfaly_func'));

        // Add client side scripts
        add_action('wp_enqueue_scripts', array( $this, 'load_arfaly_libraries'));
        add_action('admin_enqueue_scripts', array($this ,'arfaly_admin_enqueue'));

        // Customize mimes
        add_filter('upload_mimes', array( $this, 'custom_arfaly_upload_mimes'));

        add_filter('manage_edit-'.$this->post_type.'_columns', array( $this, 'set_custom_edit_arfaly_columns'));
        add_action('manage_'.$this->post_type.'_posts_custom_column', array( $this, 'custom_arfaly_column'), 10, 2);

        add_filter('posts_where', array( $this, 'filter_posts_where' ));

        // Since 4.01 we need to explicitly disable texturizing of shortcode's inner content
        add_filter('no_texturize_shortcodes', array( $this, 'filter_no_texturize_shortcodes' ));

        $this->manage_permissions = apply_filters('arfaly_manage_permissions', 'edit_posts');

        // Debug mode filter
        $this->is_debug = (bool) apply_filters('arfaly_is_debug', defined('WP_DEBUG') && WP_DEBUG);

        $this->settings = array_merge(Filetrip_Uploader::settings_defaults(), (array) get_option(Filetrip_Uploader::$settings_slug, Filetrip_Uploader::settings_defaults()));

        $this->backup_settings = (array)get_option(Filetrip_Uploader::$backup_settings_slug);

        $this->mime_settings = array_merge($this->mime_settings_defaults(), (array) get_option(Filetrip_Uploader::$mime_settings_slug, $this->mime_settings_defaults()));

        $this->process_backup_schedules();
    }

    public static function get_filetrip_main_settings()
    {
        return array_merge(Filetrip_Uploader::settings_defaults(), (array) get_option(Filetrip_Uploader::$settings_slug, Filetrip_Uploader::settings_defaults()));
    }

    // *************** ERROR HANLDING ***************
    // **********************************************
    // Handle warnings and notices in admin dashboard
    public function filetrip_admin_notice_handler()
    {
        // Skip if empty
        if (false === ( $error_array = get_transient(Filetrip_Constants::ERROR_TRANSIENT) )) {
            return;
        } else {
            if (empty($error_array)) {
                return;
            }
        }

        foreach ($error_array as $error) {
            $class = "error";
            echo '<div class="$class"> <p>$error</p></div>';
        }
        set_transient(Filetrip_Constants::ERROR_TRANSIENT, array());
    }

    // exp should be passed in string format
    public function filetrip_error_handling($exp)
    {
        // Get any existing Filetrip error transient
        if (false === ( $error_array = get_transient(Filetrip_Constants::ERROR_TRANSIENT) )) {
            set_transient(Filetrip_Constants::ERROR_TRANSIENT, array($exp));
        } else {
            if (!empty($error_array)) {
                array_push($error_array, $exp);
            } else {
                $error_array = array($exp);
            }
        }
        set_transient(Filetrip_Constants::ERROR_TRANSIENT, $error_array, 1 * HOUR_IN_SECONDS);
    }
    // ****************** END ERROR HANDLING ********

    // Add the column
    public function filename_column($cols)
    {
        $cols[Filetrip_Constants::MEDIA_COLUMN_SLUG] = "Trip your file";
        return $cols;
    }


    // Hook actions to admin_init
    public function hook_new_media_columns()
    {
        add_filter('manage_media_columns', array($this, 'filename_column'));
    }

    public function option_updated()
    {
        // When option gets updated. Do something
    }

    public function filter_no_texturize_shortcodes($shortcodes)
    {
        $shortcodes[] = $this->post_type;
        return $shortcodes;
    }

    public function custom_arfaly_upload_mimes()
    {

        // Use wp_get_mime_types if available, fallback to get_allowed_mime_types()
        $mime_types = function_exists('wp_get_mime_types') ? wp_get_mime_types() : get_allowed_mime_types() ;
        $arfaly_mime_types = itech_arfaly_get_mime_types();
        // Workaround for IE
        $mime_types['jpg|jpe|jpeg|pjpg'] = 'image/pjpeg';
        $mime_types['png|xpng'] = 'image/x-png';
        // Iterate through default extensions
        foreach ($arfaly_mime_types as $extension => $details) {
            // Skip if it's not in the settings
            if (!in_array($extension, $this->mime_settings['enabled_files'])) {
                continue;
            }

            // Iterate through mime-types for this extension
            foreach ($details['mimes'] as $ext_mime) {
                $mime_types[ $extension . '|' . $extension . sanitize_title_with_dashes($ext_mime) ] = $ext_mime;
            }
        }

        // Configuration filter: arfaly_allowed_mime_types should return array of allowed mime types (see readme)
        $mime_types = apply_filters('filetrip_allowed_mime_types', $mime_types);

        foreach ($mime_types as $ext_key => $mime) {
            // Check for php just in case
            if (false !== strpos($mime, 'php')) {
                unset($mime_types[$ext_key]);
            }
        }

        return $mime_types;
    }

    function arfaly_admin_enqueue()
    {
        global $typenow;

        if ($typenow == $this->post_type || (isset($_GET['page']) && $_GET['page'] == 'filetrip_settings')) {
            wp_enqueue_style('filetrip_meta_box_styles', ITECH_FILETRIP_PLUGIN_URL . '/assets/css/arfaly-admin.css');

            // Register the script first.
            wp_register_script('meta_box_filetrip_js', ITECH_FILETRIP_PLUGIN_URL . '/assets/js/arfaly-admin.js');

            // Now we can localize the script with our data.
            $translation_array = array( 'ajax_url' => admin_url('admin-ajax.php') );

            if ((isset($_GET['page']) && $_GET['page'] == 'filetrip_settings')) {
                $translation_array['page'] = 'filetrip_settings';
            }

            wp_localize_script('meta_box_filetrip_js', 'arfaly_object', $translation_array);

            // The script can be enqueued now or later.
            wp_enqueue_script('meta_box_filetrip_js');
        }
    }

    // Add the main shortcode for Arfaly [closify id="<id>"]
    function arfaly_func($attributes)
    {
        $attributes = shortcode_atts(array('id' => 0), $attributes);

        return Filetrip_Uploader::building_arfaly_container($attributes['id']);
    }

    // Enqueue plugin scripts
    public function load_arfaly_libraries()
    {

        wp_enqueue_script(
            'filetrip-multi-script',
            ITECH_FILETRIP_PLUGIN_URL . '/assets/js/filetrip-multi-min.js',
            array('jquery'),
            Filetrip_Constants::VERSION,
            true
        );
        wp_enqueue_style(
            'closify-default',
            ITECH_FILETRIP_PLUGIN_URL . '/assets/css/style.css',
            array(),
            Filetrip_Constants::VERSION
        );
    }

    static function building_arfaly_container($id, $required = false)
    {
        // Get closify meta information
        $meta = get_post_meta($id);

        $allowGuests = false;

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'allow_guests'])) {
            $allowGuests = true;
        }

        if (!is_user_logged_in() && !$allowGuests) {
            return;
        }

        global $current_user;
        wp_get_current_user();

        static $count = 0;
        static $previous_post_id = 0;

        // This will fix blog page counter reset issue
        if ($previous_post_id != $id) {
            $count = 0;
        }

        $closify_info = array();

        // ======== Label Translation Section =======
        $closify_info['enterTitleLbl'] = __('Enter Upload Title', 'filetrip-plugin');
        $closify_info['enterDescLbl'] = __('Enter Upload Description', 'filetrip-plugin');
        $closify_info['enterEmailLbl'] = __('Enter your email', 'filetrip-plugin');
        $closify_info['enterName'] = __('Enter your name', 'filetrip-plugin');
        $closify_info['label'] = __('Allowed file types are gif, jpg, and png.', 'filetrip-plugin');
        $closify_info['dropBox']['title'] = __('Drop files here', 'filetrip-plugin');
        $closify_info['stableUploadLbl'] = __('Everything going well so far!', 'filetrip-plugin');
        $closify_info['uploadBtnLbl'] = __('Upload', 'filetrip-plugin');
        $closify_info['previewBtnLbl'] = __('Preview', 'filetrip-plugin');
        $closify_info['deleteBtnLbl'] = __('Delete', 'filetrip-plugin');
        $closify_info['deleteConfirmLbl'] = __('Are you sure you want to delete the file?', 'filetrip-plugin');
        // ======== Label Translation Section =======

        $closify_info['dropBox']['height'] = 100;
        $closify_info['dropBox']['fontSize'] = 26;
        $closify_info['formRequired'] = ($required)?"true":"false";


        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'drop_box_font_size'])) {
            $closify_info['dropBox']['fontSize'] = $meta[Filetrip_Constants::METABOX_PREFIX.'drop_box_font_size'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'drop_box_height'])) {
            $closify_info['dropBox']['height'] = $meta[Filetrip_Constants::METABOX_PREFIX.'drop_box_height'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'uploader_theme'])) {
            $closify_info['theme'] = $meta[Filetrip_Constants::METABOX_PREFIX.'uploader_theme'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'drop_box_title'])) {
            $closify_info['dropBox']['title'] = $meta[Filetrip_Constants::METABOX_PREFIX.'drop_box_title'];
        }

        // Check user's meta for any pre-stored info
        $existingImg = get_user_meta($current_user->ID, 'closify_img_'.$id, true);

        if (isset($existingImg) && !empty($existingImg) && isset($existingImg["closify-".$id."-".$count])) {
            $img = wp_get_attachment_url($existingImg["closify-".$id."-".$count]);

            if ($img == "") {
                delete_user_meta($current_user->ID, 'closify_img_'.$id);
            }

            $closify_info['startWithThisImg'] = $img;
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'file_upload_limit'])) {
            $closify_info['limitNumberofFiles'] = intval($meta[Filetrip_Constants::METABOX_PREFIX.'file_upload_limit'][0]);
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'max_file_size'])) {
            $closify_info['allowedFileSize'] = intval($meta[Filetrip_Constants::METABOX_PREFIX.'max_file_size'][0]);
            $closify_info['allowedFileSize'] = $closify_info['allowedFileSize'] * 1048576;
        }

        if(isset($meta[Filetrip_Constants::METABOX_PREFIX.'file_preview']))
            $closify_info['disablePreview'] = ($meta[Filetrip_Constants::METABOX_PREFIX.'file_preview'][0]=='on')?'true':'false';
            
        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'debug'])) {
            $closify_info['debug'] = ($meta[Filetrip_Constants::METABOX_PREFIX.'debug'][0]=='on')?'true':'false';
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'enforce_info'])) {
            $closify_info['enforceInfo'] = ($meta[Filetrip_Constants::METABOX_PREFIX.'enforce_info'][0]=='on')?'true':'false';
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'required'])) {
            $closify_info['required'] = ($meta[Filetrip_Constants::METABOX_PREFIX.'required'][0]=='on')?'true':'false';
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'disable_drag_drop'])) {
            $closify_info['dragDrop'] = ($meta[Filetrip_Constants::METABOX_PREFIX.'disable_drag_drop'][0]=='on')?'false':'true';
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'desc_placeholder'])) {
            $closify_info['enterDescLbl'] = $meta[Filetrip_Constants::METABOX_PREFIX.'desc_placeholder'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'icon_image'])) {
            $closify_info['backgroundIcon'] = $meta[Filetrip_Constants::METABOX_PREFIX.'icon_image'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'title_placeholder'])) {
            $closify_info['enterTitleLbl'] = $meta[Filetrip_Constants::METABOX_PREFIX.'title_placeholder'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'background_color'])) {
            $closify_info['backgroundColor'] = $meta[Filetrip_Constants::METABOX_PREFIX.'background_color'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'logo_color'])) {
            $closify_info['logoColor'] = $meta[Filetrip_Constants::METABOX_PREFIX.'logo_color'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'border_color'])) {
            $closify_info['borderColor'] = $meta[Filetrip_Constants::METABOX_PREFIX.'border_color'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'label_color'])) {
            $closify_info['labelColor'] = $meta[Filetrip_Constants::METABOX_PREFIX.'label_color'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'text_color'])) {
            $closify_info['textColor'] = $meta[Filetrip_Constants::METABOX_PREFIX.'text_color'][0];
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'target_debug'])) {
            if ($meta[Filetrip_Constants::METABOX_PREFIX.'target_debug']!="") {
                $closify_info['targetOutput'] = $meta[Filetrip_Constants::METABOX_PREFIX.'target_debug'][0];
            }
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'label'])) {
            $closify_info['label'] = $meta[Filetrip_Constants::METABOX_PREFIX.'label'][0];
        }

        $closify_info['url'] = admin_url('admin-ajax.php');
        $closify_info['nonce'] = wp_create_nonce(Filetrip_Constants::NONCE);
        $closify_info['action'] = Filetrip_Constants::NONCE;
        $closify_options = json_encode($closify_info);

        // Removing double quotation from the keys
        $closify_options = preg_replace('/"([a-zA-Z]+[a-zA-Z0-9_]*)":/', '$1:', $closify_options);

        // *** Pass loading gif and background photo dynamically here

        $closifyId = 'multi-'.$id.'-'.$count;
        // to pass it into the custom javascript script
        $output = '<div id="'.$closifyId.'" closify-idx="'.$count.'" closify-id="'.$id.'"></div>';
        $output = $output . '<script type="text/javascript">
          jQuery(document).ready(function(){
            jQuery("#'.$closifyId.'").arfaly('.$closify_options.');
          });
        </script>';

        $count++;
        $previous_post_id = $id;

        return $output;
    }

    function set_custom_edit_arfaly_columns($columns)
    {
        global $itech_arfaly_globals;

        unset(
            $columns['taxonomy-filetrip_category'],
            $columns['taxonomy-filetrip_tag'],
            $columns['comments']
        );

        $columns['author'] = __('Author', $itech_arfaly_globals['domain']);
        $columns['shortcode'] = __('Shortcode', $itech_arfaly_globals['domain']);

        return $columns;
    }

    function custom_arfaly_column($column, $post_id)
    {
        global $itech_arfaly_globals;

        $post = get_post($post_id);

        switch ($column) {
            case 'quality':
                $quality = get_post_meta($post_id, '_closify_quality', true);
                if (is_string($quality)) {
                    echo $quality;
                } else {
                    _e('Unable to get quality', $itech_arfaly_globals['domain']);
                }
                break;
            case 'shortcode':
                echo '<strong>['.$this->post_type.' id="'.$post->ID.'"]</strong>';
                break;
        }
    }

    /* Event section */
    function new_files_uploaded($arfaly_post_id)
    {
        // Notify the admin via email
        $this->_notify_admin();

        // Add your logic here
    }

    function arfaly_report_error($error)
    {
        $json = array(
            "msg" => 'false',
            "error" => $error
        );

        echo json_encode($json);
        die();
    }

    /**
     * Notify site administrator by email
     */
    public function _notify_admin(FILETRIP_BKP_Scheduled_Backup $schedule = null)
    {

        // Email notifications are disabled, or upload has failed, bailing
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        if (( 'on' == $this->settings['notify_admin_uploads'] )) {
        // TODO: It'd be nice to add the list of upload files
            $to = !empty($this->settings['notification_email']) && filter_var($this->settings['notification_email'], FILTER_VALIDATE_EMAIL) ? $this->settings['notification_email'] : get_option('admin_email');
            $subj = __('New '.ucfirst(Filetrip_Constants::POST_TYPE).' content was uploaded on your website', 'filetrip-plugin');
            mail($to, $subj, $this->settings['admin_notification_text'], $headers);
        }
        if (( 'on' == $this->settings['notify_admin_backup'] ) && $schedule!=null) {
            $bkp_file_path = $schedule->get_backups();
            //error_log( print_r($bkp_file_path,true));
            if (empty($bkp_file_path)) {
                return;
            } else {
                reset($bkp_file_path);
                $bkp_file_path = current($bkp_file_path);
                //error_log( print_r($bkp_file_path,true));
            }

            // TODO: It'd be nice to add the list of uploaded files
            $to = !empty($this->settings['notification_email']) && filter_var($this->settings['notification_email'], FILTER_VALIDATE_EMAIL) ? $this->settings['notification_email'] : get_option('admin_email');
            $subj = __('New '.ucfirst(Filetrip_Constants::POST_TYPE).' backup has been generated', 'filetrip-plugin');
            $body = '<h2>New backup has been generated for your website:</h2><br>';
            $body .= '<b>Backup size</b>: ('.esc_html(size_format(@filesize($bkp_file_path))).')<br>';
            $body .= '<b>Backup name</b>: ('.$schedule->get_archive_filename().')<br>';
            $body .= '<b>Next Backup Schedule</b>: ['.date('d-M-y H:i:s', $schedule->get_next_occurrence()) .' GMT]<br><br>';
            $body .= '<b>To moderate, visit</b>: ['.admin_url(Filetrip_Constants::OPTION_PAGE).']<br>';

            mail($to, $subj, $body, $headers);
        }
    }

    // Process multi-images
    function itech_submit_arfaly()
    {
        // Sanitize the whole input
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $chunk_enabled = isset($_POST['chunk-upload'])?$_POST['chunk-upload']:"false";

        $nonceValidation = false;
        $post_id = "";
        $allowGuests = false;

        if (isset($_POST['closify-id'])) {
            $post_id = $_POST['closify-id'];
            // Get closify meta information
            $meta = get_post_meta($post_id);

            if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'allow_guests'])) {
                $allowGuests = true;
            }
        } else {
            $allowGuests = true;
        }

        if (!is_user_logged_in() && !$allowGuests) {
            $this->arfaly_report_error("You do not have permission!");
            return;
        }

        wp_get_current_user();

        // Nonce security validation
        if (isset($_POST['nonce'])) {
            $nonceValidation = wp_verify_nonce($_POST['nonce'], Filetrip_Constants::NONCE);
            if (!$nonceValidation) {
                $this->arfaly_report_error("You violated a security check!");
            }
        } else {
            $this->arfaly_report_error("Are you trying to hack me ?");
        }

        // Check if it is a delete command
        if (isset($_POST['command']) && $_POST['command']=='delete') {
            if (!isset($_POST['raqmkh'])) {
                $json = array();
                $json['data'] = "Oops. Something went wrong with deletion!";
                $json['status'] = 'false';

                $this->arfaly_report_error($json['data']);
            }

            $att_del_id = base64_decode($_POST['raqmkh']);

            // Handle file deletion here
            $result = wp_delete_post($att_del_id, true);

            if ($result == "false") {
                $json['data'] = "The object couldn't be deleted!";
                $json['status'] = 'false';

                $this->arfaly_report_error($json['data']);
            } else {
                echo base64_decode($_POST['arfalyfn']).' Has been deleted!';
                die();
            }
        }

        // Default max file size
        $maxFileSize = 1024 * 1024 * 1; // Max 10MB

        if (isset($_FILES["SelectedFile"])) {
            $temp = explode(".", $_FILES["SelectedFile"]["name"]);
        } elseif (isset($_POST["file-name"])) {
            $temp = explode(".", $_POST["file-name"]);
        } else {
            $json['data'] = sprintf(__("Invalid upload format.", 'filetrip_plugin'));
            $this->arfaly_report_error($json['data']);
        }

        // Business Logic
        $extension = strtolower(end($temp));
        $pass_extension_test = false;
        $strict_ext_array = array();

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'max_file_size'])) {
            $maxFileSize = intval($meta[Filetrip_Constants::METABOX_PREFIX.'max_file_size'][0]);
            $maxFileSize = $maxFileSize * 1048576;
        }

        if (isset($meta[Filetrip_Constants::METABOX_PREFIX.'strict_extensions'])) {
            $strict_extensions = $meta[Filetrip_Constants::METABOX_PREFIX.'strict_extensions'][0];
            $strict_extensions = str_replace(' ', '', $strict_extensions);
            $strict_ext_array = explode(',', $strict_extensions);

            foreach ($strict_ext_array as $ext) {
                if ($ext == $extension) {
                    $pass_extension_test = true;
                    break;
                }
            }
        } else {
            // If there is no strict extensions, take default wp mimes
            $pass_extension_test = true;
        }

        if (!$pass_extension_test) {
            $this->arfaly_report_error("Unsupported file type (".$extension.")!");
        }

        ########################################
        ###### Switch to chunk upload handling
        ########################################
        $att_title = '';
        $att_description = '';
        if ($chunk_enabled == "true") {
            if (isset($_REQUEST['title'])) {
                $att_title = sanitize_text_field($_REQUEST['title']);
            }
            if (isset($_REQUEST['desc'])) {
                $att_description = sanitize_text_field($_REQUEST['desc']);
            }

            $this->handle_chunk_upload($maxFileSize, $post_id, $att_title, $att_description);
            return;
        }

        ########################################
        //
        ########################################

        if ($_FILES["SelectedFile"]["size"] > $maxFileSize) {
            $json['data'] = "File size has exceeded the limit (".$maxFileSize.")!";
            $this->arfaly_report_error($json['data']);
            return;
        }


        if ($_FILES["SelectedFile"]["error"] > 0) {
            $this->arfaly_report_error("Return Code: " . $_FILES["SelectedFile"]["error"]);
            return;
        } else {
            $post_data = array();

            // add the function above to catch the attachments creation
            add_action('add_attachment', array($this, 'arfaly_new_multi_file_attachment'));

            // Save image to library and attach it to the post
            // OLD Method::media_sideload_image($targetImgURLPath, $post_id, 'Arfaly ['.$title.'] Uploaded by: '.$current_user->display_name );
            $post_data = array('post_status' => Filetrip_Constants::POST_STATUS);

            // If auto-approve is enabled skip marking files
            if ($this->settings['auto_approve_user_files'] == 'on') {
                $post_data = array();
            }

            /*==================================*/
            // Increase execution time limit
            set_time_limit(0);

            if (isset($_POST['title'])) {
                $post_data['post_title'] = $_POST['title'];
            }

            if (isset($_POST['desc'])) {
                $post_data['post_content'] = $_POST['desc'];
            }

            $att_id = media_handle_upload("SelectedFile", $post_id, $post_data, array('test_upload'=>false,'test_form'=>false,'action' => 'editpost'));

            if (is_wp_error($att_id)) {
                remove_action('add_attachment', array($this, 'arfaly_new_multi_file_attachment'));
                $this->arfaly_report_error($att_id->get_error_message());
            }

            $image_attributes = wp_get_attachment_url($att_id); // returns an array
            if ($image_attributes) {
                $targetFileURLPath = $image_attributes;
            } else {
                remove_action('add_attachment', array($this, 'arfaly_new_multi_file_attachment'));
                $this->arfaly_report_error('Error fetching image url!');
            }

            // Reset execution time limit
            //set_time_limit(120);

            // we have the Image now, and the function above will would been fired too setting the thumbnail ID in the process, so lets remove the hook so we don't cause any more trouble
            remove_action('add_attachment', array($this, 'arfaly_new_multi_file_attachment'));

            if (isset($_POST['fileIndx']) && $_POST['fileIndx']=='0') {
                do_action('itf/filetrip/upload/session/started', $post_id, $targetFileURLPath);
            }

            $json = array(
                "status" => 'true',
                "data" => $_FILES["SelectedFile"]["name"].' Has been successfully uploaded!',
                "attid" => $att_id,
                "newFileName" => $_FILES["SelectedFile"]["name"],
                "fullPath" => $targetFileURLPath
            );
        }

        //====================== Approval management ===============*/
        // Change attachment status to filetrip
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $wpdb->posts SET post_status = '".Filetrip_Constants::POST_STATUS."' WHERE ID = %d",
                $att_id
            )
        );

        // Check if auto approve is been checked and generate post_data

        if ($this->settings['auto_approve_user_files'] == 'on') {
            $this->approve_wordpress_attachment($att_id);
            do_action('itf/filetrip/upload/forward/me', $att_id, $att_title, $att_description);

            if ('on' == $this->settings['enable_auto_delete']) {
                $this->delete_upload_and_clear_record($att_id);
            }
        }
        /*==========================================================*/

        // Print out results
        echo json_encode($json);

        die();
    }

    // This function will be invoked by "arfaly_upload_submission" in case chunk upload is enabled
    function handle_chunk_upload($maxFileSize, $post_id, $att_title = '', $att_desc = '')
    {
        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $json = array();
        $DestinationDirectory   = ITECH_FILETRIP_PLUGIN_DIR_PATH.'uploads'.DIRECTORY_SEPARATOR;
        $fileName = '';

        // 1- If the file size is more than the file limit return error
        if ($_POST['file-size'] > $maxFileSize) {
            $json['data'] = sprintf(__("File size has exceeded the limit (%s)!", 'filetrip-plugin'), $maxFileSize);
            $this->arfaly_report_error($json['data']);
        }

        // Handle chunk upload scheme

        // Get a file name
        if (isset($_POST["file-name"])) {
            $fileName = $_POST["file-name"];
        } else {
            $json['data'] = sprintf(__("Invalid upload request!", 'filetrip-plugin'));
            $this->arfaly_report_error($json['data']);
        }

        $fileName = str_replace(' ', '_', $fileName);

        $filePath = $DestinationDirectory . $fileName;

        // Get chunk number, along with chunk total number (chunks)
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        // Open temp file
        if (!$out = @fopen("{$filePath}.arfaly.part.".date('d'), $chunks ? "ab" : "wb")) {
            $json['data'] = sprintf(__("Failed to open output stream.", 'filetrip-plugin'));
            $this->arfaly_report_error($json['data']);
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                $json['data'] = sprintf(__("Failed to move uploaded file.", 'filetrip-plugin'));
                $this->arfaly_report_error($json['data']);
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                $json['data'] = sprintf(__("Failed to open input stream.", 'filetrip-plugin'));
                $this->arfaly_report_error($json['data']);
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                $json['data'] = sprintf(__("Failed to open input stream.", 'filetrip-plugin'));
                $this->arfaly_report_error($json['data']);
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks) {
            //Add unique timestamp to avoid duplication
            $fileNameInfo = pathinfo($fileName);
            $fileName = $fileNameInfo['filename'].'-'.time().'.'.$fileNameInfo['extension'];

            // Update the name to a new name to avoid overwriting files
            $filePath2 = $DestinationDirectory . $fileName;

            $filePath2 = apply_filters('itf/filetrip/upload/rename', $filePath2, $post_id, $_POST['fileIndx']);

            // Strip the temp .part suffix off
            rename("{$filePath}.arfaly.part.".date('d'), $filePath2);

            /*==================================*/
            // Reset execution time limit
            set_time_limit(0);

            $att_id = $this->arfaly_add_file_to_media_uploader($post_id, $fileName, $att_desc, $att_title);

            // Upload file to wordpress
            if (!$att_id) {
                $json['data'] = sprintf(__("Upload function failed", 'filetrip-plugin'));
                $this->arfaly_report_error($json['data']);
                return;
            }

            $targetFileURLPath = wp_get_attachment_url($att_id); // returns an array
            if (! $targetFileURLPath) {
                $json['data'] = sprintf(__("Error fetching image url!", 'filetrip-plugin'));
                $this->arfaly_report_error($json['data']);
            }

            if (isset($_POST['fileIndx']) && $_POST['fileIndx']=='0') {
                do_action('itf/filetrip/upload/session/started', $post_id, $targetFileURLPath);
            }

            $arfaly_post = get_post($att_id);
            $arfaly_post->post_status = Filetrip_Constants::POST_STATUS;

            if (isset($_POST['title'])) {
                $arfaly_post->post_title = $_POST['title'];
            }

            if (isset($_POST['desc'])) {
                $arfaly_post->post_content = $_POST['desc'];
            }

            wp_update_post($arfaly_post);

            $json = array(
                "status" => 'true',
                "data" => sprintf(__('%s Has been successfully uploaded!', 'filetrip-plugin'), $fileName),
                "attid" => $att_id,
                "newFileName" => $fileName,
                "fullPath" => $targetFileURLPath
            );

            //====================== Approval management ===============*/
            // Change attachment status to filetrip
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->posts SET post_status = '".Filetrip_Constants::POST_STATUS."' WHERE ID = %d",
                    $att_id
                )
            );

            $att_info = get_post($att_id);

            // Save record to the database
            $this->save_file_for_user($att_id, $att_info->post_author);

            // Check if auto approve is been checked and generate post_data

            if ($this->settings['auto_approve_user_files'] == 'on') {
                $this->approve_wordpress_attachment($att_id);
                do_action('itf/filetrip/upload/forward/me', $att_id, $att_title, $att_desc);

                if ('on' == $this->settings['enable_auto_delete']) {
                    $this->delete_upload_and_clear_record($att_id);
                }
            }
            /*==========================================================*/

            // Delete the temp file when the upload ends
            @unlink($filePath2);

            // Print out results
            echo json_encode($json);

            die();
        }

        // Print out results
        $json['status'] = 'true';
        echo json_encode($json);

        die();
    }

    /**
     * Copies a file from the a subdirectory of the root of the WordPress installation
     * into the uploads directory, attaches it to the given post ID, and adds it to
     * the Media Library.
     *
     * @param    int      $post_id    The ID of the post to which the image is attached.
     * @param    string   $filename   The name of the file to copy and to add to the Media Library
     */
    function arfaly_add_file_to_media_uploader($post_id, $filename, $description, $title)
    {
        // Locate the file in a subdirectory of the root of the installation
        $file = ITECH_FILETRIP_PLUGIN_DIR_PATH.'uploads' . DIRECTORY_SEPARATOR . $filename;
        // If the file doesn't exist, then write to the error log and duck out
        if (! file_exists($file) || 0 === strlen(trim($filename))) {
            error_log('The file you are attempting to upload, ' . $file . ', does not exist.');
            return false;
        }
        /* Read the contents of the upload directory. We need the
        * path to copy the file and the URL for uploading the file.
        */
        $uploads = wp_upload_dir();

        $uploads_dir = $uploads['path'];
        $uploads_url = $uploads['url'];
        // Copy the file from the root directory to the uploads directory
        copy($file, trailingslashit($uploads_dir) . $filename);
        /* Get the URL to the file and grab the file and load
        * it into WordPress (and the Media Library)
        */
        $file_path = $uploads_dir . '/' . $filename;
        $file_url = $uploads_url. '/' . $filename;

        $id = $this->arfaly_media_sideload_image($file_path, $file_url, $post_id, $title, $description);
        // If there's an error, then we'll write it to the error log.
        if (is_wp_error($id)) {
            error_log(print_r($id, true));
            return false;
        }

        return $id;
    }

    function arfaly_media_sideload_image($file_path, $file_url, $post_id, $title, $desc = null, $return = 'html')
    {
        if (! empty($file_path)) {
            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filetype = wp_check_filetype(basename($file_path), null);
            $attachment = array(
                'guid'           => $file_url,
                'post_parent' => $post_id,
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_path)),
                'post_content'   => ''
            );

            if ($title!='' && $desc!='') {
                $attachment['post_title'] = $title;
                $attachment['post_content'] = $desc;
            }

            $id = wp_insert_attachment($attachment, $file_path, $post_id);
            wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $file_path));

            // If error storing permanently, unlink.
            if (is_wp_error($id)) {
                @unlink($file_path);
                return $id;
            }

            $src = wp_get_attachment_url($id);
        }

        // Finally, check to make sure the file has been saved, then return the HTML.
        if (! empty($src)) {
            if ($return === 'src') {
                return $src;
            }

            return $id;
        } else {
            return new WP_Error('image_sideload_failed');
        }
    }

    function arfaly_new_multi_file_attachment($att_id)
    {

        $this->arfaly_save_images_for_user(true, $att_id);

        return;
    }

    // Save uploads for a specific user
    function save_file_for_user($att_id, $user_id)
    {
        // Guest users have ID of 0, so make sure to replace it with -100 for successful storage
        if ($user_id == 0) {
            $user_id = -100;
        }

        $att = get_post($att_id);
        $parsed_url = parse_url($att->guid);

        // path will give you the path right after http://[domain] -> /path/to/whatever
        $size = filesize($_SERVER['DOCUMENT_ROOT'].$parsed_url['path']);

        // Trigger upload action ($att_id, $arfaly_id, $user_id, $att_size)
        do_action('itf/filetrip/upload/successful', $att_id, $att->post_parent, $user_id, $size);

        return;
    }

    function mime_settings_defaults()
    {
        $defaults = array();
        $settings = Filetrip_Settings::get_settings_fields();

        if (!isset($settings[Filetrip_Uploader::$mime_settings_slug])) {
            return array();
        }
        foreach ($settings[Filetrip_Uploader::$mime_settings_slug] as $setting) {
            $defaults[ $setting['name'] ] = $setting['default'];
        }
        return $defaults;
    }

    public static function settings_defaults()
    {
        $defaults = array();
        $settings = Filetrip_Settings::get_settings_fields();

        if (!isset($settings[Filetrip_Uploader::$settings_slug])) {
            return array();
        }
        foreach ($settings[Filetrip_Uploader::$settings_slug] as $setting) {
            $defaults[ $setting['name'] ] = $setting['default'];
        }
        return $defaults;
    }

    function arfaly_add_menu_items()
    {
        add_submenu_page(Filetrip_Constants::MAIN_MENU_PARENT_SLUG, 'Review & Approve', 'Review & Approve', $this->manage_permissions, $this->post_type.'_manage_list', array($this, 'arfaly_render_menu'));
    }

    function build_bcakup_page_html()
    {
        ?>
        <table class="wp-list-table widefat fixed media">
            <tr>
                <th>Backup Name</th>
                <th>Date</th>
                <th>View</th>
                <th>Delete</th>
            </tr>
            <tr>
                <td>Backup Name</td>
                <td>Date</td>
                <td>View</td>
                <td>Delete</td>
            </tr>
        </table>
        <?php
    }

    function arfaly_render_menu()
    {

        /** WordPress Administration Bootstrap */
        require_once(ABSPATH . '/wp-admin/admin.php');

        $title = __('Review, Distribute, and Manage '.ucfirst($this->post_type).' Uploads', 'filetrip-plugin');
        set_current_screen('upload');
        if (! current_user_can('upload_files')) {
            wp_die(__('You do not have permission to upload files.', 'filetrip-plugin'));
        }

        ?>
        <div class="wrap">
            <?php echo arfaly_get_icon('logo-text', '', '', Filetrip_Constants::ITF_WEBSITE_LINK);?><br>
            <h1><?php echo esc_html($title); ?>
                <?php
                if (isset($_REQUEST['s']) && $_REQUEST['s']) {
                    printf('<span class="subtitle">' . __('Search results for &#8220;%s&#8221;', 'filetrip-plugin') . '</span>', get_search_query());
                } ?>
            </h1>

            <?php
            $message = '';
            $arfaly_media_list = new Filetrip_Media_List_Table();
            $pagenum = $arfaly_media_list->get_pagenum();
            $doaction = $arfaly_media_list->current_action();
            $message = $this->process_bulk_action($arfaly_media_list);
            $arfaly_media_list->prepare_items();

            if (isset($_GET['posted']) && (int) $_GET['posted']) {
                $message = __('Media attachment updated.', 'filetrip-plugin');
                $_SERVER['REQUEST_URI'] = esc_url(remove_query_arg(array( 'posted' ), $_SERVER['REQUEST_URI']));
            }

            if (isset($_GET['attached']) && (int) $_GET['attached']) {
                $attached = (int) $_GET['attached'];
                $message = sprintf(_n('Reattached %d attachment.', 'Reattached %d attachments.', $attached), $attached);
                $_SERVER['REQUEST_URI'] = esc_url(remove_query_arg(array( 'attached' ), $_SERVER['REQUEST_URI']));
            }

            if (isset($_GET['deleted']) && (int) $_GET['deleted']) {
                $message = sprintf(_n('Media attachment permanently deleted.', '%d media attachments permanently deleted.', $_GET['deleted']), number_format_i18n($_GET['deleted']));
                $_SERVER['REQUEST_URI'] = esc_url(remove_query_arg(array( 'deleted' ), $_SERVER['REQUEST_URI']));
            }

            if (isset($_GET['trashed']) && (int) $_GET['trashed']) {
                $message = sprintf(_n('Media attachment moved to the trash.', '%d media attachments moved to the trash.', $_GET['trashed']), number_format_i18n($_GET['trashed']));
                $message .= ' <a href="' . esc_url(wp_nonce_url('edit.php?post_type='.$this->post_type.'&doaction=undo&action=untrash&ids='.( isset($_GET['ids']) ? $_GET['ids'] : '' ), "bulk-media")) . '">' . __('Undo', 'filetrip-plugin') . '</a>';
                $_SERVER['REQUEST_URI'] = esc_url(remove_query_arg(array( 'trashed' ), $_SERVER['REQUEST_URI']));
            }

            if (isset($_GET['untrashed']) && (int) $_GET['untrashed']) {
                $message = sprintf(_n('Media attachment restored from the trash.', '%d media attachments restored from the trash.', $_GET['untrashed']), number_format_i18n($_GET['untrashed']));
                $_SERVER['REQUEST_URI'] = esc_url(remove_query_arg(array( 'untrashed' ), $_SERVER['REQUEST_URI']));
            }

            if (isset($_GET['approved'])) {
                $message = 'The photo was approved';
            }

            $messages[1] = __('Media attachment updated.', 'filetrip-plugin');
            $messages[2] = __('Media permanently deleted.', 'filetrip-plugin');
            $messages[3] = __('Error saving media attachment.', 'filetrip-plugin');
            $messages[4] = __('Media moved to the trash.', 'filetrip-plugin') . ' <a href="' . esc_url(wp_nonce_url('edit.php?post_type='.$this->post_type.'&doaction=undo&action=untrash&ids='.( isset($_GET['ids']) ? $_GET['ids'] : '' ), "bulk-media")) . '">' . __('Undo', 'filetrip-plugin') . '</a>';
            $messages[5] = __('Media restored from the trash.', 'filetrip-plugin');

            if (isset($_GET['message']) && (int) $_GET['message']) {
                $message = $messages[$_GET['message']];
                $_SERVER['REQUEST_URI'] = esc_url(remove_query_arg(array( 'message' ), $_SERVER['REQUEST_URI']));
            }

            if (!empty($message)) { ?>
                <div id="message" class="updated is-dismissible"><p><?php echo $message; ?></p></div>
            <?php                                                                                                                                                                                                                                                                                                                                     } ?>

            <form id="posts-filter" action="" method="get">
                <input type="hidden" name="page" value="<?php echo Filetrip_Constants::REVIEW_APPROVE_MENU_PAGE; ?>" />
                <input type="hidden" name="post_type" value="<?php echo Filetrip_Constants::POST_TYPE; ?>" />
                <?php $arfaly_media_list->search_box(__('Search Media', 'filetrip-plugin'), 'media'); ?>

                <?php $arfaly_media_list->display(); ?>

                <div id="ajax-response"></div>
                <?php find_posts_div(); ?>
                <br class="clear" />

            </form>
        </div>
        <?php
    }

    /**
     * Since WP 3.5-beta-1 WP Media interface shows private attachments as well
     * We don't want that, so we force WHERE statement to post_status = 'inherit'
     *
     * @since 0.3
     *
     * @param string $where WHERE statement
     * @return string WHERE statement
     */
    function filter_posts_where($where)
    {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return $where;
        }

        $screen = get_current_screen();
        if (! defined('DOING_AJAX') && $screen && isset($screen->base) && $screen->base == 'upload' && ( !isset($_GET['page']) || $_GET['page'] != $this->post_type.'_manage_list' )) {
            $where = str_replace("post_status = 'private'", "post_status = 'inherit'", $where);
        }
        return $where;
    }

    /**
     * Approve a media file from a forwader source
     *
     * TODO: refactor in 0.6
     *
     * @return [type] [description]
     */
    function approve_media()
    {

        // Check permissions, attachment ID, and nonce
        if (isset($_GET['id'])) {
            $query_s = '';
            $query_s = $query_s.'media='.$_GET['id'].'&';
            $query_s = $query_s.'source='.Filetrip_Constants::Transfer_Type('forward').'&';

            wp_safe_redirect(admin_url(Filetrip_Constants::FILETRIP_DISTRIBUTOR_PAGE).'&'.$query_s);
            exit;
        } else {
            exit('There was no item selected');
        }
    }

    /**
     * Delete post and redirect to referrer
     *
     * @return [type] [description]
     */
    function delete_post()
    {
        $args = array();
        if ($this->_check_perms_and_nonce() && 0 !== (int) $_GET['id']) {
            if (wp_delete_post((int) $_GET['id'], true)) {
                $args['deleted'] = 1;
            }
        }

        Filetrip_Uploader_Recorder::database_cleansing();

        wp_safe_redirect(esc_url_raw(add_query_arg($args, wp_get_referer())));
        exit;
    }

    /**
     * Delete an upload and clean the database
     *
     * @return [type] [description]
     */
    function delete_upload_and_clear_record($att_id)
    {
        $result = wp_delete_post($att_id);
        Filetrip_Uploader_Recorder::database_cleansing();

        return $result;
    }

    /**
     * Handles security checks
     *
     * @return bool
     */
    function _check_perms_and_nonce()
    {
        return current_user_can($this->manage_permissions) && wp_verify_nonce($_REQUEST['arfaly_nonce'], Filetrip_Constants::NONCE);
    }

    public function process_bulk_action($wp_media_list_table)
    {

        // security check!
        if (isset($_POST['_wpnonce']) && ! empty($_POST['_wpnonce'])) {
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (! wp_verify_nonce($nonce, $action)) {
                wp_die('Nope! Security check failed!');
            }
        }

        $action = $wp_media_list_table->current_action();

        switch ($action) {
            case 'delete':
                foreach ((array) $_REQUEST['media'] as $post_id_delete) {
                    if (!current_user_can('edit_post', $post_id_delete)) {
                        wp_die(__('You are not allowed to approve this file upload.'));
                    }

                    $post = get_post($post_id_delete);

                    if (is_object($post)) {
                        wp_delete_post($post_id_delete, true);

                        do_action('itf/filetrip/upload/deleted', $post);
                    } else {
                        return 'No file object found';
                    }
                }
                return 'Selected files has been deleted';
                break;
            case 'approve':
                if (isset($_GET['media'])) {
                    $query_s = '';
                    foreach ($_GET['media'] as $key => $value) {
                        $query_s = $query_s.'media['.$key.']='.$value.'&';
                        $this->approve_wordpress_attachment($value);
                    }

                    $query_s = $query_s.'source='.Filetrip_Constants::Transfer_Type('forward').'&';
                    wp_safe_redirect(admin_url(Filetrip_Constants::FILETRIP_DISTRIBUTOR_PAGE).'&'.$query_s);
                    exit;
                } else {
                    return 'There was no item selected';
                }
                return;
                break;
            case 'only_approve':
                if (isset($_GET['media'])) {
                    foreach ($_GET['media'] as $key => $value) {
                        $this->approve_wordpress_attachment($value);
                    }

                    // Go to Media Library right after
                    wp_safe_redirect(admin_url(Filetrip_Constants::MEDIA_LIBRARY_PAGE));
                    exit;
                } else {
                    return 'There was no item selected';
                }
                return;
                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }

    function approve_wordpress_attachment($post_id)
    {
        global $wpdb;
        $post = get_post($post_id);

        if (is_object($post)) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->posts SET post_status = 'inherit' WHERE ID = %d",
                    $post_id
                )
            );

            do_action('itf/filetrip/upload/approved', $post);
            return true;
        } else {
            return false;
        }
    }

    function process_backup_schedules()
    {
        // If backup is disabled
        if (isset($this->settings['disable_backup']) && 'on' == $this->settings['disable_backup']) {
            filetrip_bkp_deactivate();
            // Cancel and do nothing
            return;
        }


        FILETRIP_BKP_Schedules::get_instance()->refresh_schedules();

        $schedules = FILETRIP_BKP_Schedules::get_instance()->get_schedules();

        // Return if there is no schedule
        if (!isset($schedules[0])) {
            $fullSchedule = new FILETRIP_BKP_Scheduled_Backup((string) time());
            $fullSchedule->set_type('complete');
            $fullSchedule->set_schedule_start_time(filetrip_bkp_determine_start_time('filetrip_bkp_daily', array( 'hours' => '23', 'minutes' => '0' )));
            $fullSchedule->set_reoccurrence('filetrip_bkp_daily');
            $fullSchedule->set_max_backups(7);
            $fullSchedule->save();

            $schInstance = FILETRIP_BKP_Schedules::get_instance();
            $schInstance->refresh_schedules();
        }

        $schedules = FILETRIP_BKP_Schedules::get_instance()->get_schedules();
        /**
         * @var FILETRIP_BKP_Scheduled_Backup $schedule
         */
        $schedule = $schedules[0];
        $errors = array();
        $settings = array();

        if (isset($this->backup_settings['schedule_type'])) {
            $schedule_type = sanitize_text_field($this->backup_settings['schedule_type']);

            if (! trim($schedule_type)) {
                $errors['filetrip_schedule_type'] = __('Backup type cannot be empty', 'filetrip-plugin');
            } elseif (! in_array($schedule_type, array( 'complete', 'file', 'database' ))) {
                $errors['filetrip_schedule_type'] = __('Invalid backup type', 'filetrip-plugin');
            } else {
                $settings['type'] = $schedule_type;
            }
        }

        if (isset($this->backup_settings['schedule_recurrence_type'])) {
            $schedule_recurrence_type = sanitize_text_field($this->backup_settings['schedule_recurrence_type']);

            if (empty($schedule_recurrence_type)) {
                $errors['filetrip_schedule_recurrence']['filetrip_type'] = __('Schedule cannot be empty', 'filetrip-plugin');
            } elseif (! in_array($schedule_recurrence_type, array_keys(filetrip_bkp_get_cron_schedules())) && 'manually' !== $schedule_recurrence_type) {
                $errors['filetrip_schedule_recurrence']['filetrip_type'] = __('Invalid schedule', 'filetrip-plugin');
            } else {
                $settings['recurrence'] = $schedule_recurrence_type;
            }
        }

        if (isset($this->backup_settings['schedule_start_week_day'])) {
            $day_of_week = sanitize_text_field($this->backup_settings['schedule_start_week_day']);

            if (! in_array($day_of_week, array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ))) {
                $errors['filetrip_schedule_start_day_of_week'] = __('Day of the week must be a valid lowercase day name', 'filetrip-plugin');
            } else {
                $settings['start_time']['day_of_week'] = $day_of_week;
            }
        }

        if (isset($this->backup_settings['schedule_start_month_day'])) {
            $day_of_month = absint($this->backup_settings['schedule_start_month_day']);

            $options = array(
                'min_range' => 1,
                'max_range' => 31,
            );

            if (false === filter_var($day_of_month, FILTER_VALIDATE_INT, array( 'options' => $options ))) {
                $errors['filetrip_schedule_start_day_of_month'] = __('Day of month must be between 1 and 31', 'filetrip-plugin');
            } else {
                $settings['start_time']['day_of_month'] = $day_of_month;
            }
        }

        if (isset($this->backup_settings['schedule_start_time_hours'])) {
            $hours = absint($this->backup_settings['schedule_start_time_hours']);

            $options = array(
                'min_range' => 0,
                'max_range' => 23
            );

            if (false === filter_var($hours, FILTER_VALIDATE_INT, array( 'options' => $options ))) {
                $errors['filetrip_schedule_start_hours'] = __('Hours must be between 0 and 23', 'filetrip-plugin');
            } else {
                $settings['start_time']['hours'] = $hours;
            }
        }

        if (isset($this->backup_settings['schedule_start_time_minutes'])) {
            $minutes = absint($this->backup_settings['schedule_start_time_minutes']);

            $options = array(
                'min_range' => 0,
                'max_range' => 59,
            );

            if (false === filter_var($minutes, FILTER_VALIDATE_INT, array( 'options' => $options ))) {
                $errors['filetrip_schedule_start_minutes'] = __('Minutes must be between 0 and 59', 'filetrip-plugin');
            } else {
                $settings['start_time']['minutes'] = $minutes;
            }
        }

        if (isset($this->backup_settings['no_max_backups'])) {
            $max_backups = sanitize_text_field($this->backup_settings['no_max_backups']);

            if (empty($max_backups)) {
                $errors['filetrip_schedule_max_backups'] = __('Max backups can\'t be empty', 'filetrip-plugin');
            } elseif (! is_numeric($max_backups)) {
                $errors['filetrip_schedule_max_backups'] = __('Max backups must be a number', 'filetrip-plugin');
            } elseif (! ( $max_backups >= 1 )) {
                $errors['filetrip_schedule_max_backups'] = __('Max backups must be greater than 0', 'filetrip-plugin');
            } else {
                $settings['max_backups'] = absint($max_backups);
            }
        }

        // Save the service options
        foreach (FILETRIP_BKP_Services::get_services($schedule) as $service) {
            $errors = array_merge($errors, $service->save());
        }

        //print_r(FILETRIP_BKP_Services::get_services( $schedule ));

        if (! empty($settings['recurrence']) && ! empty($settings['start_time'])) {
            // Calculate the start time depending on the recurrence
            $start_time = filetrip_bkp_determine_start_time($settings['recurrence'], $settings['start_time']);

            if ($start_time) {
                $schedule->set_schedule_start_time($start_time);
            }
        }

        if (! empty($settings['recurrence'])) {
            $schedule->set_reoccurrence($settings['recurrence']);
        }

        if (! empty($settings['type'])) {
            $schedule->set_type($settings['type']);
        }

        if (! empty($settings['max_backups'])) {
            $schedule->set_max_backups($settings['max_backups']);
        }

        // Save the new settings
        $schedule->save();

        // Remove any old backups in-case max backups was reduced
        $schedule->delete_old_backups();
    }

    function ajax_immediate_backup()
    {
        // If backup is disabled
        if ('on' == $this->settings['disable_backup']) {
            filetrip_bkp_deactivate();
            // Cancel and do nothing
            die();
        }

        set_time_limit(0);
        session_write_close();
        ignore_user_abort(true);

        filetrip_bkp_cleanup();

        FILETRIP_BKP_Schedules::get_instance()->refresh_schedules();

        $schedules = FILETRIP_BKP_Schedules::get_instance()->get_schedules();
        /**
         * @var FILETRIP_BKP_Scheduled_Backup $schedule
         */
        $schedule = $schedules[0];
        $schedule->run();

        echo __('<b>Your backup has been processed</b>', 'filetrip-plugin');
        die();
    }
}
