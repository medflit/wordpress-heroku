<?php

/*
 * This calss should provide an abstraction for database record handling
 */


class Filetrip_Uploader_Recorder
{
  private $api_selection;
  
  public function __construct($selection) 
  {
    $this->api_selection = $selection;
  }
  
  // Return uploaded files for a specific user
  public static function get_files_for_user($arfaly_uploader_ids, $user_ids = -1)
  {
    // Filetrip return image object structure
    // stdClass Object
    /*(
        [id] => 1
        [user_id] => 1
        [att_id] => 2134
        [arfaly_uploader_id] => 1932
        [att_size] => 396195
        [time] => 2015-10-04 17:51:56
        [dropbox_sent] => 
        [drive_sent] => 
        [ftp_sent] => 
        [dropbox_cdn] => 
        [drive_cdn] => 
        [short_url] => 
    )*/
    global $wpdb;
    $where_userid = "(";
    $where_arfaly_uploader_id = "(";

    /*======================================================*/
    /*=================Generate Where======================*/
    // Filter user_ids
    for($i=0; $i < count($user_ids);$i++)
    {
      // Guest users have ID of 0, so make sure to replace it with -100 for successful storage
      if($user_ids[$i] == 0){
        $user_ids[$i] = -100;
      }

      if($i<(count($user_ids)-1))
        $where_userid = $where_userid . "user_id=".$user_ids[$i]." OR ";
      else
        $where_userid = $where_userid . "user_id=".$user_ids[$i].")";
    }

    if($arfaly_uploader_ids != ""){
      for($i=0; $i < count($arfaly_uploader_ids);$i++)
      {

        if($i<(count($arfaly_uploader_ids)-1))
          $where_arfaly_uploader_id = $where_arfaly_uploader_id . "arfaly_uploader_id=".$arfaly_uploader_ids[$i]." OR ";
        else
          $where_arfaly_uploader_id = $where_arfaly_uploader_id . "arfaly_uploader_id=".$arfaly_uploader_ids[$i].")";
      }
    }

    /*=================Generate Where END===================*/
    /*======================================================*/

    // If there was no arfaly ID, return the whole images for a single user
    if($arfaly_uploader_ids == "")
    {
      $select_query = (sprintf("SELECT * FROM %s WHERE %s;", $wpdb->prefix .Filetrip_Constants::RECORD_TABLE_NAME, $where_userid ) );
      $existingImg = $wpdb->get_results( $select_query, ARRAY_A  );
      return $existingImg;
    }else{

      $select_query = sprintf("SELECT * FROM %s WHERE %s AND %s;", $wpdb->prefix .Filetrip_Constants::RECORD_TABLE_NAME, $where_arfaly_uploader_id, $where_userid ) ;
      $existingImg = $wpdb->get_results( $select_query, ARRAY_A  );
      // print_r($select_query);
      return $existingImg;
    }

    return false;

  }

  // This function will be called when new file is been uploaded successfully
  public static function register_uploaded_file($att_id, $arfaly_uploader_id, $user_id, $att_size)
  {
    global $wpdb;

    try{
      $wpdb->replace( 
        $wpdb->prefix . Filetrip_Constants::RECORD_TABLE_NAME, 
        array( 
            'user_id' => $user_id, 
            'att_id' => $att_id,
            'arfaly_uploader_id' => $arfaly_uploader_id,
            'att_size' => $att_size,
            'time' => date('Y-m-d H:i:s')
        ), 
        array( 
            '%d', 
            '%d',
            '%d',
            '%s', 
            '%s' 
        ) 
      );
    }catch(Exception $ex)
    {
      Report_Error("Database insert error, check your error log file");
      error_log("Filetrip Error:: ".$ex);
    }
  }

  public static function database_cleansing()
  {
    global $wpdb;

    $uploadRecords = Filetrip_Uploader_Recorder::get_uploaded_files();

    foreach($uploadRecords as $upload){

        $upload_obj = wp_get_attachment_image_src( $upload['att_id'] );
        // If the attachment is not available, remove it
        if(!$upload_obj)
        {
          // Delete record if the attachment is no longer exist
          $wpdb->delete( $wpdb->prefix . Filetrip_Constants::RECORD_TABLE_NAME, array( 'att_id' => intval($upload['att_id']) ), array( '%d' ) );
        }
    }
  }

  public static function get_uploaded_files()
  {
    global $wpdb;

    $select_query = (sprintf("SELECT * FROM %s;", $wpdb->prefix .Filetrip_Constants::RECORD_TABLE_NAME ) );
    $existingUploads = $wpdb->get_results( $select_query, ARRAY_A  );

    return $existingUploads;
  }
  
}

?>
