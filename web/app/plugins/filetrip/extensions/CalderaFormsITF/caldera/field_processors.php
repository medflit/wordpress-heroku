<?php


add_filter('caldera_forms_process_field_filetrip_uploader', 'filetrip_handle_file_upload', 10, 3);

/* IMPORTANT:: This function process form information and return list of URL's to Caldera according to its standard */

function filetrip_handle_file_upload($entry, $field, $form){
  
  $uploads = array();

  $required = false;
  if ( isset( $field[ 'required' ] ) &&  $field[ 'required' ] ){
      $required = true;
  }
    
  if(isset($_POST["image-id"]))
  {
    $att_ids = $_POST["image-id"];
    
    foreach( $att_ids as $att ){
      $url = wp_get_attachment_url( intval($att) );
      
      // If URL has been successfully returned
      if($url)
        $uploads[] = $url;
    }
  }else if(!!isset($_POST["image-id"]) && $required){
    return new WP_Error( 'fail', __('No file has been uploaded', 'filetrip-plugin') );
  }
  
  if( count( $uploads ) > 1 ){
      return $uploads;
  }
  
  if(!empty($uploads))
    return $uploads[0];
  else
    return '';
}