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
use iTechFlare\WP\Plugin\FileTrip\iTechFlareCore;
use iTechFlare\WP\iTechFlareExtension\ReportingITF\Arfaly_Report_Page;

/**
 * Class Example
 * @package iTechFlare\WP\iTechFlareExtension
 */
class ReportingITF extends FlareExtension
{
	/**
	 * @var string
	 */
	protected $extension_name = 'Filetrip Reporting';

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
	protected $capability = 'edit_posts';

	/**
	 * @var string
	 */
	protected $extension_description = 'Get in detailed reports about who upload what, where and when.';
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
		// do module
		if (current_user_can($this->capability)) {
			// include
			LoaderOnce::load( __DIR__ .'/library/autoloader.php');
			
			$report_page = new Arfaly_Report_Page($this->extensionGetDirectoryUrl());
		}
	}

}

