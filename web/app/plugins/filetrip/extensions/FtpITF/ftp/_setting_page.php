<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!class_exists('Filetrip_FTP_Setting_page')){

    class Filetrip_FTP_Setting_page
    {
        static public $ftp_settings_slug = 'filetrip_ftp_setting';

        function __construct() {
            if(!Filetrip_FTP_Utility::check_if_ftp_disabled()){
                add_filter( 'itf/filetrip/settings/add/section', array( $this, 'add_setting_section' ), 1);
                add_filter( 'itf/filetrip/settings/add/section_fields', array( $this, 'add_setting_fields' ), 1);
                add_action( 'itf/filetrip/settings/page/header' , array( $this, 'print_html_in_page_header' ), 1);
            }
        }

        function add_setting_section($sections)
        {
            $sections[] = array(
              'id' => Filetrip_Constants::POST_TYPE.'_ftp_setting',
              'title' => __( 'FTP settings', 'filetrip-plugin' ),
          );

            return $sections;
        }

        function add_setting_fields($fields)
        {
            $channelSettings = array(
				array(
					'name' => 'ftp_host',
					'label' => __( 'FTP hostname or IP', 'filetrip-plugin' ),
					'desc' => __( 'FTP hostname', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '',
                    'placeholder' => 'http://'
				),
				array(
					'name' => 'ftp_port',
					'label' => __( 'FTP port', 'filetrip-plugin' ),
					'desc' => __( 'The default is 21', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '21' 
                ),
				array(
					'name' => 'ftp_username',
					'label' => __( 'FTP Username', 'filetrip-plugin' ),
					'desc' => __( 'Please enter the username for FTP access', 'filetrip-plugin' ),
					'type' => 'text',
					'default' => '' 
                ),
				array(
					'name' => 'ftp_password',
					'label' => __( 'FTP password', 'filetrip-plugin' ),
					'desc' => __( 'Please enter the password for FTP access', 'filetrip-plugin' ),
					'type' => 'password',
					'default' => '' 
                )
			);

            $fields[Filetrip_FTP_Setting_page::$ftp_settings_slug] = $channelSettings;

            return $fields;
        }

        function print_html_in_page_header()
        {
            if(!Filetrip_FTP_Utility::check_if_ftp_disabled() && Filetrip_FTP::is_ftp_active())
            {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
                <p><strong>Your FTP channel is active.</strong></p>
                </div>
                <?php
            }

            if(!Filetrip_FTP_Utility::check_if_ftp_disabled() && !Filetrip_FTP::is_ftp_active())
            {
                ?>
                <div class="error settings-error notice is-dismissible"> 
                <b>Please fill correct FTP information to activate your FTP channel.</b>
                <br><b>Notice</b>: Several incorrect attempts might block your IP address
                </div>
                <?php
            }
        }
    }

}