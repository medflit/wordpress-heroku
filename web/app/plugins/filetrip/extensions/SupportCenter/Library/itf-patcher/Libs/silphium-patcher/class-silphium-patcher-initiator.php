<?php
namespace iTechFlare\WP\iTechFlareExtension\SupportCenter\Patcher;

use iTechFlare\WP\iTechFlareExtension\SupportCenter;
use iTechFlare\WP\iTechFlareExtension\SupportCenter\Patcher\Silphium_Patcher;

/**
 * Class Patcher
 * @package iTechFlare\WP\iTechFlareExtension\SupportCenter\Patcher
 */
class Silphium_Patcher_Initiator
{
	/**
	 * @var SupportCenter
	 */
	protected $bundle;

	/**
	 * Patcher constructor.
	 *
	 * @param SupportCenter $bundle
	 */
	public function __construct(SupportCenter $bundle)
	{
		if ( is_admin() ) {
			$this->bundle = $bundle;
			// TODO Doing call instance
		}
	}

	/**
	 * Patcher Load
	 */
	public function load()
	{
		static $load;
		if (!isset($load)) {
			$load = true;
			if ($this->bundle) {
				$this->bundle->addBundleTab(
					'patcher',
					array(
						'include' => realpath(__DIR__  . '/../..') . '/Screens/fix-update-tab.php',
						'slug'    => 'patcher',
						'title'   => __( 'Fix Center', 'filetrip-plugin' ),
						'action'  => 'itechflare/extensions/support_pages/patcher'
					)
				);
			}
		}

		/**
		* Instantiate the plugin class
		*/
		Silphium_Patcher::get_instance();

	}

	/**
	 * Get Current Patcher URL
	 *
	 * @return string
	 */
	public function getPatcherUrl()
	{
		return plugins_url('', __FILE__);
	}
}
