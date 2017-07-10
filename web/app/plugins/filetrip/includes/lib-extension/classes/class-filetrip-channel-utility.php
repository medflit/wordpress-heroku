<?php

/*
 * This calss should provide an abstraction for handling chunked uploads
 * for both Dropbox, Google Drive, and all of the other channels.
 */


class Filetrip_Channel_Utility
{
  private $api_selection;
  static public $CHUNK_SIZE = 8388608; // (8 MB) This is the typical chunk size
  
  public function __construct() 
  {
  }
  
  /*
  * @param $file_uri : Contains the file path
  * @param $start_point : Starting pointer that defines where the function should start reading bytes from; and then returns a chunk of bytes
  *             with size ($CHUNK_SIZE)
  * Use this function to send SSE responses.
  */
  public static function read_chunk_from_file($file_uri, $start_point)
  {
    $bytes_to_read = Filetrip_Channel_Utility::$CHUNK_SIZE;
    $file_size = filesize($file_uri);
    $data = null;
    
    // Check if bytes to read is not overflowing
    if(($bytes_to_read+$start_point) >= $file_size)
    {  
      $bytes_to_read = ($file_size - $start_point);
    }
    if($start_point >= $file_size)
    {
      return array("",false);
    }
    
    //echo '$bytes_to_read'.$bytes_to_read;
    
    try{
      $fp = fopen($file_uri, 'r');

      // move to the 7th byte
      fseek($fp, $start_point);

      $data = fread($fp,$bytes_to_read);

      fclose($fp);
    }
    catch(Exception $exp)
    {
      do_action('itech_error_caught', $exp);
      return array("", $bytes_to_read);
    }
    
    return array($data,$bytes_to_read);
  }

  /*
  * Use this function to send SSE responses.
  */
  public static function sse_send_message($id, $data, $event = "message")
  {
    if (ob_get_contents()) ob_end_clean();

    /*
    * If custom event is defined, use it
    */
    if($event != "message")
    {
      echo 'event:' . $event . PHP_EOL;
    }

    echo 'id:' . $id . PHP_EOL;
    echo 'data:' . $data . PHP_EOL;
    echo PHP_EOL;

    if (ob_get_contents()) ob_end_flush();
    flush();
  } 

  public static function format_bytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);

    $size = array('digits'=>round($bytes, $precision),'unit'=>$units[$pow]);
    
    return  $size; 
  } 
  
  public static function add_upload_job($uploadID, $channel, $offset)      
  {
    $r_uploads = array();
    $r_uploads = array('id'=>$uploadID, 'offset'=>$offset, 'time'=>time(), 'channel'=> $channel);
      
    $dropbox_setting = Filetrip_Dropbox::get_dropbox_settings();
    
    // First time called
    if(!isset($dropbox_setting[Filetrip_Dropbox::$dropbox_resumable_uploads_slug]))
      $dropbox_setting[Filetrip_Dropbox::$dropbox_resumable_uploads_slug] = array();

    $dropbox_setting[Filetrip_Dropbox::$dropbox_resumable_uploads_slug][$uploadID] = $r_uploads;

    Filetrip_Dropbox::update_dropbox_settings($dropbox_setting);
    
    return true;
  }

  static function get_channel_selected($post_id)
  {
      if(empty($post_id))
        return false;
    
      $meta = get_post_meta( $post_id );
      if(isset($meta[Filetrip_Constants::METABOX_PREFIX.'channel_select']))
      {
        $channel_selected = unserialize($meta[Filetrip_Constants::METABOX_PREFIX.'channel_select'][0]);
        return $channel_selected;
      }
      
      return false;
  }


  
}

?>
