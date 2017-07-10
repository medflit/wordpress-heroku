<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if(!class_exists('Filetrip_Google_Drive')){

// Load setting page 
require_once __DIR__ . '/_setting_page.php';

  class Filetrip_Google_Drive
  {
    private $app_id;
    private $app_secret;
    private $app_info;
    private $client;

    static public $google_drive_csrf_token_slug = 'google_drive_token';
    static public $google_drive_refresh_token_slug = 'refresh_token';
    static public $google_drive_auth_code_slug = 'authorization_code';
    static public $google_drive_active_slug = 'google_drive_active';
    static public $google_drive_client_secret_slug = 'google_client_secret';
    static public $google_drive_client_id_slug = 'google_client_id';
    static public $google_redirect_url_slug = 'google_redirect_url';
    static public $google_drive_app_id = 'Filetrip/1.0'; 
    
    public $settings = array();
    
    public function __construct($setting) {

      // Exit if it is disabled
      if(Filetrip_Drive_Utility::check_if_drive_disabled())
      {
        return;
      }
      
      if(!isset($setting[Filetrip_Google_Drive::$google_drive_client_id_slug]) || !isset($setting[Filetrip_Google_Drive::$google_drive_client_secret_slug])
              || !isset($setting[Filetrip_Google_Drive::$google_redirect_url_slug])
              || strlen($setting[Filetrip_Google_Drive::$google_drive_client_id_slug])<=5 || strlen($setting[Filetrip_Google_Drive::$google_drive_client_secret_slug])<=5)
      {
        return;
      }
      
      // Hooks
      add_action('wp_ajax_get_drive_folder_list',array( $this, 'get_folder_list_html'));

      try{
        // Initialization
        $this->client = Filetrip_Google_Drive::build_client_object($setting);
        
        // Check if token is expired, and if it's not update client object
        if(Filetrip_Google_Drive::is_google_drive_active())
        {
          $this->is_token_expired($setting);
        }
        
        if(isset($_GET['code']) && isset($_GET['page']) && $_GET['page']==(Filetrip_Constants::POST_TYPE.'_settings'))
        {
          $setting[Filetrip_Google_Drive::$google_drive_auth_code_slug] = $_GET['code'];
          $this->update_google_drive_settings($setting);
        }
        
        // Authniticate using code and get token
        if(!Filetrip_Google_Drive::is_google_drive_active() && isset($setting[Filetrip_Google_Drive::$google_drive_auth_code_slug]))
        {
          $this->authenticate($setting[Filetrip_Google_Drive::$google_drive_auth_code_slug], $setting);
        }

      }catch(\Exception $e)
      {
        Filetrip_Google_Drive::deactivate_google_drive();
        do_action('itech_error_caught', $e->getMessage());
        return;
      }
      
    }
    
    public static function build_client_object($setting)
    {
      $client = new Google_Client();
      // Sanitize fields
      $clientId = trim($setting[Filetrip_Google_Drive::$google_drive_client_id_slug], " \t\n\r\0");
      $clientSecret = trim($setting[Filetrip_Google_Drive::$google_drive_client_secret_slug], " \t\n\r\0");
      $redirectUri = trim($setting[Filetrip_Google_Drive::$google_redirect_url_slug], " \t\n\r\0");

      // Get your credentials from the console
      $client->setClientId($clientId);
      $client->setClientSecret($clientSecret);
      $client->setRedirectUri($redirectUri);
      $client->setApplicationName(Filetrip_Google_Drive::$google_drive_app_id);
      $client->setScopes(array(
          'https://www.googleapis.com/auth/drive',
          'https://www.googleapis.com/auth/drive.appdata'));
      $client->setAccessType('offline');
      $client->setApprovalPrompt('force');
      
      $token = isset($setting[Filetrip_Google_Drive::$google_drive_csrf_token_slug])?$setting[Filetrip_Google_Drive::$google_drive_csrf_token_slug]:'';
      
      if($token!='')
        $client->setAccessToken($token);
          
      return $client;
    }
    
    private function is_token_expired($setting)
    {
      $token = isset($setting[Filetrip_Google_Drive::$google_drive_csrf_token_slug])?$setting[Filetrip_Google_Drive::$google_drive_csrf_token_slug]:'';
      
      // If token is empty
      if($token == '')
      {
        return true;
      }
      
      $this->client->setAccessToken($token);
      
      // If expired, start activation process from scratch
      if($this->client->isAccessTokenExpired())
      {
        // echo 'expired';
        if(isset($setting[Filetrip_Google_Drive::$google_drive_refresh_token_slug]) && $setting[Filetrip_Google_Drive::$google_drive_refresh_token_slug]!=''){        
          try{
            $this->client->refreshToken($setting[Filetrip_Google_Drive::$google_drive_refresh_token_slug]); 
            $new_token = $this->client->getAccessToken();
            // Finalize and store important token handshake information 
            $this->save_new_token($new_token, $setting);
          }catch(Exception $e )
          {
            Filetrip_Google_Drive::deactivate_google_drive();
            do_action('itech_error_caught', $e->getMessage());
            return;
          }
        }else{
          // echo 'is_token_expired function ....';
          Filetrip_Google_Drive::deactivate_google_drive();
        }
        return true;
      }else{
        return false;
      }
    }
    
    private function authenticate($code, $setting)
    {
        try {
          // Exchange authorization code for access token
          $accessToken = $this->client->authenticate($code);
          $this->client->setAccessToken($accessToken);
        }
        catch (\Exception $ex) {
          do_action('itech_error_caught', $ex->getMessage());
          Filetrip_Google_Drive::deactivate_google_drive();
          return;
        }
        // Finalize and store important token handshake information 
        $this->save_new_token($accessToken, $setting);
    }
    
    private function save_new_token($accessToken, $setting)
    {
      // Finalize and store important token handshake information 
      $token = json_decode($accessToken);
      $setting[Filetrip_Google_Drive::$google_drive_csrf_token_slug] = $accessToken;
      $setting[Filetrip_Google_Drive::$google_drive_active_slug] = true;
      $setting[Filetrip_Google_Drive::$google_drive_refresh_token_slug] = isset($token->refresh_token)?$token->refresh_token:'';
      Filetrip_Google_Drive::update_google_drive_settings( $setting );
    }
    
    public function auth_start()
    {
      if(!Filetrip_Google_Drive::is_google_drive_active())
      {
        if(!is_object($this->client))
        {
          do_action('itech_error_caught', 'Please fill designated forms with correct values (Google Drive)');
          return;
        }
        
        $this->auth_url = $this->client->createAuthUrl();
        header('location:'.$this->auth_url);
        exit();
      }
    }
    
    static public function is_google_drive_active()
    {
      $temp_setting = Filetrip_Google_Drive::get_google_drive_settings();
      if(isset($temp_setting[Filetrip_Google_Drive::$google_drive_active_slug]) && $temp_setting[Filetrip_Google_Drive::$google_drive_active_slug])
      {
        return true;
      }else
      {
        return false;
      }
    }
    
    static public function deactivate_google_drive()
    {
      $temp_setting = Filetrip_Google_Drive::get_google_drive_settings();
      unset($temp_setting[Filetrip_Google_Drive::$google_drive_auth_code_slug]);
      unset($temp_setting[Filetrip_Google_Drive::$google_drive_active_slug]);
      unset($temp_setting[Filetrip_Google_Drive::$google_drive_csrf_token_slug]);
      
      Filetrip_Google_Drive::update_google_drive_settings($temp_setting);
    }
    
    static public function get_google_drive_settings()
    {
      $google_drive_settings = (array)get_option(Filetrip_Google_Drive_Setting_page::$google_settings_slug);
      
      return $google_drive_settings;
    }
    
    static public function update_google_drive_settings($new_setting)
    {
        update_option( Filetrip_Google_Drive_Setting_page::$google_settings_slug, $new_setting );
    }
    
    static public function get_file_info($fileId)
    {
      try 
      {
        $setting = Filetrip_Google_Drive::get_google_drive_settings();

        $client  = Filetrip_Google_Drive::build_client_object($setting);
        $service = new Google_Service_Drive($client);

        $file = $service->files->get($fileId);
      } catch (\Exception $e) {
        do_action('itech_error_caught', $e->getMessage());
      }
      
      return $file;
    }
    
    // Called by ajax from meta arfaly's posts
    public function get_folder_list_html()
    {
      // Sanitize the whole input
      $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $path = $_POST['path'];
      $root_title = 'root';
    
      $switch = '_filetrip_drive_folder';
      if( isset($_POST['page']) && $_POST['page'] == 'filetrip_settings')
      {
        $switch = 'filetrip_settings\\\\[drive_folder\\\\]';
      }
      
      $files = Filetrip_Google_Drive::get_folder_list($path);
      
      $first_file = '';
      // Get grand parent folder
      if(isset($files[0]))
        $first_file = Filetrip_Google_Drive::get_file_info($files[0]['modelData']['parents'][0]['id']);

      if($path != '')
      {
        $file_info = Filetrip_Google_Drive::get_file_info($path);
        $root_title = $file_info['title'];
      }
      
      ob_start();
      ?>
        <div class="arfaly-drive-folder-loading spinner"></div>
            <b>Notice:</b> If the screen is empty, this means you have no folders.
            <h3>Path: <b><?php echo $root_title; ?></b></h3>
            <?php if ( empty( $files ) ) : ?>
            <?php echo _e( 'No files in this folder', 'arfaly_plugin' ); ?>
            <?php else : ?>
            <ul class="arfaly-file-listing">
              <?php 
              if( $first_file!='' && !empty($first_file['modelData']['parents'])){ ?>
                <li class="arfaly-drive-get-back">
                  <a href="javaScript: void(0);" onclick="javascript:arfaly_drive_get_folder_list('<?php echo $first_file['modelData']['parents'][0]['id']; ?>')">(Back) ../</a>
                </li>
                <?php 
              }
              foreach( $files as $file ) : ?>
                    <?php if ( $file['mimeType'] == 'application/vnd.google-apps.folder' ) : ?>
                        <li class="arfaly-folder-drive">
                          <a href="javaScript: void(0);" onclick="javascript:arfaly_update_drive_folder('<?php echo '('.$file['title'].') - '.$file['id']; ?>', '<?php echo $switch; ?>')" class="button">Select</a>
                            <?php echo '<img src="'.$file['iconLink'].'">'; ?>
                          <a href="javaScript: void(0);" onclick="javascript:arfaly_drive_get_folder_list('<?php echo $file['id']; ?>')"><?php echo $file['title'] ;?></a>
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
    
    static public function get_folder_list($query = '')
    { 
      $result = array();
      $pageToken = NULL;

      do {
        try {
          $parameters = array();
          if ($pageToken) {
            $parameters['pageToken'] = $pageToken;
          } else {
            if($query == '')
            {
              $query = "'root' in parents";
              $parameters['q'] = $query;
            }else{
              $parameters['q'] = '"'.$query.'" in parents';
            }
          }
          $setting = Filetrip_Google_Drive::get_google_drive_settings();
      
          $client = Filetrip_Google_Drive::build_client_object($setting);
          $service = new Google_Service_Drive($client);
      
          $files = $service->files->listFiles($parameters);

          $result = array_merge($result, $files->getItems());
          $pageToken = $files->getNextPageToken();
        } catch (Exception $e) {
          do_action('itech_error_caught', $e->getMessage());
          $pageToken = NULL;
        }
      } while ($pageToken);
      //var_dump($result[0]);
      return $result;
    }
    
    public static function resumable_file_upload($filesize, $title, $description, $mimeType, $filepath, $folder_id = '', $sse_enabled = true)
    {

      set_time_limit(0);
      session_write_close();
      ignore_user_abort( true );
      
      try{
        $setting = Filetrip_Google_Drive::get_google_drive_settings();

        $client = Filetrip_Google_Drive::build_client_object($setting);
        $service = new Google_Service_Drive($client);

        $file = new Google_Service_Drive_DriveFile();
        $file->setTitle($title);
        $file->setDescription($description);

        if($folder_id!=''){
          $parent = new Google_Service_Drive_ParentReference();
          $parent->setId($folder_id);
          $file->setParents(array($parent));
        }

        // Call the API with the media upload, defer so it doesn't immediately return.
        $client->setDefer(true);
        $request = $service->files->insert($file);
        $byteCounter = 0;

        // Create a media file upload to represent our upload process.
        $media = new Google_Http_MediaFileUpload(
          $client,
          $request,
          $mimeType,
          null,
          true,
          Filetrip_Channel_Utility::$CHUNK_SIZE
        );
        $media->setFileSize(filesize($filepath));
        
        // Upload the various chunks. $status will be false until the process is
        // complete.
        $status = false;
        $handle = fopen($filepath, "rb");
        while (!$status && !feof($handle)) {
          $chunk = fread($handle, Filetrip_Channel_Utility::$CHUNK_SIZE);

          try {
            $status = $media->nextChunk($chunk);
          } catch (Exception $exc) {
            // Retry
            $status = $media->nextChunk($chunk);
          }

          $byteCounter +=  Filetrip_Channel_Utility::$CHUNK_SIZE;

          if($sse_enabled){
            /**
            *  Construct SSE message and echo it to the client
            */
            $data = array(
              'percentage' => ($filesize!=0)?intval($byteCounter/$filesize*100):0,
              'bytes' => $byteCounter
            );
            Filetrip_Channel_Utility::sse_send_message($handle, json_encode($data,JSON_UNESCAPED_SLASHES));
          }
        }

        // The final value of $status will be the data from the API for the object
        // that has been uploaded.
        $result = false;
        if($status != false) {
          $result = $status;
        }

        fclose($handle);
        // Reset to the client to execute requests immediately in the future.
        $client->setDefer(false);
      }catch(\Exception $exp){

        error_log("Filetrip: " . $exp->getMessage());
        $shortExpMsg = substr($exp->getMessage(), 0, Filetrip_Constants::ERROR_MESSAGE_MAX_LENGHT);
        
        if($sse_enabled){
          Filetrip_Channel_Utility::sse_send_message($handle, "Google Drive Error: ".$shortExpMsg, "error");
        }

        return false;
      }
      
      if($sse_enabled){
        /**
        *  Construct SSE message and echo it to the client
        */
        Filetrip_Channel_Utility::sse_send_message($handle, "finished", "finished");
        return true;
      }
    }
    
  }

  // Initiate Google Drive Setting Page
  $filetripDriveSettingPage = new Filetrip_Google_Drive_Setting_page();

}


?>
