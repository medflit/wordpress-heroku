<?php
/**
 * Frontend Uploader Settings
 */
class Filetrip_Settings {

	private $settings_api;

	function __construct() {
		$this->settings_api = new Filetrip_Settings_API;
        
		add_action( 'current_screen', array( $this, 'action_current_screen' ) );
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
	}

	/**
	 * Only run if current screen is plugin settings or options.php
	 * @return [type] [description]
	 */
	function action_current_screen() {
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        // Initialize settings
        $this->settings_api->admin_init($this);
	}
    
    function header_top_calback()
    {
			// Channels will use this action to update their current active status in setting page
			do_action('itf/filetrip/settings/page/header');
    }
    
    static function check_if_backup_disabled()
    {
      // If backup is disabled
      $tempSettings = get_option( Filetrip_Uploader::$settings_slug, false );
      if($tempSettings!=false && isset($tempSettings['disable_backup']) && 'on' == $tempSettings['disable_backup'])
      {
        // Cancel and do nothing
        return true;
      }else{
        return false;
      }
    }
    
    function footer_calback()
    {

      // If backup is disabled
      if($this->check_if_backup_disabled())
        return;
    
      // Refresh the schedules from the database to make sure we have the latest changes
      FILETRIP_BKP_Schedules::get_instance()->refresh_schedules();

      $schedules = FILETRIP_BKP_Schedules::get_instance()->get_schedules();

      if(empty($schedules))
      {
        return;
      }
        
      if ( ! empty( $_GET['filetrip_schedule_id'] ) ) {
          $schedule = new FILETRIP_BKP_Scheduled_Backup( sanitize_text_field( $_GET['hmbkp_schedule_id'] ) );
      } else {
          $schedule = $schedules[0];
      }
      
      if($schedules[0] && count($schedules[0]->get_backups())>0){
        $schedule = $schedules[0];
        echo '<h4>The custom backup schedule:</h4>';
        require ITECH_FILETRIP_PLUGIN_DIR_PATH . 'includes/backups-table.php';
      }
    }

	/**
	 * Get post types for checkbox option
	 * @return array of slug => label for registered post types
	 */
	static function get_post_types() {
		$arfaly_public_post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach( $arfaly_public_post_types as $slug => $post_object ) {
			if ( $slug == 'attachment' ) {
				unset( $arfaly_public_post_types[$slug] );
				continue;
			}
			$arfaly_public_post_types[$slug] = $post_object->labels->name;
		}
		return $arfaly_public_post_types;
	}

	function action_admin_menu() {
		add_submenu_page( 'edit.php?post_type='.Filetrip_Constants::POST_TYPE, 'Settings' , 'Settings', 'edit_posts', Filetrip_Constants::POST_TYPE.'_settings', array( $this, 'plugin_page' ) );
	}

	function get_settings_sections() {
      
      $sections = array(
          array(
              'id' => Filetrip_Constants::POST_TYPE.'_settings',
              'title' => __( ucfirst(Filetrip_Constants::POST_TYPE).' Settings', 'filetrip-plugin' ),
          ),
		  array(
              'id' => Filetrip_Constants::POST_TYPE.'_mime_setting',
              'title' => __( 'MIME Settings', 'filetrip-plugin' ),
          )
      );
      
      // If backup is disabled
      if(!Filetrip_Settings::check_if_backup_disabled())
      {
			$sections[] = array(
				'id' => Filetrip_Constants::POST_TYPE.'_backup_setting',
				'title' => __( 'Custom Backup Schedule', 'filetrip-plugin' ),
				);
      }
   
      // Allow destination widgets to add their own config section
      $sections = apply_filters('itf/filetrip/settings/add/section', $sections);
      
      return $sections;
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	static function get_settings_fields() {;

		$settings_fields = array(
			Filetrip_Constants::POST_TYPE.'_settings' => array(
                array(
					'name' => 'enable_auto_delete',
					'label' => __( 'Automatically delete files from Wordpress', 'filetrip-plugin' ),
					'desc' => __( 'Automatically delete files from Wordpress if they have been successfully sent to all other selected channels.', 'filetrip-plugin' ),
					'type' => 'checkbox',
					'default' => '',
				),
                array(
					'name' => 'notify_admin_uploads',
					'label' => __( 'Notify site admin', 'filetrip-plugin' ),
					'desc' => __( 'Notify admin about new uploads', 'filetrip-plugin' ),
					'type' => 'checkbox',
					'default' => '',
				),
                array(
					'name' => 'notify_admin_backup',
					'label' => __( '', 'filetrip-plugin' ),
					'desc' => __( 'Notify admin about new backups', 'filetrip-plugin' ),
					'type' => 'checkbox',
					'default' => '',
				),
               array(
					'name' => 'auto_approve_user_files',
					'label' => __( 'Auto-approve user uploads', 'filetrip-plugin' ),
					'desc' => __( '<b style="color:red">Not Recommended</b>: <span style="font-size:12px">Be-careful, by enabling this feature you might make your Users wait for a long time. As it will take a prolong time to iterate through uploaded files; and, start forwarding the files into all of your selected channels.</span> <br><b><span style="font-size:10px">(EXAMPLE: So for a 100MB file with all channels selected, your server must transfer the same file 4 times to 4 different channels; which might force it to timeout and generate <b>Error 404</b>)</span>', 'filetrip-plugin' ),
					'type' => 'checkbox',
					'default' => '',
				),
				array(
					'name' => 'disable_backup',
					'label' => __( 'Disable backups', 'filetrip-plugin' ),
					'desc' => __( 'Yes', 'filetrip-plugin' ),
					'type' => 'checkbox',
					'default' => '',
				),
				array(
					'name' => 'admin_notification_text',
					'label' => __( 'Admin Notification', 'filetrip-plugin' ),
					'desc' => __( 'Message that admin will get on new file upload', 'filetrip-plugin' ),
					'type' => 'wysiwyg',
					'default' => 'Someone uploaded a new '. ucfirst(Filetrip_Constants::PLUGIN_NAME) .' file, please moderate at:<br>'.
							'<a href="'. admin_url( Filetrip_Constants::UPLOAD_PAGE_MENU) .'">Review &amp; Approve</a>',
					'sanitize_callback' => 'wp_filter_post_kses'
				),
				array(
					'name' => 'notification_email',
					'label' => __( 'Notification email', 'filetrip-plugin' ),
					'desc' => __( 'Leave blank to use site admin email', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '',
					'sanitize_callback' => 'sanitize_email',
				)
			),
			Filetrip_Constants::POST_TYPE.'_mime_setting' => array(
				array(
					'name' => 'enabled_files',
					'label' => __( 'Allow extra file types to be uploaded in addition to the extensions allowed natively by Wordpress', 'filetrip-plugin' ),
					'desc' => '',
                    'type' => 'multicheck',
                    'default' => array(),
                    'options' => itech_arfaly_get_exts_descs(),
				)
			),
            Filetrip_Constants::POST_TYPE.'_backup_setting' => array(
				array(
					'name' => 'schedule_type',
					'label' => __( 'Backup', 'filetrip-plugin' ),
					'desc' => __( '', 'filetrip-plugin' ),
					'type' => 'select',
                    'default' => 'complete',
                    'options' => array(
                        'complete' => 'Both Database & Files',
                        'file' => 'Files only',
                        'database' => 'Database Only'
                    )
				),
				array(
					'name' => 'schedule_recurrence_type',
					'label' => __( 'Schedule', 'filetrip-plugin' ),
					'desc' => __( '', 'filetrip-plugin' ),
					'type' => 'select',
					'default' => 'manually',
                    'options' => array(
                        'manually' => 'Manual Only',
                        'filetrip_bkp_hourly' => 'Once Hourly',
                        'filetrip_bkp_twicedaily' => 'Twice Daily',
                        'filetrip_bkp_daily' => 'Once Daily',
                        'filetrip_bkp_weekly' => 'Once Weekly',
                        'filetrip_bkp_fortnightly' => 'Once Biweekly',
                        'filetrip_bkp_monthly' => 'Once Monthly',
                    )
                ),
                array(
					'name' => 'schedule_start_week_day',
					'label' => __( 'Start day', 'filetrip-plugin' ),
					'desc' => __( '', 'filetrip-plugin' ),
					'type' => 'select',
					'default' => 'manually',
                    'class' => 'recurring-setting start-day',
                    'options' => array(
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    )
                ),
                array(
					'name' => 'schedule_start_month_day',
					'label' => __( 'Start day of month', 'filetrip-plugin' ),
					'desc' => __( '', 'filetrip-plugin' ),
					'type' => 'number_day',
					'default' => '1',
                ),
                array(
					'name' => 'schedule_start_time',
					'label' => __( 'Start time', 'filetrip-plugin' ),
					'desc' => __( '', 'filetrip-plugin' ),
					'type' => 'hours_minutes',
                ),
                array(
					'name' => 'no_max_backups',
					'label' => __( 'Number of backups to store on this server', 'filetrip-plugin' ),
					'desc' => __( 'Past this limit, older backups will be deleted automatically.', 'filetrip-plugin' ),
                    'type' => 'number_backup',
                    'default' => '2',
				),
                array(
					'name' => 'backup_now',
					'label' => __( 'Immediate backup', 'filetrip-plugin' ),
					'desc' => __( 'Backup with the same above settings.', 'filetrip-plugin' ),
                    'type' => 'backup_now',
                    'default' => '2',
				)
			)
		);
        
		// Allow destination channels to extend setting fields and their own setting 
		$settings_fields = apply_filters('itf/filetrip/settings/add/section_fields', $settings_fields);
        
		return $settings_fields;
	}

	/**
	 * Render the UI
	 */
	function plugin_page() {
		echo arfaly_get_icon('logo-text','','', Filetrip_Constants::ITF_WEBSITE_LINK);
		echo '<div class="wrap">';
    	$this->header_top_calback();
		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();
		echo '</div>';
    	$this->footer_calback();
	}
}

// Instantiate
$filetrip_settings = new Filetrip_Settings;