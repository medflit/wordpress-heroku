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
class Arfaly_Report_Statistic {
  //put your code here
  
  private $report_records;
  private $total_upload = null;
  private $total_size = null;
  private $days_in_range = null;
  private $date_range;
  
  public $plain_count;
  public $pdf_count;
  public $doc_count;
  public $ppt_count;
  public $pic_count;
  public $archive_count;
  public $audio_count;
  public $video_count;
  public $code_count;
  public $unknown_count;
  
  private $db_table;
  
    
  /**
  * Constructor that takes a date range and calculate all of the statistics needed from the report accordingly
  * 
  * @param string $db_table
  * @param bool $ranged True if you need to build statistics based on date range
  * @param Date $from Formatted as 'Y-m-d'
  * @param Date $to Formatted as 'Y-m-d'
   * 
  * @return void
  */
  
  public function __construct($db_table, $ranged, $from=0, $to=0)
  {
    $this->db_table = $db_table;
    if($ranged && $from!=0 && $to!=0)
    {
      $this->date_range = array(
          'from' => date_create_from_format('Y-m-d',$from),
          'to' =>   date_create_from_format('Y-m-d',$to)
          );
      
      $this->get_report_messages_by_date(true, $from, $to);
    }else{
      $this->date_range = null;
      $this->get_report_messages_by_date(false);
    }
  }
  
  function get_report_messages_by_date($ranged, $from=0, $to=0)
  {
    global $wpdb;
    $report_msg = null;
    
    // If we need to select records based on datetime range
    if($ranged){
      $results = $wpdb->get_results( 'SELECT * FROM '.$this->db_table.' WHERE time BETWEEN "'.$from.' 00:00:00" AND "'.$to. ' 23:59:59"' , ARRAY_A );
    }else{
      $results = $wpdb->get_results( 'SELECT * FROM '.$this->db_table , ARRAY_A );
    }
    
    $report_msgs = array();
    
    if(count($results)>=0)
    {
      foreach($results as $r)
      {
            
        $report_msg = new Arfaly_Report_Message($r);
        
        array_push($report_msgs, $report_msg);
      }
    }else{
      return null;
    }
    
    // Pre-calculate total uploads and store it
    $this->total_upload = $wpdb->get_var( 'SELECT count(*) FROM '.$this->db_table);
    
    // Pre calculate total disk usage and store it
    $this->total_size = $wpdb->get_var( 'SELECT sum(att_size) FROM '.$this->db_table);
    $this->total_size = $this->formatBytes($this->total_size);
    
    // Calculate how many days between given range and store it
    $this->days_in_range = $this->get_days_from_date_range($from, $to);
    
    // Store report message collection
    $this->report_records = $report_msgs;
    
    return;
  }
  
  public function get_selected_total_uploads(){
        
    return count($this->report_records);
  }
  
  /**
  * This function return how many uploads are been approved, and how many are waiting to be approved
  * 
  * 
  * @return array('approved'=>?, 'not-approved'=>?)
  */
  public function get_approved_upload_count()
  {
    $approved = 0;
    $not_approved = 0;
    
    foreach($this->report_records as $upload)
    {
      if($upload->approved)
      {
        $approved++;
      }else{
        $not_approved++;
      }
    }
    
    return array('approved'=>$approved, 'not-approved'=>$not_approved);
  }
  
  public function get_total_uploads(){
    global $wpdb;
    $count = $wpdb->get_var( 'SELECT count(*) FROM '.$this->db_table);
    
    $this->total_upload = $count;
    
    return $count;
  }
  
  public function get_total_hard_disk_usage()
  {
    return $this->total_size;
  }
  
  public function get_selected_total_hard_disk_usage(){
    $size=0;
    foreach($this->report_records as $upload)
    {
      $size = $size+$upload->size;
    }
    
    $size = $this->formatBytes($size, 2);
    
    return $size;
  }
  
  public function get_selected_avg()
  {    
    return round(count($this->report_records)/$this->days_in_range, 2);
  }
  
  public function get_selected_upload_types_counts(){
    
    $this->doc_count=0;
    $this->plain_count=0;
    $this->pdf_count=0;
    $this->doc_count=0;
    $this->ppt_count=0;
    $this->pic_count=0;
    $this->audio_count=0;
    $this->video_count=0;
    $this->code_count=0;
    $this->unknown_count=0;
    $this->archive_count=0;
  
    foreach($this->report_records as $upload)
    {
      switch($upload->ext)
      {
        case 'doc':case 'docx':case 'doc':case 'php':case 'cpp':case 'xls':case 'xlsx':
          $this->doc_count++; 
          break;
        case 'ppt':case 'pptx':
          $this->ppt_count++;
          break;
        case 'zip':case 'rar':case '7zip':case 'gz':case 'tar':
          $this->archive_count++;
          break;
        case 'mp3':case 'wav':case 'midi':
          $this->audio_count++;
          break;
        case 'mp4':case 'mov':case 'flv':case '3gp':case '3gpp':case 'avi':
          $this->video_count++;
          break;
        case 'gif':case 'png':case 'jpeg':case 'jpg':case 'bmp':case 'raw':
          $this->pic_count++;
          break;
        case 'c':case 'cpp':case 'php':case 'js':case 'asm':
          $this->code_count++;
          break;
        case 'txt':case '':
          $this->plain_count++;
          break;
        default:
          break;
      }
    }
    
    return;
  }
  
  public function get_top_ten_uploaders()
  {
    global $wpdb;
    
    $data = array();
    
    $from = $this->date_range['from']->format('Y-m-d');
    $to = $this->date_range['to']->format('Y-m-d');
    
    $query = 'SELECT user_id, count(*) as count FROM '.$this->db_table.' WHERE time BETWEEN "'.$from.' 00:00:00" AND "'.$to. ' 23:59:59" GROUP BY user_id ORDER BY count DESC LIMIT 10';
    
    // Select top 10 uploaders
    $result = $wpdb->get_results($query, ARRAY_A);
    
    foreach($result as $record)
    {
      $color = Random_Color::one(array('format'=>'hex'));
      
      $author_info = get_userdata($record['user_id']);
    
      if($record['user_id']<=0)
      {
        $author_name = "guest";
      }else{
        $author_name = $author_info->user_login;
      }
      
      $data[] = array(
          'value'=> $record['count'],
          'color'=> $color,
          'label'=> $author_name
      );
    }
    
    return json_encode($data);
    
  }
  
  public function get_selected_upload_chart_data_js()
  {
    if($this->date_range == null)
      return false;
    
    $dailyUploadCounter = array();
    $yearlyUploadCounter = array();
    $monthlyUploadCounter = array();
    
    $fromYear = intval($this->date_range['from']->format('Y'));
    $toYear = intval($this->date_range['to']->format('Y'));
    
    $yearly = ($fromYear!=$toYear)?true:false;
    
    // Monthly case
    if($this->days_in_range>31 && !$yearly){
      
      // Initialize month array by the range of [from-to]
      $from = intval($this->date_range['from']->format('m'));
      $to = intval($this->date_range['to']->format('m'));

      if($from>$to)
      {
        $temp = $from;
        $from = $to;
        $to = $temp;
      }

      for($i=$from;$i<=$to;$i++)
      {
          $monthlyUploadCounter[$i]=0;
      }
      
      foreach($this->report_records as $upload)
      {
          $month = intval(date('m', $upload->datetime));
          $monthlyUploadCounter[$month]++;
      }
      return $this->convert_chart_array_to_chart_js($monthlyUploadCounter, true);
      
      // Daily case
    }else if($this->days_in_range<31  && !$yearly){
      
      $month = intval($this->date_range['from']->format('m'));
      $year = intval($this->date_range['from']->format('Y'));
      $number_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
              
      for($i=1;$i<=$number_of_days;$i++)
      {
          $dailyUploadCounter[$i]=0;
      }
    
      foreach($this->report_records as $upload)
      {
          $day = intval(date('d', $upload->datetime));
          $dailyUploadCounter[$day]++;
      }
      return $this->convert_chart_array_to_chart_js($dailyUploadCounter);
      
      // Yearly case
    }else{
      
      // Initialize year array by the range of [from-to]
      $from = intval($this->date_range['from']->format('Y'));
      $to = intval($this->date_range['to']->format('Y'));

      if($from>$to)
      {
        $temp = $from;
        $from = $to;
        $to = $temp;
      }

      for($i=$from;$i<=$to;$i++)
      {
          $yearlyUploadCounter[$i]=0;
      }
      
      foreach($this->report_records as $upload)
      {
          $year = intval(date('Y', $upload->datetime));
          $yearlyUploadCounter[$year]++;
      }
      return $this->convert_chart_array_to_chart_js($yearlyUploadCounter);
      
    }
    
  }
  
  // Utility functions
  
  public function convert_chart_array_to_chart_js($chart_data, $monthly=false)
  {  
    $monthTranslator = array(
        '1'=> 'January',
        '2'=> 'February',
        '3'=> 'March',
        '4'=> 'April',
        '5'=> 'May',
        '6'=> 'June',
        '7'=> 'July',
        '8'=> 'August',
        '9'=> 'September',
        '10'=> 'October',
        '11'=> 'November',
        '12'=> 'December'
    );
        
    // Paired containers
    $labels_arr = array();
    $data_arr = array();
    
    // Parse chart array
    foreach($chart_data as $key=>$data)
    {
      if($monthly){
        $labels_arr[] = $monthTranslator[$key];
      }
      else{
        $labels_arr[] = $key;
      }
      $data_arr[] = $data;
    }
    
    return array('labels'=>json_encode($labels_arr), 'data'=>json_encode($data_arr));
    
  }
  
  function get_days_from_date_range($from, $to){

     $from = strtotime($from);
     $to = strtotime($to);
     $datediff = $to - $from;
     
     return floor($datediff/(60*60*24));
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
  
}
?>
