<?php

namespace iTechFlare\WP\iTechFlareExtension\ReportingITF;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

use iTechFlare\WP\iTechFlareExtension\ReportingITF\Arfaly_Report_Statistic;
use iTechFlare\WP\iTechFlareExtension\ReportingITF\Arfaly_Report_Message;

$date_range = array();

// Initialize date range
$dateTo = Date('Y-m-d');
$dateTo = strtotime($dateTo.' -1 day');
$dateTo = Date('Y-m-d', $dateTo);

$dateFrom = strtotime($dateTo.' -6 months');
$dateFrom = Date('Y-m-d', $dateFrom);
  
if(isset($_POST['dateRange']) && isset($_POST['action']) && $_POST['dateRange']!='')
{
  $nonceValidation = wp_verify_nonce( $_POST['_wpnonce'], 'arfaly-update-reporting-chart' );
  if($nonceValidation)
  {
    $date_range = explode(' to ', $_POST['dateRange']);
  }
}

global $wpdb;

if(!empty($date_range))
{
  $report = new Arfaly_Report_Statistic($wpdb->prefix . \Filetrip_Constants::RECORD_TABLE_NAME, true, $date_range[0], $date_range[1]);
  $dateTo = $date_range[1];
  $dateFrom = $date_range[0];
}else{
  $report = new Arfaly_Report_Statistic($wpdb->prefix . \Filetrip_Constants::RECORD_TABLE_NAME, true, $dateFrom, $dateTo);  
}

$report->get_selected_upload_types_counts();
$total_selected_size = $report->get_selected_total_hard_disk_usage();
$upload_chart_data = $report->get_selected_upload_chart_data_js();
$total_size = $report->get_total_hard_disk_usage();
$approved = $report->get_approved_upload_count();

?>

<div class="wrap" role="report">
  <h2><?php echo __(\Filetrip_Constants::PLUGIN_NAME." Report Page", 'filetrip-plugin'); ?></h2>
  <form method="post">
    <input type="hidden" name="action" value="arfaly-update-reporting-chart">
    <?php wp_nonce_field( 'arfaly-update-reporting-chart' ); ?>
    <input type="text" name="dateRange" id="dateRange" size="25">
    <input type="submit" class="page-title-action arfaly-update-action" value="<?php echo 'Update'; ?>">
    <span class="icon-help-circled" title="<?php echo __("Choose date range and click update to get statistics for the selected time period", 'filetrip-plugin'); ?>"></span>
  </form>
  <div id="poststuff">
    <!-- #post-body .metabox-holder goes here -->
    <div id="post-body" class="metabox-holder columns-2">
      
      <div id="post-body-content">
          <!-- #post-body-content -->
          <div class="arfaly-plot-container">
            <canvas id="myChart" width="400" height="200"></canvas>
          </div>
          <ul class="arfaly-info-list arfaly-info-horizontal">
            <li style="text-align:left">
              <h3><?php echo __("Top 10 Uploaders", 'filetrip-plugin'); ?></h3>
              <canvas id="userUploadChart" width="150" height="150"></canvas>
            </li>
            <li style="text-align:left">
              <h3><?php echo __("Count by type", 'filetrip-plugin'); ?></h3>
              <span class="icon-doc-text">Plain</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->plain_count; ?></span><br>
              <span class="icon-file-pdf">PDF</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->pdf_count; ?></span><br>
              <span class="icon-file-word">Documents</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->doc_count; ?></span><br>
              <span class="icon-file-powerpoint">Slides</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->ppt_count; ?></span><br>
              <span class="icon-file-image">Pictures</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->pic_count; ?></span><br>
            </li>
            <li style="text-align:left">
              <h3><?php echo __("Count by type", 'filetrip-plugin'); ?></h3>
              <span class="icon-file-archive">Archives</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->archive_count; ?></span><br>
              <span class="icon-file-audio">Sound</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->audio_count; ?></span><br>
              <span class="icon-file-video">Videos</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->video_count; ?></span><br>
              <span class="icon-file-code">Code</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->code_count; ?></span><br>
              <span class="icon-folder-empty">Uknown</span> &rArr;<span class="arfaly-counter-sml"><?php echo $report->unknown_count; ?></span>
            </li>
          </ul>
      </div>
      <div id="postbox-container-1" class="postbox-container">
        <ul class="arfaly-info-list">
          <li>
            <h3><?php echo __("Legend", 'filetrip-plugin'); ?></h3>
            <span class="icon-star arfaly-total"></span> <?php echo __("Total Statistics", 'filetrip-plugin'); ?><br>
            <span class="icon-star-empty arfaly-ranged-total" ></span> <?php echo __("Partial Statistics <br>Based on selected date range", 'filetrip-plugin'); ?>
          </li>
          <li class="arfaly-total">
            <h3><?php echo __("Total Uploads", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" title="<?php echo __("Choose date range and click update to get statistics for the selected time period", 'filetrip-plugin'); ?>"></span>
            <span class="icon-upload-cloud"></span>
            <span class="arfaly-counter"><?php echo $report->get_total_uploads(); ?></span>
          </li>
          <li class="arfaly-total">
            <h3><?php echo __("Total Disk Space Usage", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" title="<?php echo __("Choose date range and click update to get statistics for the selected time period", 'filetrip-plugin'); ?>"></span>
            <span class="icon-download"></span>
            <span class="arfaly-counter"><?php echo $total_size['digits']; ?></span><span style="font-size:20px"><?php echo $total_size['unit']; ?></span>
          </li>
          <li>
            <h3><?php echo __("Selected Total Uploads", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" title="<?php echo __("This is the total number of uploads for the selected period", 'filetrip-plugin'); ?>"></span>
            <span class="icon-upload-cloud"></span>
            <span class="arfaly-counter"><?php echo $report->get_selected_total_uploads(); ?></span>
          </li>
          <li>
            <h3><?php echo __("Hard Disk Space Usage", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" title="<?php echo __("This is the total size of hard disk usage for the uploads in the selected period", 'filetrip-plugin'); ?>"></span>
            <span class="icon-download"></span>
            <span class="arfaly-counter"><?php echo $total_selected_size['digits']; ?></span><span style="font-size:20px"><?php echo $total_selected_size['unit']; ?></span>
          </li>
          <li>
            <h3><?php echo __("Avg Upload", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" title="<?php echo __("Choose date range and click update to get statistics for the selected time period", 'filetrip-plugin'); ?>"></span>
            <span class="icon-flash"></span>
            <span class="arfaly-counter"><?php echo  $report->get_selected_avg(); ?></span><span style="font-size:20px"><?php echo __("  / Day", 'filetrip-plugin'); ?></span>
          </li>
          <li>
            <h3><?php echo __("Approved Uploads", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" rel="tooltip" title="<?php echo __("Number of uploads been approved, within the selected period of time", 'filetrip-plugin'); ?>"></span>
            <span class="icon-ok"></span>
            <span class="arfaly-counter"><?php echo $approved['approved']; ?></span>
          </li>
          <li>
            <h3><?php echo __("Not-Approved Uploads", 'filetrip-plugin'); ?></h3>
            <span class="icon-help-circled" title="<?php echo __("Number of uploads need to be approved, within the selected period of time", 'filetrip-plugin'); ?>"></span>
            <span class="icon-eye-off"></span>
            <span class="arfaly-counter"><?php echo $approved['not-approved']; ?></span><br>
            <a href="<?php echo admin_url( \Filetrip_Constants::FILETRIP_MAIN_MENU.'&page='.\Filetrip_Constants::REVIEW_APPROVE_MENU_PAGE ) ?>">Review & approve</a>
          </li>
        </ul>
      </div>
    </div>
    
  </div>
  
</div>

<script>
  jQuery( document ).ready(function($) {
    
    // Initialize tooltip 
        tipOptions = {
      smartPlacement:true
    };
    
    // Initialize tooltip 
    $('.icon-help-circled').powerTip(tipOptions);
    
    
    $('.arfaly-counter').counterUp({
                delay: 10,
                time: 1000
            });
    $('.arfaly-counter-sml').counterUp({
                delay: 20,
                time: 1000
            });
    
    //Chart.defaults.global.responsive = true;
    // Get the context of the canvas element we want to select
    var ctx = document.getElementById("myChart").getContext("2d");
    var userUploadCtx = document.getElementById("userUploadChart").getContext("2d");

    var data = {
        labels: <?php echo $upload_chart_data['labels']; ?>,
        datasets: [
              {
                  label: "My Second dataset",
                  fillColor: "rgba(96,125,139,0.4)",
                  strokeColor: "#607D8B",
                  pointColor: "#607D8B",
                  pointStrokeColor: "#fff",
                  pointHighlightFill: "#fff",
                  pointHighlightStroke: "rgba(151,187,205,1)",
                  data: <?php echo $upload_chart_data['data']; ?>
              }
          ]
      };
      
    var pieData = <?php echo $report->get_top_ten_uploaders(); ?>;
      
    
    var myLineChart = new Chart(ctx).Line(data, {responsive: true});
    var myPieChart = new Chart(userUploadCtx).Pie(pieData);

    // Range Date Picker section
    $('#dateRange').dateRangePicker({
		showShortcuts: true,
		shortcuts : 
		{
			'prev-days': [3,5,7],
			'prev': ['week','month','year'],
			'next-days':null,
			'next':null
		}
	});

    $('#dateRange').data('dateRangePicker').setDateRange("<?php echo $dateFrom;?>","<?php echo $dateTo;?>");
    
  });
</script>