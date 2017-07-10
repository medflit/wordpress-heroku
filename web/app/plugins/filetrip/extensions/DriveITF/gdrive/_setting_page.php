<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!class_exists('Filetrip_Google_Drive_Setting_page')){

    class Filetrip_Google_Drive_Setting_page
    {
        static public $google_settings_slug = 'filetrip_google_drive_setting';
        
        function __construct() {
            if(!Filetrip_Drive_Utility::check_if_drive_disabled()){
                add_filter( 'itf/filetrip/settings/add/section', array( $this, 'add_setting_section' ), 1);
                add_filter( 'itf/filetrip/settings/add/section_fields', array( $this, 'add_setting_fields' ), 1);
                add_action( 'itf/filetrip/settings/page/header' , array( $this, 'print_html_in_page_header' ), 1);
            }
        }

        function add_setting_section($sections)
        {
            $sections[] = array(
                'id' => Filetrip_Constants::POST_TYPE.'_google_drive_setting',
                'title' => __( 'Google Drive API settings', 'filetrip-plugin' ),
            );

            return $sections;
        }

        function add_setting_fields($fields)
        {
            $channelSettings = array(
				array(
					'name' => 'google_client_id',
					'label' => __( 'Google Drive client ID', 'filetrip-plugin' ),
					'desc' => __( 'You shall create your own app <a target="_blank" href="https://console.developers.google.com"><b>Create your Google Drive application now</b></a>', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'name' => 'google_client_secret',
					'label' => __( 'Google Drive client secret', 'filetrip-plugin' ),
					'desc' => __( 'Google Drive client secret', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '' 
                ),
				array(
					'name' => 'google_redirect_url',
					'label' => __( 'Google Drive app redirect URL', 'filetrip-plugin' ),
					'desc' => __( 'This must be identical to your Google Drive app configuration', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '' 
                )
			);

            $fields[Filetrip_Google_Drive_Setting_page::$google_settings_slug] = $channelSettings;

            return $fields;
        }

        function print_html_in_page_header()
        {
            $drive_auth_state = false;
            // Check if Drive setting is complete
            $drive_setting = (array)get_option( Filetrip_Google_Drive_Setting_page::$google_settings_slug);
            if(isset($drive_setting['google_client_id']) && isset($drive_setting['google_client_secret']) 
                    && !Filetrip_Google_Drive::is_google_drive_active())
            {
                $drive_auth_state = true;
            }else{
                $drive_auth_state = false;
            }

            if(!Filetrip_Drive_Utility::check_if_drive_disabled() && Filetrip_Google_Drive::is_google_drive_active())
            {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
                <p><strong>Your Google Drive is active.</strong></p>
                </div>
                <?php
            }


            if(!Filetrip_Drive_Utility::check_if_drive_disabled() && $drive_auth_state)
            {
                ?>
                <div class="error settings-error notice is-dismissible"> 
                <b>Make sure to fill the correct Google Drive API Settings. Then, please click over the following button to get your plugin activated.</b>
                <br><a target="_blank" href='<?php echo admin_url(Filetrip_Constants::OPTION_PAGE).'&action=do_drive_auth&arfaly_nonce='.  wp_create_nonce(Filetrip_Constants::NONCE); ?>' class="button button-large" style="margin-bottom:10px;margin-left:10px" >Authorize your plugin</a>
                </div>
                <?php
            }
        }

    }

}