<?php

namespace iTechFlare\WP\iTechFlareExtension\ReportingITF;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report-message
 *
 * @author aelbuni
 */
class Arfaly_Report_Message {
  //put your code here
  public $uploader_name; // Name of the Arfaly uploader
  public $size;
  public $att_url;
  public $att_id;
  public $att_name;
  public $datetime;
  public $share_count;
  public $ext;
  public $mime;
  public $author_name;
  public $approved;
  
  private $db_table;
  
  public function __construct($record, $wp_media_included = false)
  {
    if(!$wp_media_included)
      $this->update_report_variables($record);
    else
      $this->update_report_variables_with_media($record);
  }
  
  private function update_report_variables($record)
  {
    $post_info = get_post($record['arfaly_uploader_id']);
    $author_info = get_userdata($record['user_id']);
    
    if($record['user_id']<0)
    {
      $this->author_name = "guest";
    }else{
      $this->author_name = $author_info->user_login;
    }
    
    $this->size = $record['att_size'];
    
    $this->uploader_name = $post_info->post_title;
    
    // Check attachment info
    $att_info = get_post($record['att_id']);
    if($att_info==null)
      return;
    
    $att_url = wp_get_attachment_url($record['att_id']);
    $ext = pathinfo($att_url, PATHINFO_EXTENSION);
    
    $this->att_url = $att_url;
    $this->att_id = $record['att_id'];
    $this->att_name = $att_info->post_title;
    $this->datetime = strtotime($att_info->post_date);
            
    $att_info = get_post($record['att_id']);
    $this->ext = $ext;
    $this->approved = ($att_info->post_status == \Filetrip_Constants::POST_STATUS)?false:true;
    $this->mime = $att_info->post_mime_type;
     
  }
  
  function update_report_variables_with_media($attachment_id)
  {
    $att_info = get_post($attachment_id);
    $post_info = get_post($att_info->post_parent);
    $author_info = get_userdata($att_info->post_author);
    
    if($att_info==null)
      return;
        
    $att_src = get_attached_file($attachment_id);
    
    // Return if file doesn't exist
    if(!file_exists($att_src)){
      wp_delete_attachment($attachment_id);
      return;
    }
    
    $att_url = wp_get_attachment_url($attachment_id);
    $ext = pathinfo($att_url, PATHINFO_EXTENSION);
    
    if($att_info->post_author<=0)
    {
      $this->author_name = "guest";
    }else{
      $this->author_name = $author_info->user_login;
    }
    
    $this->size = @filesize($att_src);
    $this->datetime = strtotime($att_info->post_date);
    
    if($post_info)
      $this->uploader_name = $post_info->post_title;
    else
      $this->uploader_name = 'none';
    
    $this->att_url = $att_url;
    $this->att_id = $attachment_id;
    $this->att_name = $att_info->post_title;
            
    $this->ext = $ext;
    $this->approved = ($att_info->post_status == \Filetrip_Constants::POST_STATUS)?false:true;
    $this->mime = $att_info->post_mime_type;
  }
  
}
?>
