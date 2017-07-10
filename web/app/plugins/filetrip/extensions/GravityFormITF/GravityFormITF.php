<?php
/**
 * Example Extension
 *      Example extension just like below
 *      Use Extension Name Unique as Possible, because same Name Will Be [Override Able]
 *
 * @method init() as initialization after active
 */
namespace iTechFlare\WP\iTechFlareExtension;

use iTechFlare\WP\Plugin\FileTrip\Core\Abstracts\FlareExtension;
use iTechFlare\WP\Plugin\FileTrip\Core\Helper\LoaderOnce;

/**
 * Class Example
 * @package iTechFlare\WP\iTechFlareExtension
 */
class GravityFormITF extends FlareExtension
{
	/**
	 * @var string
	 */
	protected $extension_name = 'Gravity Forms Connector';

	/**
	 * @var string
	 */
	protected $extension_uri = 'https://itechflare.com'; // with or without http://

	/**
	 * @var string
	 */
	protected $extension_author = 'iTechFlare';

	/**
	 * @var string
	 */
	protected $extension_author_uri = 'https://itechflare.com';

	/**
	 * @var string
	 */
	protected $extension_version = '1.0.1';

	/**
	 * @var capability
	 */
	protected $capability = 'none';

	/**
	 * @var string
	 */
	protected $extension_description = 'Add new Filetrip Upload field in Gravity Forms and connect your next forms to the cloud.';
	/**
	 * @var string
	 *      fill with full URL to Extension icon
	 *      please use Square recommendation is :
	 *      128px square max 256px
	 *      Extension must be jpg or jpeg
	 */
	protected $extension_icon; // fill with icon url
	/**
	 * Initials
	 */
	public function init()
	{
		/**
		* If Gravity Forms not installed ask for installation/activation and exit 
		*/
		if(!class_exists('GFCommon'))
		{
			add_action( 'admin_notices', array($this,'my_admin_error_notice')); 
			return;
		}

		// include
		LoaderOnce::load( __DIR__ .'/gravity-form/config-template.php');
		LoaderOnce::load( __DIR__ .'/gravity-form/class-gf-field-filetrip.php');

		add_action( 'gform_enqueue_scripts', array($this, 'load_filetrip_scripts'), 10, 2 );
		add_filter( 'gform_preview_styles', array($this, 'load_filetrip_styles'), 10, 2 );
	}

	function my_admin_error_notice() {
      $msg = 'The '.\Filetrip_Constants::PLUGIN_NAME.' plugin needs <a target="_blank" href="http://www.gravityforms.com/">Gravity Forms</a> so you can build forms';
      $class = "error";
      echo '<div class="'.$class.'"> <p>'.$msg.'</p></div>'; 
    }

	// Enqueue plugin scripts
	function load_filetrip_scripts($form, $is_ajax) {

		wp_enqueue_script(
				'filetrip-multi-script',
				ITECH_FILETRIP_PLUGIN_URL.'/assets/js/filetrip-multi-min.js', array('jquery'), \Filetrip_Constants::VERSION, true
			);
	}

	// Enqueue plugin styles
	function load_filetrip_styles($styles, $form) {
		wp_register_style( 'filetrip-default',
				ITECH_FILETRIP_PLUGIN_URL.'/assets/css/style.css', array(), \Filetrip_Constants::VERSION);
		$styles = array( 'filetrip-default' );
		return $styles;
	}

	
}

