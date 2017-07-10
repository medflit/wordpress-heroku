<?php

namespace iTechFlare\WP\iTechFlareExtension\ReportingITF;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

use iTechFlare\WP\iTechFlareExtension\ReportingITF\Arfaly_Report_Statistic;
use iTechFlare\WP\iTechFlareExtension\ReportingITF\Arfaly_Report_Message;
use iTechFlare\WP\iTechFlareExtension\ReportingITF\Random_Color;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report-page
 *
 * @author aelbuni
 */
class Arfaly_Report_Page {
//put your code here
  protected static $instance = null;
  private $plugin_url;

  public function __construct($extension_url)
  {
    // Init
    $this->plugin_url = $extension_url;
    add_action( 'init', array( $this, 'report_init' ) );
  }
  
  function report_init()
  {
    add_action('admin_menu', array($this, 'arfaly_add_menu_items'));
    
    // Enqueue Admin scripts and stylesheets
    add_action( 'admin_enqueue_scripts', array($this ,'arfaly_admin_enqueue') );
  }
  
  function arfaly_add_menu_items(){
     add_submenu_page(\Filetrip_Constants::FILETRIP_MAIN_MENU, __("Reporting", 'filetrip-plugin'), __("Reporting", 'filetrip-plugin'), 'edit_posts' ,'arfaly_report_page', array($this, 'arfaly_render_report_menu'));
  }
  
  /**
  * This function is responsible of generating HMTL output of the Reporitng Page
  *
  * @return void
  */
  function arfaly_render_report_menu()
  {
    ob_start();
    
    require_once realpath(__DIR__  . '/../..') . '/views/upload-report-section.php';
    
    $string  = ob_get_contents();
    ob_end_clean();
    
    echo $string;
  }
  
  function arfaly_admin_enqueue() {
    global $typenow;

    if( $typenow == \Filetrip_Constants::POST_STATUS && isset($_GET['page']) && $_GET['page']=='arfaly_report_page') {
        wp_register_script( 'arfaly_reporting_js', $this->plugin_url . '/assets/js/Chart.min.js' ,array(), \Filetrip_Constants::VERSION, true );
        wp_enqueue_script( 'arfaly_reporting_js');
        wp_enqueue_script( 'arfaly_reporting_moment', $this->plugin_url . '/assets/js/moment.min.js' ,array('jquery'), \Filetrip_Constants::VERSION, true );
        wp_enqueue_script( 'arfaly_reporting_datepicker', $this->plugin_url . '/assets/js/jquery.daterangepicker.js' ,array('jquery', 'arfaly_reporting_moment'), \Filetrip_Constants::VERSION, true );
        wp_enqueue_script( 'arfaly_tooltip_js', $this->plugin_url . '/assets/js/tooltip.min.js' ,array('jquery'), \Filetrip_Constants::VERSION, true );
        
        wp_enqueue_script( 'arfaly_reporting_counter', $this->plugin_url . '/assets/js/jquery.counterup.min.js' ,array('jquery'), \Filetrip_Constants::VERSION, true );
        wp_enqueue_script( 'arfaly_reporting_waypoint', $this->plugin_url . '/assets/js/waypoints.min.js' ,array('jquery'), \Filetrip_Constants::VERSION, true );
        
        wp_enqueue_style( 'arfaly_admin_widgets_css', $this->plugin_url . '/assets/css/arfaly-admin-font.css' );
        wp_enqueue_style( 'arfaly_reporting_datepicker', $this->plugin_url . '/assets/css/daterangepicker.css' );
        wp_enqueue_style( 'arfaly_tooltip_css', $this->plugin_url . '/assets/css/tooltip.css' );
    }
  }
  
  /**
  * Return an instance of this class.
  *
  *
  * @return    object    A single instance of this class.
  */
  public static function get_instance() {

      // If the single instance hasn't been set, set it now.
      if ( null == self::$instance ) {
          self::$instance = new self;
      }

      return self::$instance;
  }
  
}
?>
