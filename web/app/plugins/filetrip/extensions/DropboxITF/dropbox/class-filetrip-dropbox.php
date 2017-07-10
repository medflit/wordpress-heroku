<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!class_exists('Filetrip_Dropbox')){

// Load setting page 
require_once __DIR__ . '/_setting_page.php';

  class Filetrip_Dropbox
  {
    private $app_key;
    private $app_secret;
    private $app_info;
    private $web_auth;

    static public $dropbox_csrf_token_slug = 'dropbox_token';
    static public $dropbox_user_id_slug = 'dropbox_user_id';
    static public $dropbox_box_auth_code_slug = 'authorization_code';
    static public $dropbox_active_slug = 'dropbox_active';
    static public $dropbox_secret_slug = 'dropbox_app_secret';
    static public $dropbox_key_slug = 'dropbox_app_key';
    static public $dropbox_resumable_uploads_slug = 'dropbox_resumable_uploads';
    static public $dropbox_client_id = 'Filetrip/1.0'; 
    
    public $settings = array();
    
    public function __construct($setting) {

      // Exit if it is disabled
      if(Filetrip_Dropbox_Utility::check_if_dropbox_disabled())
      {
        return;
      }
      
      if(!isset($setting[Filetrip_Dropbox::$dropbox_key_slug]) || !isset($setting[Filetrip_Dropbox::$dropbox_secret_slug])
              || strlen($setting[Filetrip_Dropbox::$dropbox_key_slug])<=5 || strlen($setting[Filetrip_Dropbox::$dropbox_secret_slug])<=5)
      {
        return;
      }
      
      // AJAX Hooks
      add_action('wp_ajax_get_dropbox_folder_list',array( $this, 'get_folder_list_html'));
        
      // If demo version, don't do anything
      if(Filetrip_Constants::DEMO_MODE)
        return;
      
      $this->app_key = trim($setting[Filetrip_Dropbox::$dropbox_key_slug], " \t\n\r\0");
      $this->app_secret = trim($setting[Filetrip_Dropbox::$dropbox_secret_slug], " \t\n\r\0");   
      
      try {
        $this->app_info = new DropboxITF\AppInfo($this->app_key, $this->app_secret);
        
        $this->web_auth = new DropboxITF\WebAuthNoRedirect($this->app_info, Filetrip_Dropbox::$dropbox_client_id);
      }
      catch (\Exception $ex) {
        do_action('itech_error_caught', "Filetrip: Error in input formatting");
        Filetrip_Dropbox::deactivate_dropbox();
        return;
      }

      if(!Filetrip_Dropbox::is_dropbox_active() && isset($setting[Filetrip_Dropbox::$dropbox_box_auth_code_slug]) && strlen($setting[Filetrip_Dropbox::$dropbox_box_auth_code_slug])>5)
      {
        try {
          list($accessToken, $userId) = $this->web_auth->finish($setting[Filetrip_Dropbox::$dropbox_box_auth_code_slug]);
        }
        catch (\Exception $ex) {
          do_action('itech_error_caught', "Filetrip: Error communicating with Dropbox API: " . $ex->getMessage() . "\n");
          Filetrip_Dropbox::deactivate_dropbox();
          return;
        }
        $setting[Filetrip_Dropbox::$dropbox_csrf_token_slug] = $accessToken;
        $setting[Filetrip_Dropbox::$dropbox_user_id_slug] = $userId;
        $setting[Filetrip_Dropbox::$dropbox_active_slug] = true;
        Filetrip_Dropbox::update_dropbox_settings( $setting );
      }
      
    }
    
    public function auth_start()
    {
      if(!Filetrip_Dropbox::is_dropbox_active() && strlen($this->app_key)>5 && strlen($this->app_secret)>5)
      {
        $this->auth_url = $this->web_auth->start();
        header('location:'.$this->auth_url);
        exit();
      }else{
        do_action('itech_error_caught', 'Please fill designated forms with correct values (Dropbox)');
        // Handle error
      }
    }
    
    static public function is_dropbox_active()
    {
      $temp_setting = Filetrip_Dropbox::get_dropbox_settings();
      if(isset($temp_setting[Filetrip_Dropbox::$dropbox_active_slug]) && $temp_setting[Filetrip_Dropbox::$dropbox_active_slug])
      {
        return true;
      }else
      {
        return false;
      }
    }
    
    static public function deactivate_dropbox()
    {
      $temp_setting = Filetrip_Dropbox::get_dropbox_settings();
      unset($temp_setting[Filetrip_Dropbox::$dropbox_box_auth_code_slug]);
      unset($temp_setting[Filetrip_Dropbox::$dropbox_active_slug]);
      
      Filetrip_Dropbox::update_dropbox_settings($temp_setting);
    }
    
    static public function get_dropbox_settings()
    {
      $dropbox_settings = (array)get_option(Filetrip_Dropbox_Setting_page::$dropbox_settings_slug);
      
      return $dropbox_settings;
    }
    
    static public function update_dropbox_settings($new_setting)
    {
        update_option( Filetrip_Dropbox_Setting_page::$dropbox_settings_slug, $new_setting );
    }
    
    static public function get_folder_list($path = '')
    {
      $dropbox_setting = Filetrip_Dropbox::get_dropbox_settings();
      
      $client = new DropboxITF\Client($dropbox_setting[Filetrip_Dropbox::$dropbox_csrf_token_slug],Filetrip_Dropbox::$dropbox_client_id );
      
      try{ 
        $files = $client->getFolderList($path);

        return $files;
      }catch(\Exception $ex)
      {
        do_action('itech_error_caught', "Error communicating with Dropbox API: " . $ex->getMessage() . "\n");
        // Filetrip_Dropbox::deactivate_dropbox();
        exit();
      }
    }

    /**
	  * @param array 
	  *      This function will be called as Server Side Event process, so event-stream is expected to open,
    *      and progress information should be sent back to the client side to notify users
	  */
    public static function resumable_file_upload($destination_path , $file_uri, $filesize, $userTaggedFolder = false, $username = 'Guest', $sse_enabled = true)
    {
      
      set_time_limit(0);
      session_write_close();
      ignore_user_abort( true );

      if(!Filetrip_Dropbox::is_dropbox_active())
      {
        return false;
      }
      
      // Upload ID to resume or null (new chunked upload)
      $uploadID = null;
      // Resume the upload
      $offset = null;
      $result = null;
      $bytes_read = 0;
      $data = null;
      $chunk_status = false;
      
      $dropbox_setting = Filetrip_Dropbox::get_dropbox_settings();
      
      $client = new DropboxITF\Client($dropbox_setting[Filetrip_Dropbox::$dropbox_csrf_token_slug],Filetrip_Dropbox::$dropbox_client_id );
      
      list($data,$bytes_read) = Filetrip_Channel_Utility::read_chunk_from_file($file_uri, 0);
      
      $filename = basename($file_uri);
      
      try{
        $uploadID = $client->chunkedUploadStart($data);
        
        $offset = $bytes_read;

        while ($data) {
    
          list($data,$bytes_read) = Filetrip_Channel_Utility::read_chunk_from_file($file_uri, $offset);
          if($data == false)
            break;
          
          $chunk_status = $client->chunkedUploadContinue($uploadID, $offset, $data);
          
          if($chunk_status==false)
          {
            do_action('itech_error_caught', 'Dropbox upload job probably expired, or ID is invalid');
            return false;
          }
          // Passing wrong offset, rewind with correct offset
          if(is_int($chunk_status))
          {
            $offset = $chunk_status;
            continue;
          }

          $offset = intval($offset + $bytes_read);

          if($sse_enabled){
            /**
            *  Construct SSE message and echo it to the client
            */
            $data = array(
              'percentage' => ($filesize!=0)?intval($offset/$filesize*100):0,
              'bytes' => $offset
            );
            Filetrip_Channel_Utility::sse_send_message($uploadID, json_encode($data,JSON_UNESCAPED_SLASHES));
          }
        }

        // Check if sub-folder option is enabled or not ?
        if($userTaggedFolder){
          $result = $client->chunkedUploadFinish($uploadID, $destination_path.'/'.$username.'/'.$filename, $offset, DropboxITF\WriteMode::add());
        }else{
          $result = $client->chunkedUploadFinish($uploadID, $destination_path.'/'.$filename, $offset, DropboxITF\WriteMode::add());
        }
        
      }catch(\Exception $exp)
      {
        error_log("Filetrip: " . $exp->getMessage());
         
        $shortExpMsg = substr($exp->getMessage(), 0, Filetrip_Constants::ERROR_MESSAGE_MAX_LENGHT);

        if($sse_enabled){
          Filetrip_Channel_Utility::sse_send_message($uploadID, "Dropbox Error: ".$shortExpMsg, "error");
        }

        return false;
      }
      
      if($sse_enabled){
        /**
        *  Construct SSE message and echo it to the client
        */
        Filetrip_Channel_Utility::sse_send_message($offset, "finished", "finished");
        return true;
      }
    }
    
    // Called by ajax from meta arfaly's posts
    public function get_folder_list_html()
    {
      
      if(Filetrip_Constants::DEMO_MODE)
      {
          echo $this->return_dummy_folder_list();
      }
      
      $switch = '_filetrip_dropbox_folder';
      if( isset($_POST['page']) && $_POST['page'] == 'filetrip_settings')
      {
        $switch = 'filetrip_settings\\\\[dropbox_folder\\\\]';
      }
      
      // Sanitize the whole input
      $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $path = $_POST['path'];
      
      $files = Filetrip_Dropbox::get_folder_list($path);
      
      // Build step back one level path name
      $step_back_path = dirname($path);
      if($path == '/' || $path == '\\' || $step_back_path == '/' || $step_back_path == '\\'){
        $step_back_path = '';
      }
      
      $folder_icon = '<img src="'.ITECH_FILETRIP_PLUGIN_URL.'/assets/img/dropbox-folder.png" width="30" height="30">';
      ob_start();
      ?>
              <div class="arfaly-dp-folder-loading spinner"></div>
              <h3>Path: <b><?php echo ($path=='')?'/root':$path; ?></b></h3>
              <?php if ( empty( $files ) ) : ?>
              <?php echo _e( 'No files in this folder', 'arfaly_plugin' ); ?>
              <?php else : ?>
              <ul class="arfaly-file-listing">
                <?php if($path != ''){ ?>
                  <li>
                    <?php echo $folder_icon;?>
                    <a href="javaScript: void(0);" onclick="javascript:arfaly_dropbox_get_folder_list('<?php echo $step_back_path; ?>')">../</a>
                  </li>
                  <?php }
                  foreach( $files['entries'] as $file ) : ?>
                      <?php if ( $file['.tag'] == 'folder' ) : ?>
                          <li class="arfaly-folder">
                            <a href="javaScript: void(0);" onclick="javascript:arfaly_update_dropbox_folder('<?php echo $file['path_lower']; ?>', '<?php echo $switch; ?>')" class="button">Select</a>
                              <?php echo $folder_icon;?>
                            <a href="javaScript: void(0);" onclick="javascript:arfaly_dropbox_get_folder_list('<?php echo $file['path_lower']; ?>')"><?php echo $file['name'] ;?></a>
                          </li>
                      <?php endif; ?>
                  <?php endforeach; ?>
                  <?php foreach( $files['entries'] as $file ) : ?>
                      <?php if ( !$file['.tag'] == 'folder' ) : ?>
                          <li class="<?php echo $file['icon']; ?>">
                              <a href="#"><?php echo basename( $file['path_lower'] ) ;?></a> (File) (<?php echo ''; ?>)
                          </li>
                      <?php endif; ?>
                  <?php endforeach; ?>
              </ul>
          <?php endif; ?>
        </div>
      <?php
      
      $html = ob_get_contents();
      ob_end_clean();
    
      echo $html;
      
      die();
    }
    
    function return_dummy_folder_list()
    {
      ob_start();
      ?>
              
        <div class="arfaly-dp-folder-loading spinner"></div>
            <h3>Path: <b>/</b></h3>
            <ul class="arfaly-file-listing">
              <li class="arfaly-folder">
                <a href="javaScript: void(0);" onclick="javascript:arfaly_update_dropbox_folder('/Assets', 'filetrip_settings\\[dropbox_folder\\]')" class="button">Select</a>
                <img src="http://localhost/freelance/wp-content/plugins/filetrip-plugin/assets/img/dropbox-folder.png" width="30" height="30">
                <a href="javaScript: void(0);" onclick="javascript:arfaly_dropbox_get_folder_list('/itechflare Assets')">Assets</a>
              </li>
              <li class="arfaly-folder">
                <a href="javaScript: void(0);" onclick="javascript:arfaly_update_dropbox_folder('/Multimedia', 'filetrip_settings\\[dropbox_folder\\]')" class="button">Select</a>
                <img src="http://localhost/freelance/wp-content/plugins/filetrip-plugin/assets/img/dropbox-folder.png" width="30" height="30">
                <a href="javaScript: void(0);" onclick="javascript:arfaly_dropbox_get_folder_list('/Multimedia')">Multimedia</a>
              </li>
                <a href="#">logo-design.jpg</a> (File) (30 KB)
              </li>
        </ul>
                
      </div>
        
      <?php
      
      $html = ob_get_contents();
      ob_end_clean();
    
      echo $html;
      
      die();
    }
    
  }

  // Initiate Dropbox Setting Page
  $filetripDropboxSettingPage = new Filetrip_Dropbox_Setting_page();

}

?>
