<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace iTechFlare\WP\iTechFlareExtension;

/**
 * Description of arfaly-caldera
 *
 * @author aelbuni
 */

class Filetrip_Caldera
{
  
  protected static $instance = null;
  
    function __construct() {
      
      // If form integration is enabled, ask the user to install Caldera Forms
      if( !class_exists('Caldera_Forms'))
      {
        add_action( 'admin_notices', array($this,'my_admin_error_notice')); 
      }

      add_filter('caldera_forms_get_field_types', array( $this, 'set_filetrip_field_type'), 1);
    }
  
    function my_admin_error_notice() {
      $msg = 'The '.\Filetrip_Constants::PLUGIN_NAME.' plugin needs <a target="_blank" href="https://en-ca.wordpress.org/plugins/caldera-forms/">Caldera Forms</a> so you can build forms';
      $class = "error";
      echo '<div class="'.$class.'"> <p>'.$msg.'</p></div>'; 
    }
  
  /**
    * Set new Caldera element for Arfaly 
    *
    */
  function set_filetrip_field_type($fields)
  {
    
    $new_fields = array(
			'filetrip_uploader' => array(
				"field"		=>	__("Filetrip Uploader", 'filetrip-plugin'),
				"description" => __('Filetrip Uploader', 'filetrip-plugin'),
				"file"		=>	realpath(__DIR__  . '/..') . "/fields/field.php",
				"viewer"	=>	array($this, 'handle_file_view'),
				"category"	=>	__("Basic", 'filetrip-plugin').', '.__("File", 'filetrip-plugin'),
				"setup"		=>	array(
					"preview"	=>	realpath(__DIR__  . '/..') . "/fields/preview.php",
					"template"	=>	realpath(__DIR__  . '/..') . "/fields/config_template.php"
				)
			)
		);
    return array_merge( $fields, $new_fields );
    
  }
    
  /**
    * Output markup for file fields
    *
    * @param array $value Saved file paths
    * @param array $field Field config
    * @param array $form Form config
    *
    * @return string
    */
  public static function handle_file_view($value, $field, $form){
      $out = array();
      foreach( (array) $value as $file_url ){
          $out[] = '<a href="' . $file_url .'" target="_blank">' . basename($file_url) .'</a>';
      }
      return implode(', ', $out );
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
