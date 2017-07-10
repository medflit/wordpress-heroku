<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!class_exists('Filetrip_Dropbox_Setting_page')){

    class Filetrip_Dropbox_Setting_page
    {
        static public $dropbox_settings_slug = 'filetrip_dropbox_setting';
    
        function __construct() {
            if(!Filetrip_Dropbox_Utility::check_if_dropbox_disabled()){
                add_filter( 'itf/filetrip/settings/add/section', array( $this, 'add_setting_section' ), 1);
                add_filter( 'itf/filetrip/settings/add/section_fields', array( $this, 'add_setting_fields' ), 1);
                add_action( 'itf/filetrip/settings/page/header' , array( $this, 'print_html_in_page_header' ), 1);
            }
        }

        function add_setting_section($sections)
        {
            $sections[] = array(
                'id' => Filetrip_Constants::POST_TYPE.'_dropbox_setting',
                'title' => __( 'Dropbox API settings', 'filetrip-plugin' ),
            );

            return $sections;
        }

        function add_setting_fields($fields)
        {
            $channelSettings = array(
				array(
					'name' => 'dropbox_app_key',
					'label' => __( 'Dropbox app key', 'filetrip-plugin' ),
					'desc' => __( 'You shall create your own app <a target="_blank" href="https://www.dropbox.com/developers/apps">visit</a>', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '',
				),
				array(
					'name' => 'dropbox_app_secret',
					'label' => __( 'Dropbox app secret', 'filetrip-plugin' ),
					'desc' => __( 'Dropbox app secret', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '' 
                )
			);

            // Override setting in case app_secret and app_key is provided
            $dropbox_setting = (array)get_option( Filetrip_Dropbox_Setting_page::$dropbox_settings_slug);
            if(isset($dropbox_setting['dropbox_app_key']) && isset($dropbox_setting['dropbox_app_secret']) 
                    && !Filetrip_Dropbox::is_dropbox_active())
            {
                $channelSettings[] = array(
                            'name' => 'authorization_code',
                            'label' => __( 'Authorization Code', 'filetrip-plugin' ),
                            'desc' => __( 'Please paste your authorization code here', 'filetrip-plugin' ),
                            'type' => 'text',
                            'default' => '' 
                        );
            }

            // Update Dropbox setting setting fields 
            $fields[Filetrip_Dropbox_Setting_page::$dropbox_settings_slug] = $channelSettings;

            return $fields;
        }

        function print_html_in_page_header()
        {
            if(!Filetrip_Dropbox_Utility::check_if_dropbox_disabled() && !Filetrip_Dropbox::is_dropbox_active())
            {
                ?>
                <div class="error settings-error notice is-dismissible"> 
                    <b>Please click over below (Authorize your Dropbox App) button to get the authorization code, once you get it, paste it in authorization code box and click save.</b>
                    <br><a target="_blank" href='<?php echo admin_url(Filetrip_Constants::OPTION_PAGE).'&action=do_dropbox_auth&arfaly_nonce='.  wp_create_nonce(Filetrip_Constants::NONCE); ?>' class="button button-large" >Authorize your plugin</a>
                </div>
                <?php
            }

            if(!Filetrip_Dropbox_Utility::check_if_dropbox_disabled() && Filetrip_Dropbox::is_dropbox_active())
            {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
                <p><strong>Your Dropbox channel is active.</strong></p>
                </div>
                <?php
            }
        }

    }

}