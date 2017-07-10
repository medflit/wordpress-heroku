<?php

#define demo

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if(!class_exists('Filetrip_FTP')){

// Load setting page 
require_once __DIR__ . '/_setting_page.php';

  class Filetrip_FTP
  {
    private $ftp_host;
    private $ftp_username;
    private $ftp_password;
    private $ftp_port;
    
    static public $ftp_username_slug = 'ftp_username';
    static public $ftp_password_slug = 'ftp_password';
    static public $ftp_port_slug = 'ftp_port';
    static public $ftp_host_slug = 'ftp_host';
    static public $ftp_active_slug = 'ftp_active';
    static public $ftp_invalid_info_hash_slug = 'ftp_invalid_hash';
    
    public $settings = array();
    
    public function __construct($setting) {
      
      // If demo version, don't do anything
      if(Filetrip_Constants::DEMO_MODE)
        return;
      
      // Exit if the FTP is disabled
      if(Filetrip_FTP_Utility::check_if_ftp_disabled())
      {
        return;
      }
      
      $login = false;
      if(!isset($setting[Filetrip_FTP::$ftp_host_slug]) || !isset($setting[Filetrip_FTP::$ftp_username_slug]) )
      {
        return;
      }
      
      // AJAX Hooks
      add_action('wp_ajax_get_ftp_folder_list',array( $this, 'get_folder_list_html'));
      
      // Initialize settings
      $this->ftp_host = trim($setting[Filetrip_FTP::$ftp_host_slug], " \t\n\r\0");
      $this->ftp_username = $setting[Filetrip_FTP::$ftp_username_slug];
      $this->ftp_password = $setting[Filetrip_FTP::$ftp_password_slug];
      $this->ftp_port = $setting[Filetrip_FTP::$ftp_port_slug]==''?21:intval($setting[Filetrip_FTP::$ftp_port_slug]);
      
      $hashArray = array(
          'host' => $this->ftp_host,
          'port' => $this->ftp_port,
          'username' => $this->ftp_username,
          'password' => $this->ftp_password,
      );
      $currentInfoHash = md5(serialize($hashArray));
      
      // Check if the FTP server is active and then update FTP status accordingly to avoid repeasting this process
      if(!Filetrip_FTP::is_ftp_active() && isset($this->ftp_host) && isset($this->ftp_username))
      {
        try {   
          // Authentication section
          // Connect to the FTP server

          // Cancel process if the credentials are repeated
          if(isset($setting[Filetrip_FTP::$ftp_invalid_info_hash_slug]))
          {
            $prev_invalid_info_hash = $setting[Filetrip_FTP::$ftp_invalid_info_hash_slug];
            if( $currentInfoHash == $prev_invalid_info_hash )
            {
              return;
            }
          }
          
          $conn_id = ftp_connect($this->ftp_host, $this->ftp_port); 
          if(!$conn_id)
          {
            $invalid_info = array(
                'host' => $this->ftp_host,
                'port' => $this->ftp_port,
                'username' => $this->ftp_username,
                'password' => $this->ftp_password,
            );
            // Store the hash value of the array for further comparison
            $setting[Filetrip_FTP::$ftp_invalid_info_hash_slug] = md5(serialize($invalid_info));
            
            // Update FTP settings
            Filetrip_FTP::update_ftp_settings($setting);
            
            Filetrip_FTP::deactivate_ftp();
            return;
          }
          
          // If connection is valid, continue
          set_time_limit(0);
          session_write_close();
          ignore_user_abort( true );
          
          $login = @ftp_login($conn_id, $this->ftp_username, $this->ftp_password); 
          ftp_pasv($conn_id, true);
          
          set_time_limit(120);
        }
        catch (\Exception $ex) {
          do_action('itech_error_caught', "Error while establishing FTP connection: " . $ex->getMessage() . "\n");
          Filetrip_FTP::deactivate_ftp();
          ftp_close($conn_id);
          return;
        }
      
        if($login){
          // Update FTP status
          $setting[Filetrip_FTP::$ftp_active_slug] = true;
          Filetrip_FTP::update_ftp_settings( $setting );
        }else{
          // Update FTP status
          $setting[Filetrip_FTP::$ftp_active_slug] = false;
          Filetrip_FTP::update_ftp_settings( $setting );
        }
        
        // Close FTP connection
        ftp_close($conn_id);
        return;
      }
      
    }
    
    public function auth_start()
    {
      if(!Filetrip_FTP::is_ftp_active() && strlen($this->app_key)>5 && strlen($this->app_secret)>5)
      {
        $this->auth_url = $this->web_auth->start();
        header('location:'.$this->auth_url);
        exit();
      }else{
        do_action('itech_error_caught', 'Please fill designated forms with correct values (FTP)');
        // Handle error
      }
    }
    
    static public function is_ftp_active()
    {
      $temp_setting = Filetrip_FTP::get_ftp_settings();
      if(isset($temp_setting[Filetrip_FTP::$ftp_active_slug]) && $temp_setting[Filetrip_FTP::$ftp_active_slug])
      {
        return true;
      }else
      {
        return false;
      }
    }
    
    static public function deactivate_ftp()
    {
      $temp_setting = Filetrip_FTP::get_ftp_settings();
      unset($temp_setting[Filetrip_FTP::$ftp_active_slug]);
      
      Filetrip_FTP::update_ftp_settings($temp_setting);
    }
    
    static public function get_ftp_settings()
    {
      $ftp_settings = (array)get_option(Filetrip_FTP_Setting_page::$ftp_settings_slug);
      
      return $ftp_settings;
    }
    
    static public function update_ftp_settings($new_setting)
    {
        update_option( Filetrip_FTP_Setting_page::$ftp_settings_slug, $new_setting );
    }
    
    static public function get_folder_list($path = '.')
    {
      // Exit in case inactive
      if(!Filetrip_FTP::is_ftp_active())
      {
        return;
      }
      $ftp_setting = Filetrip_FTP::get_ftp_settings();
          
      set_time_limit(0);
      session_write_close();

      try{ 
        // Authentication section
        // Connect to the FTP server
        // Fetch root folders 

        $conn_id = ftp_connect($ftp_setting[Filetrip_FTP::$ftp_host_slug], $ftp_setting[Filetrip_FTP::$ftp_port_slug]); 
        $login = ftp_login($conn_id, $ftp_setting[Filetrip_FTP::$ftp_username_slug], $ftp_setting[Filetrip_FTP::$ftp_password_slug]); 
        ftp_pasv($conn_id, true);

        $files = Filetrip_FTP::translate_ftp_rawlist(ftp_rawlist($conn_id, $path));

        
        return $files;
      }catch(Exception $ex) {
        print("Error communicating with FTP Server: " . $ex->getMessage() . "\n");
        Filetrip_FTP::deactivate_ftp();
      }
      
      // Close FTP connection
      ftp_close($conn_id);
      return;
    }
    
    // Translate FTP raw file format into a mix of directoy and file array with detailed informations
    static public function translate_ftp_rawlist($ftp_rawlist)
    {
      $rawlist = array();
      $files = array();
      foreach ($ftp_rawlist as $v) {
        $info = array();
        $vinfo = preg_split("/[\s]+/", $v, 9);
        if ($vinfo[0] !== "total") {
          $info['chmod'] = $vinfo[0];
          $info['num'] = $vinfo[1];
          $info['owner'] = $vinfo[2];
          $info['group'] = $vinfo[3];
          $info['size'] = $vinfo[4];
          $info['month'] = $vinfo[5];
          $info['day'] = $vinfo[6];
          $info['time'] = $vinfo[7];
          $info['name'] = $vinfo[8];
          $rawlist[$info['name']] = $info;
        }
      }
      $dir = array();
      $file = array();
      foreach ($rawlist as $k => $v) {
        if ($v['chmod']{0} == "d") {
          $dir[$k] = $v;
        } elseif ($v['chmod']{0} == "-") {
          $file[$k] = $v;
        }
      }
      $files['dir'] = $dir;
      $files['files'] = $file;
      
      return $files;
    }
    
    // This function is reponsible to upload files to the configured FTP server
    public static function resumable_file_upload($destination_path , $file_uri, $sse_enabled = true)
    {
      set_time_limit(0);
      session_write_close();
      ignore_user_abort( true );

      if(!Filetrip_FTP::is_ftp_active() )
      {
        return false;
      }
      
      try{
        $setting = Filetrip_FTP::get_ftp_settings();

        // Initialize settings
        $ftp_host = trim($setting[Filetrip_FTP::$ftp_host_slug], " \t\n\r\0");
        $ftp_username = $setting[Filetrip_FTP::$ftp_username_slug];
        $ftp_password = $setting[Filetrip_FTP::$ftp_password_slug];
        $ftp_port = $setting[Filetrip_FTP::$ftp_port_slug]==''?21:intval($setting[Filetrip_FTP::$ftp_port_slug]);

        $conn_id = ftp_connect($ftp_host, $ftp_port); 
        $login = ftp_login($conn_id, $ftp_username, $ftp_password); 
        if(!$login)
        {
          do_action('itech_error_caught', "FTP login failed, please check your setting and server availability.");
          return false;
        }

        ftp_pasv($conn_id, true);

        // Give read permission for users
        chmod($file_uri, 0755);
        
        // Initiate the Upload
        $ret = @ftp_nb_put($conn_id, $destination_path.'/'.basename($file_uri), $file_uri, FTP_BINARY);
        
        while ($ret == FTP_MOREDATA) {
          // Continue uploading...
          $ret = ftp_nb_continue($conn_id);
        }
        
        if ($ret != FTP_FINISHED) {
          //echo "There was an error uploading the file...";
          do_action('itech_error_caught', "There was an error uploading the file...");
          return false;
        }
      }catch(\Exception $exp){
        error_log("Filetrip: " . $exp->getMessage());

        if($sse_enabled){
          Filetrip_Channel_Utility::sse_send_message($ret, "Error while uploading file to FTP. Check your log for more details.", "error");
        }

        return false;
      }

      if($sse_enabled){
        /**
        *  Construct SSE message and echo it to the client
        */
        Filetrip_Channel_Utility::sse_send_message($ret, "finished", "finished");
        return true;
      }
      
    }
    
    // Called by ajax from meta arfaly's posts
    public function get_folder_list_html()
    {
      if(!Filetrip_FTP::is_ftp_active())
      {
        die();
      }
      
      // Should we go one directory down ?
      $newPath='';
      $clickDirectory = '';
      
      // Not finished yet. This is for DEMO version
      if(Filetrip_Constants::DEMO_MODE)
      {
          echo $this->return_dummy_folder_list();
      }
      
      $switch = '_filetrip_ftp_folder';
      if( isset($_POST['page']) && $_POST['page'] == 'filetrip_settings')
      {
        $switch = 'filetrip_settings\\\\[ftp_folder\\\\]';
      }
      
      // Sanitize the whole input
      $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $path = $_POST['path'];
      
      // Dynamic FTP path manipulation
      // 1- Get the clicked directory name 
      $clickDirectory = basename($path);
      
      if($clickDirectory != '..' && $clickDirectory != '.' && $clickDirectory != '/'){
        $newPath = $path;
      }else if($clickDirectory == '..'){
        // You have to remove one directory from the path string
        // We should go one level down
        // 1- Remove the last '/' character
        $updatePath = substr($path, 0, strlen($path)-3);
        // 2- Remove the last directory form the path
        $updatePath = substr($updatePath, 0, strrpos($updatePath, '/'));
        $newPath=$updatePath;
      } else {
        $newPath = $path;
      }
      
      // Fetch the requested path folder list
      $files = Filetrip_FTP::get_folder_list($newPath);
      $files = $files['dir'];
      unset($files['.']);
      
      if($newPath != '/'){
        $curPath = $newPath.'/';
      }else{
        $curPath = $newPath;
      }
      $folder_icon = '<img src="'.ITECH_FILETRIP_PLUGIN_URL.'/assets/img/dropbox-folder.png" width="30" height="30">';
      ob_start();
      ?>
              <div class="arfaly-ftp-folder-loading spinner"></div>
              <h3>Path: <b><?php echo $curPath; ?></b></h3>
              <?php if ( empty( $files ) ) : ?>
              <?php echo _e( 'No files in this folder', 'arfaly_plugin' ); ?>
                    <ul class="arfaly-file-listing">
                      <li>
                        <?php echo $folder_icon;?>
                        <a href="javaScript: void(0);" onclick="javascript:arfaly_ftp_get_folder_list('<?php echo ".."; ?>')">../</a>
                      </li>
                    </ul>
              <?php else : ?>
              <ul class="arfaly-file-listing">
                <?php if($curPath != '/'){ ?>
                  <?php }
                  foreach( $files as $path => $file ) : ?>
                          <li class="arfaly-folder">
                            <a href="javaScript: void(0);" onclick="javascript:arfaly_update_dropbox_folder('<?php echo $curPath.$path; ?>', '<?php echo $switch; ?>')" class="button">Select</a>
                              <?php 
                                echo $folder_icon;
                              ?>
                            <a href="javaScript: void(0);" onclick="javascript:arfaly_ftp_get_folder_list('<?php echo $curPath.$path; ?>')"><?php echo basename( $path ) ;?></a>
                          </li>
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
    
  }

  // Initiate FTP Setting Page
  $filetripFTPSettingPage = new Filetrip_FTP_Setting_page();

}

?>