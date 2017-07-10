<?php

/*
 * This calss should provide an abstraction for database record handling
 */


class Filetrip_Distributor
{
  private $_registered_channels;
  private $_file_distribution_type;
  
  public function __construct() 
  {

    add_action( 'admin_enqueue_scripts', array($this ,'distributer_admin_enqueue') );
    add_action('wp_ajax_arfaly_distribute_files', array( $this, 'ajax_distribute_files' ) );
    add_action( 'admin_print_scripts', array( $this, 'print_admin_script') );
    add_action('load-upload.php', array( $this, 'custom_filetrip_bulk_action'));
 
    // Add arfaly upload manage menu
    add_action('admin_menu', array($this, 'add_menu_items'));
  }

  /**
  * Process Filetrip channel bulk forwarding actions
  */
  function custom_filetrip_bulk_action() {
 
    if ( !isset( $_REQUEST['detached'] ) ) {

      // get the action
      $wp_list_table = _get_list_table('WP_Media_List_Table');  
      $action = $wp_list_table->current_action();
    
      if(!isset($action) || $action == ''){
        return;
      }

      // Apply filter to retrieve active channels 
      $registeredChannels = array();
      $registeredChannels = apply_filters('itf/filetrip/filter/register/channels', $registeredChannels);

      $this->_registered_channels = $registeredChannels;

      // If action is not directed for Filetrip. Just exit();
      if(!array_key_exists($action, $registeredChannels)){
        return;
      }
    
      // security check
      // check_admin_referer('bulk-media'); 
    
      // ...
      // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
      if(isset($_REQUEST['media'])) {
        $post_ids = array_map('intval', $_REQUEST['media']);
      }
      
      if(empty($post_ids)) return;

      // Initiate $redirect_page link
      $redirect_page = admin_url( Filetrip_Constants::FILETRIP_DISTRIBUTOR_PAGE );

      //$pagenum = $wp_list_table->get_pagenum();
      $redirect_args = array(
        'channel' => $action, 
        'source' => Filetrip_Constants::Transfer_Type('media')
        );
      $redirect_page = add_query_arg( $redirect_args, $redirect_page );

      // Add media list to the query
      $mediaArray = array();
      foreach($post_ids as $key=>$value)
      {
        $mediaArray['media['.$key.']'] = $value;
      }

      $redirect_page = add_query_arg( $mediaArray, $redirect_page );

      // 4. Redirect client
      wp_safe_redirect($redirect_page);
    
      exit();
    }
  }

  function add_menu_items()
  {
    add_submenu_page(null, 'Distribution Center', Filetrip_Constants::PLUGIN_NAME.' File Distributer', 'edit_posts', Filetrip_Constants::POST_TYPE .'_files_distributor', array($this, 'arfaly_render_files_distributor'));
  }

  function distributer_admin_enqueue()
  {
    if(isset($_GET['page']) && $_GET['page'] == 'filetrip_files_distributor')
    {
      wp_enqueue_style( 'filetrip_distributor_style', ITECH_FILETRIP_PLUGIN_URL . '/assets/css/distributor-admin.css' );

      // Register the script first.
      wp_enqueue_script( 'filetrip_event_source_js', ITECH_FILETRIP_PLUGIN_URL . '/assets/js/event-source.js', array('jquery'), Filetrip_Constants::VERSION, true );
      wp_enqueue_script( 'filetrip_backend_uploader_js', ITECH_FILETRIP_PLUGIN_URL . '/assets/js/filetrip-backend-uploader.js', array('jquery', 'filetrip_event_source_js'), Filetrip_Constants::VERSION, true );
      wp_register_script( 'filetrip_distributor_js', ITECH_FILETRIP_PLUGIN_URL . '/assets/js/filetrip-distributor.js' );
      
      // Apply filter to retrieve active channels 
      $registeredChannels = array();
      $registeredChannels = apply_filters('itf/filetrip/filter/register/channels', $registeredChannels);

      $this->_registered_channels = $registeredChannels;

      switch($_GET['source']){
        case Filetrip_Constants::Transfer_Type('media'): case Filetrip_Constants::Transfer_Type('forward'):
            
            // Extract media information
            $media_info = array();

            foreach((array)$_GET['media'] as $mediaID){
              $attID = intval($mediaID);
              $media_info[] = $this->extract_upload_file_information($attID, $_GET['source']);
            }

            $translation_array['mediaList'] = $media_info;
            $translation_array['registeredChannels'] = $registeredChannels;
            $translation_array['source'] = $_GET['source'];
            $translation_array['ajax_url'] = admin_url( 'admin-ajax.php' );
            $translation_array['back_url'] = admin_url( Filetrip_Constants::MAIN_MENU_PARENT_SLUG.'&page=filetrip_manage_list' );
          break;


        case Filetrip_Constants::Transfer_Type('backup'):

          if(!isset($_GET['channel'])){ 
            wp_safe_redirect( wp_get_referer() );
            exit;
          }
          $file_size = $_GET['file_size'];
          $friendly_size = Filetrip_Channel_Utility::format_bytes($file_size, 0);

          $translation_array['file_size_friendly'] = $friendly_size['digits'].' '.$friendly_size['unit'];
          $translation_array['source'] = Filetrip_Constants::Transfer_Type('backup');
          $translation_array['channel'] = $_GET['channel'];
          $translation_array['file_size'] = $_GET['file_size'];
          $translation_array['file_path'] = $_GET['backup_archive'];
          $translation_array['registeredChannels'] = $registeredChannels;
          $translation_array['ajax_url'] = admin_url( 'admin-ajax.php' );
          $translation_array['back_url'] = admin_url( Filetrip_Constants::MAIN_MENU_PARENT_SLUG.'&page=filetrip_manage_list' );
        break;

        default:
        break;
      }
      
      wp_localize_script( 'filetrip_distributor_js', 'dist_tasks_options', $translation_array );
      wp_enqueue_script('filetrip_distributor_js', false, array('jquery', 'wp-util', 'filetrip_transfer_card_tmpl_js', 'filetrip_backend_uploader_js' ), Filetrip_Constants::VERSION, true);
    }
  }

  function print_admin_script()
  {
    include_once ITECH_FILETRIP_LIB_EXTENSION_DIR . "/classes/templates/filetrip-transfer-card.php";
  }

  /**
  * arfaly_render_files_distributor
  *
  * Admin page of the main Filetrip distributor
  *
  * @return (null)
  */
  function arfaly_render_files_distributor()
  {
      echo arfaly_get_icon('logo-text','','', Filetrip_Constants::ITF_WEBSITE_LINK);
      ?>
      
      <h2>Filetrip transmission process has just started:</h2>
      <div class="update-nag"><b>Notice</b>: The upload process might take a long time to be completed, and it depends mainly on the size and the quantity of the transport package.<br>
      However, the uploading process should be maintained in your server even if you close the browser page.</div>
      <br><br>
      <div>

        <div class="filetrip-distribution-navigation--menu">
          <div class="filetrip-distribution--layout-menu">
            <h4>Change Layout</h4>
            <span class="toggler active" data-toggle="grid"><span class="entypo-layout"></span></span>
            <span class="toggler" data-toggle="list"><span class="entypo-list"></span></span>
          </div>
          
          <?php if($_GET['source']==Filetrip_Constants::Transfer_Type('forward')) { ?>
            <!-- Enable Filters: Only if the source was set as 'forwarder' -->
            <div class="filetrip-distribution--category-buttons">
              <h4>Filter Uploads</h4>
              <span class="button button-selector" onClick="FiletripFilter('all')">All</span>
              <?php
                foreach($this->_registered_channels as $key=>$channel){
                  // Render the current channel filter button
                  echo '<span class="button button-selector" onClick="FiletripFilter(\''.$key.'-button\')">'.$channel['channel_name'].'</span>';
                }
              ?>
            </div>
          <?php } ?>

        </div>

        <ul class="surveys grid" id="filetrip-transfer-cards">
          
        </ul>

      </div>
    <?php
  }

  /**
  * extract_upload_file_information
  *
  * Extract JSON format information for a specific upload
  *
  * @param (int) ($att_id) The attachment ID of the uploaded file
  * @param (int) ($source) Where the request is coming from {Media, Forwarder, Backup}
  * @return (string) ($json_data)
  */
  function extract_upload_file_information($att_id, $source)
  {
    /**
    * extract_upload_file_information
    */
    $json_data = array();
    
    // Check attachment info
    $att_info = get_post($att_id);
    if($att_info==null)
      return false;
    
    $json_data['title'] = $att_info->post_title;
    $json_data['url'] = wp_get_attachment_url($att_id);
    $json_data['id'] = $att_id;

    $att_url = wp_get_attachment_url($att_id);
    $ext = pathinfo($att_url, PATHINFO_EXTENSION);

    $json_data['ext'] = $ext;
    $json_data['approved'] = ($att_info->post_status == \Filetrip_Constants::POST_STATUS)?false:true;
    $json_data['mime'] = $att_info->post_mime_type;
    $json_data['datetime'] = strtotime($att_info->post_date);
    $json_data['file_size'] = @filesize( get_attached_file( $att_id ) );

    $friendly_size = Filetrip_Channel_Utility::format_bytes($json_data['file_size'], 0);
    $json_data['file_size_friendly'] = $friendly_size['digits'].' '.$friendly_size['unit'];
    
    /**
    * Determine upload destination
    */ 

    switch($source){
      case Filetrip_Constants::Transfer_Type('media'):
        
        $json_data['source'] = Filetrip_Constants::Transfer_Type('media');
        $json_data['forwarders']['source'] = 'media';
        $json_data['forwarders']['single_channel'] = isset($_GET['channel'])?$_GET['channel']:''; 
        break;

      case Filetrip_Constants::Transfer_Type('backup'):

        $json_data['source'] = Filetrip_Constants::Transfer_Type('backup');

        break;

      case Filetrip_Constants::Transfer_Type('forward'):

        $json_data['source'] = Filetrip_Constants::Transfer_Type('forward');
        // Apply filter to retrieve active channels 
        $registeredForwarder = array();
        $registeredForwarder = apply_filters('itf/filetrip/filter/channels/media/dest', $registeredForwarder, $att_id);
        $json_data['forwarders'] = $registeredForwarder;
      break;

      default:
      break;
    }

    return ($json_data);
            
  }
  
}

$filetripDistributor = new Filetrip_Distributor();

?>
