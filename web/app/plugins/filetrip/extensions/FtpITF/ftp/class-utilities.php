<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Filetrip_FTP_Utility
{
  
  	function __construct() {
	  }

    static function check_if_ftp_disabled()
    {
      // If backup is disabled
      $tempSettings = get_option( Filetrip_Uploader::$settings_slug, false );
      if($tempSettings!=false && isset($tempSettings['disable_ftp']) && 'on' == $tempSettings['disable_ftp'])
      {
        // Cancel and do nothing
        return true;
      }else{
        return false;
      }
    }

    static function build_select_folder_widget($args, $refObj, $cmb = false)
    {
        $switchClearTarget = '_filetrip_ftp_folder';

        if( isset($_GET['page']) && $_GET['page'] == 'filetrip_settings')
        {
          $switchClearTarget = 'filetrip_settings\\\\[ftp_folder\\\\]';
        }

        if($cmb == false)
        {
            $value = esc_attr( $refObj->get_option( $args['id'], $args['section'], $args['std'] ) );
            $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        }

        if(Filetrip_FTP_Utility::check_if_ftp_disabled())
        {
            echo '<span style="color:red">FTP has been disabled.</span> Please enable it so you can select folder destination';
            if($cmb == false){
              // Add hidden field to retain the value of the select box
              $html = sprintf( '<input style="margin-bottom: 10px" type="hidden" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" readonly/>', $size, $args['section'], $args['id'], $value );
              echo $html;
            }else{
              // Add hidden field to retain the value of the select box
              echo $refObj->input( array( 'type' => 'hidden' ) );
            }
            return;
        }

        if(!Filetrip_FTP::is_ftp_active())
        {
          echo '<span style="color:red">FTP is still not active.</span> Activate here: <a href='.admin_url(Filetrip_Constants::OPTION_PAGE).'>link</a>';
          return;
        }

        if($cmb == false){
          $html = sprintf( '<input style="margin-bottom: 10px" type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" readonly/>', $size, $args['section'], $args['id'], $value );
          $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
          echo $html;
        }else{
          echo $refObj->input( array( 'type' => 'text' ) );
        }

        // FTP should be active now
        add_thickbox();  
        ?>
          <div id="ftp-folder-list" style="display:none;text-align:center">
          <h2><?php echo arfaly_get_icon('ftp', ''); ?>
            Your FTP folder list:
          </h2>
            <div id="arfaly_ftp_folder_content">
            </div>
            <script>
              arfaly_ftp_get_folder_list('/');
            </script>
          </div><br>
          <?php echo arfaly_get_icon('ftp', ''); ?>
          <a href="#TB_inline?width=600&height=500&inlineId=ftp-folder-list" class="thickbox button">Select Folder</a>
          <button href="" onClick="javascript:clear_dropbox_folder_selection('<?php echo $switchClearTarget ;?>')" class="thickbox button">Clear</button>
        <?php
    }

}