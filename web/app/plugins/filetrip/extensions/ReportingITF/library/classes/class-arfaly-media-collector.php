<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace iTechFlare\WP\iTechFlareExtension\ReportingITF;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Description of data-adapter
 *
 * @author aelbuni
 */
class Arfaly_Media_Collector {
  //put your code here
  
  private $report_records;
  
  private $db_table;
  
    
  /**
  * Constructor that takes a date range and calculate all of the statistics needed from the report accordingly
  * 
  * @param string $db_table
  * @param bool $arfaly True if you need to fetch and build Arfaly upload table only, otherwise set to False to fetch WP_Media attachments
   * 
  * @return void
  */
  
  public function __construct($db_table, $arfaly = true)
  {
    $this->db_table = $db_table;
    $this->get_arfaly_records($arfaly);
  }
  
  function get_arfaly_records($arfaly)
  {
    global $wpdb;
    $report_msg = null;
    
    // If we need to select records based on datetime range
    $results = $wpdb->get_results( 'SELECT * FROM '.$this->db_table , ARRAY_A );
    
    $report_msgs = array();
    
    if(count($results)>=0 && $arfaly)
    {
      foreach($results as $r)
      {
            
        $report_msg = new Arfaly_Report_Message($r);
        
        array_push($report_msgs, $report_msg);
      }
    }
    
    // If media was selected
    if(!$arfaly)
    {
      $args = array(
                'post_type'=> 'attachment',
                'posts_per_page' => -1
            );
    
      $attachments = get_posts($args);

      foreach($attachments as $att)
      {
        $report_msg = new Arfaly_Report_Message($att->ID, true);
        
        array_push($report_msgs, $report_msg);
      }
    }
    
    
    $this->report_records = $report_msgs;
    
    return;
  }
  
  public function get_arfaly_upload_list()
  {
    return $this->report_records;
  }
  
  function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);

    $size = array('digits'=>round($bytes, $precision),'unit'=>$units[$pow]);
    
    return  $size; 
  } 
  
  public function build_json_titles()
  {
    $titles = '[
            { title: "'.__("Size", 'filetrip-plugin').'" },
            { title: "SizeSort" },
            { title: "TypeNames" },
            { title: "'.__("View", 'filetrip-plugin').'" },
            { title: "'.__("Name", 'filetrip-plugin').'" },
            { title: "'.__("Date", 'filetrip-plugin').'" },
            { title: "'.__("Type", 'filetrip-plugin').'" },
            { title: "'.__("Author", 'filetrip-plugin').'" },
            { title: "'.__("Approved", 'filetrip-plugin').'" },
            { title: \'<a class="arfaly-header-check-label" href="javaScript: void(0);" onclick="javascript:check_all()"><span class="icon-check"></span></a><br><a class="arfaly-header-check-label" href="javaScript: void(0);" onclick="javascript:clear_all()"><span class="icon-check-empty"></span></a>\' },
        ]';
                    
    return $titles;
  }
  
  public function build_json_data_table_arfaly()
  {
    if(empty($this->report_records))
      return '[]';
      
    $jsonTable = array();
    
    foreach($this->report_records as $record)
    {
      $recordSrc = get_attached_file( $record->att_id );
      
      // If source file doesn't exist, delete attachment
      if(!file_exists($recordSrc)){
        wp_delete_attachment($record->att_id);
        continue;
      }
      
      $recordSize = @filesize( $recordSrc );
      $size = $this->formatBytes($recordSize);
      $type = $this->get_type_from_ext($record->ext);
      
      $jsonTable[] = array(
          $size['digits'].' <b>('.$size['unit'].'</b>)',
          $record->size,
          $type['type'],
          '<a target="_blank" class="arfaly-header-check-label arfaly-preview-tip" href="'.$record->att_url.'" >Preview</a>',
          '<span title="'.$record->att_name.'">'.$record->att_name.'</span>',
          '<span class="arfaly-date-span" title="'.Date('H:m:s',$record->datetime).'">'.Date('Y-m-d',$record->datetime).'</span>',
          $type['icon'],
          $record->author_name,
          $record->approved?'<div style="display:none">approved</div><span title="'.__('Approved', 'filetrip-plugin').'" class="icon-ok"></span>':'<div style="display:none">not approved</div><span title="'.__('Not Approved', 'filetrip-plugin').'" class="icon-eye-off"></span>',
          '<input type="checkbox" class="att_ids" value="'.$record->att_id.'">'
      );
    }
    
    return json_encode($jsonTable);
  }
  
  public function get_type_from_ext($ext)
  {
    $return_type = array();
    switch($ext)
      {
        case 'doc':case 'docx':case 'doc':case 'php':case 'cpp':case 'xls':case 'xlsx':
          $return_type = array('type'=>'Document','icon'=>'<span title="'.__('Document', 'filetrip-plugin').'" class="icon-file-word"></span>');
          return $return_type; 
          break;
        case 'ppt':case 'pptx':
          $return_type = array('type'=>'Slide','icon'=>'<span title="'.__('Slide', 'filetrip-plugin').'" class="icon-file-powerpoint"></span>');
          return $return_type;
          break;
        case 'zip':case 'rar':case '7zip':case 'gz':case 'tar':
          $return_type = array('type'=>'Archive','icon'=>'<span title="'.__('Archive', 'filetrip-plugin').'" class="icon-file-archive"></span>');
          return $return_type;
          break;
        case 'mp3':case 'wav':case 'midi':
          $return_type = array('type'=>'Audio','icon'=>'<span title="'.__('Audio', 'filetrip-plugin').'" class="icon-file-audio"></span>');
          return $return_type;
          break;
        case 'mp4':case 'mov':case 'flv':case '3gp':case '3gpp':case 'avi':
          $return_type = array('type'=>'Video','icon'=>'<span title="'.__('Video', 'filetrip-plugin').'" class="icon-file-video"></span>');
          return $return_type;
          break;
        case 'gif':case 'png':case 'jpeg':case 'jpg':case 'bmp':case 'raw':
          $return_type = array('type'=>'Picture','icon'=>'<span title="'.__('Image', 'filetrip-plugin').'" class="icon-file-image"></span>');
          return $return_type;
          break;
        case 'c':case 'cpp':case 'php':case 'js':case 'asm':
          $return_type = array('type'=>'Code','icon'=>'<span title="'.__('Code', 'filetrip-plugin').'" class="icon-file-code"></span>');
          return $return_type;
          break;
        case 'txt':case '':
          $return_type = array('type'=>'Unknown','icon'=>'<span title="'.__('Unknown', 'filetrip-plugin').'" class="icon-folder-empty"></span>');
          return $return_type;
          break;
        default:
          break;
      }
  }
  
  /**
  * Constructor that takes a date range and calculate all of the statistics needed from the report accordingly
  * 
  * @param $att_id_array
  * @param bool $wp_media_structure True if user wants to maintain Wordpress Media upload structure
  * @param bool $archive_shareable True if user wants to archive shareable for certain period of time
   * 
  * @return array('url'=>**, 'path'=>**)
  */
  
  public function archive_and_return_path($att_id_array, $wp_media_structure = false, $archive_shareable = false )
  {
    $files = array();
    $fileName = "";
    foreach($att_id_array as $att_id)
    {
      $path = get_attached_file($att_id);
      $att_info = get_post($att_id);
      
      // If there is a path, save {path, and date} into the array
      if($path)
        $files[] = array('path' => $path, 'date' => $att_info->post_date);
    }
    
    if($archive_shareable)
    {
      //$fileName = $this->build_filename_shareable_archive();
      $fileName = $this->build_filename_shareable_archive();
    }else{
      $fileName = $this->build_filename_regular_archive();
    }
    
    $zipfilepath = ARFALY_GOLD_TMP_FOLDER_URI.DIRECTORY_SEPARATOR.$fileName;
    $zip = new ZipArchive;
    
    $zip->open($zipfilepath, ZipArchive::CREATE);
    foreach ($files as $file) {   
      
      if($wp_media_structure){
        // Archive according to Wordpress Media /uploads structure
        $creationDate = strtotime($file['date']);
        $destFileName = date('Y', $creationDate).DIRECTORY_SEPARATOR.date('m', $creationDate).DIRECTORY_SEPARATOR.basename($file['path']);
      }else{
        // Just archive the files with no folders
        $destFileName = basename($file['path']);
      }
      
      $zip->addFile($file['path'], $destFileName);
    }
    $zip->close();
    
    $zipfileurl = ITECHARFALY_GOLDPLUGINURL.'/uploads'.DIRECTORY_SEPARATOR.$fileName;
    

    return array('url'=>$zipfileurl, 'path'=>$zipfilepath);
    
  }       
  
  private function build_filename_regular_archive()
  {
    $zipname = 'archive_'.time();
    $postfix = '.arfaly.part.'.date('d').'.zip';
    
    return $zipname.$postfix;
  }
  
  private function build_filename_shareable_archive()
  {
    $zipname = 'shareable_'.time();
    $postfix = '.arfaly.zip';
    
    return $zipname.$postfix;
  }
  
}
?>
