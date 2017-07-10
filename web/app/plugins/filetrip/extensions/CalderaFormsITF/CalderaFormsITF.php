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
class CalderaFormsITF extends FlareExtension
{
	/**
	 * @var string
	 */
	protected $extension_name = 'Caldera Forms Connector';

	/**
	 * @var string
	 */
	protected $extension_uri = 'https://calderaforms.com/'; // with or without http://

	/**
	 * @var string
	 */
	protected $extension_author = 'iTechFlare';

	/**
	 * @var string
	 */
	protected $extension_author_uri = \Filetrip_Constants::ITF_WEBSITE_LINK;

	/**
	 * @var string
	 */
	protected $extension_version = '1.0.1';

	/**
	 * @var string
	 */
	protected $extension_description = 'Add new Filetrip Upload field in Caldera Forms and connect your next forms to the cloud.';
	/**
	 * @var string
	 *      fill with full URL to Extension icon
	 *      please use Square recommendation is :
	 *      128px square max 256px
	 *      Extension must be jpg or jpeg
	 */
	protected $extension_icon; // fill with icon url

	/**
	 * @var capability
	 */
	protected $capability = 'none';

	/**
	 * Initials
	 */
	public function init()
	{
		// ************************* Caldera Integration Section *************
		// include
		LoaderOnce::load( __DIR__ .'/caldera/classes/filetrip-caldera.php');
		LoaderOnce::load( __DIR__ .'/caldera/field_processors.php');

		// Add caldera extension
		Filetrip_Caldera::get_instance();
	}
}

