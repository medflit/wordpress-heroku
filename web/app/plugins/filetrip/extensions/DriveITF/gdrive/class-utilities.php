<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Filetrip_Drive_Utility
{
  
  	function __construct() {
	  }

    static function check_if_drive_disabled()
    {
      // If backup is disabled
      $tempSettings = get_option( Filetrip_Uploader::$settings_slug, false );
      if($tempSettings!=false && isset($tempSettings['disable_drive']) && 'on' == $tempSettings['disable_drive'])
      {
        // Cancel and do nothing
        return true;
      }else{
        return false;
      }
    }

    static function build_select_folder_widget($args, $refObj, $cmb = false)
    {
        // Check which input target should be cleared. Setting widget is different than post folder selection widget
        $switchClearTarget = '_filetrip_drive_folder';

        if( isset($_GET['page']) && $_GET['page'] == 'filetrip_settings')
        {
          $switchClearTarget = 'filetrip_settings\\\\[drive_folder\\\\]';
        }

        if($cmb == false)
        {
            // Parse information from args in case we are calling from page_settings library 
            $value = esc_attr( $refObj->get_option( $args['id'], $args['section'], $args['std'] ) );
            $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        }
        
        if(self::check_if_drive_disabled())
        {
            echo '<span style="color:red">Google Drive has been disabled.</span> Please enable it so you can select folder destination';
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

        if(!Filetrip_Google_Drive::is_google_drive_active())
        {
          echo '<span style="color:red">Google Drive is still not active.</span> Activate here: <a href='.admin_url(Filetrip_Constants::OPTION_PAGE).'>link</a>';
          return;
        }

        if($cmb == false){
          $html = sprintf( '<input style="margin-bottom: 10px" type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" readonly/>', $size, $args['section'], $args['id'], $value );
          $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
          echo $html;
        }else{
          echo $refObj->input( array( 'type' => 'text' ) );
        }

        // Google Drive should be active now
        add_thickbox();  
        ?>
          <div id="drive-folder-list" style="display:none;text-align:center">
          <h2><?php echo arfaly_get_icon('google-drive', ''); ?>
            Your Google Drive folder list:
          </h2>
            <div id="arfaly_drive_folder_content">
            </div>
            <script>
              arfaly_drive_get_folder_list('');
            </script>
          </div>
          <br>
          <?php echo arfaly_get_icon('google-drive', ''); ?>
          <a href="#TB_inline?width=600&height=500&inlineId=drive-folder-list" class="thickbox button">Select Folder</a>
          <button onClick="javascript:clear_drive_folder_selection('<?php echo $switchClearTarget; ?>')" class="thickbox button">Clear</button>
        <?php
    }

}