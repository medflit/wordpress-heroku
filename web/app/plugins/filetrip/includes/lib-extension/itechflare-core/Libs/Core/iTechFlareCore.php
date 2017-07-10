<?php
namespace iTechFlare\WP\Plugin\FileTrip;

use iTechFlare\WP\Plugin\FileTrip\Core\AdminMenu;
use iTechFlare\WP\Plugin\FileTrip\Core\ExtensionInitiator;

/**
 * Class iTechFlareCore
 * @package iTechFlare\WP\Plugin\FileTrip
 */
final class iTechFlareCore
{
	/**
	 * Version
	 */
	const VERSION = \Filetrip_Constants::ITF_CORE_EXTENSION_VER;

	/**
	 * Plugin Name
	 */
	const PLUGIN_NAME = \Filetrip_Constants::PLUGIN_NAME;

	/**
	 * Short name
	 */
	const SHORT_NAME = 'iTechFlareCore';

	/**
	 * @var iTechFlareCore
	 */
	protected static $instance;

	/**
	 * @var AdminMenu
	 */
	protected static $adminMenu;

	/**
	 * @var ExtensionInitiator
	 */
	protected $extensionInitializer;

	// add some core extension directory name here
	private static $core_extension = array(
		'SupportCenter'
    );

	/**
	 * @var array
	 */
	private static $prior_extension = array();

    /**
     * @var string
     */
    protected $capability = 'edit_posts';

	/**
	 * Core constructor.
	 */
	public function __construct()
	{	
		if (!isset(self::$instance)) {
			self::$instance = $this;
			$c = $this;
			$this->extensionInitializer = new ExtensionInitiator(
                ITECH_EXTENSION_DIRECTORY,
                self::SHORT_NAME,
                true // injectable
            );

			self::$adminMenu = new AdminMenu($this->extensionInitializer);

			/**
			 * Admin Menu Hooks
			 */
			add_action('itf_filetrip_admin_loaded_extension', function($itf) use($c) {
				$c->showAdmin();
			}, 1);

			add_action('itf_filetrip_admin_loaded_extension', function($itf) use($c) {
                // doing before extension loaded
                do_action('itf_before_core_loaded', $itf);
                $c::$adminMenu->renderAdminMenu(90);
                do_action('itf_after_core_loaded', $itf);
			}, 40);
		}
	}


    /**
     * Add Extension
     *
     * @param string $className
     * @return bool|string
     */
    public function addExtension($className)
    {
        return self::$adminMenu
            ->getExtensionLoader()
            ->injectExtensionTo(
                self::SHORT_NAME,
                $className
            );
    }

	/**
     * Current Core Url
     *
	 * @return string
	 */
	public static function flareCoreUrl()
	{
		static $url;
		if (!$url) {
			$url = plugins_url('', dirname(__DIR__));
		}
		return $url;
	}

	/**
	 * @return iTechFlareCore
	 */
	protected function showAdmin()
	{
		static $showed;
		$instance = static::getInstance();
		if ($showed) {
			return $instance;
		}

		$showed = true;
		$instance::$adminMenu->setCoreExtensions(static::$core_extension);
		$instance::$adminMenu->setPriorExtension(static::$prior_extension);
		$instance::$adminMenu->setCapability($instance->capability);
		$instance::$adminMenu->setMenuTitle(static::PLUGIN_NAME);
		$instance::$adminMenu->setPageTitle(
		    __(static::PLUGIN_NAME . ' Extensions', 'filetrip-plugin')
        );

		// Add Menu Page
		$instance::$adminMenu->setAdminMenuPage(function() {
			require __DIR__ . '/Screens/AdminMenuPage.php';
		});
        $instance::$adminMenu->initExtensions();
		return $instance;
	}

	/**
	 * @return iTechFlareCore
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		$instance = self::getInstance();
		return $instance::VERSION;
	}

	/**
	 * Get Plugin Name
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		$instance = self::getInstance();
		return $instance::PLUGIN_NAME;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getName();
	}

	/**
	 * @return string
	 */
	public static function getShortName()
	{
		$instance = self::getInstance();
		return $instance::SHORT_NAME;
	}

	/**
	 * @return string
	 */
	public static function getSlugExtension()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getSlugExtension();
	}

	/**
	 * @return string
	 */
	public static function getSlug()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getSlug();
	}

	/**
	 * @return string
	 */
	public static function getPageTitle()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getPageTitle();
	}

	/**
	 * @return string
	 */
	public static function getMenuTitle()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getMenuTitle();
	}

	/**
	 * @return string
	 */
	public static function getCapability()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getCapability();
	}
	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public static function getExtension($name)
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getExtension($name);
	}

	/**
	 * @return array
	 */
	public static function getAllAvailableExtensions()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getAllActiveExtensions();
	}

	/**
	 * @return array
	 */
	public static function getAllActiveExtensions()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getAllActiveExtensions();
	}

	/**
	 * @return array
	 */
	public static function getDBActiveExtensions()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getDBActiveExtensions();
	}

	/**
	 * @return string
	 */
	public static function getNonce()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getNonce();
	}

	/**
	 * @return bool|false|int
	 */
	public static function verifyNonce()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->verifyNonce();
	}

	/**
	 * Add Sub Menu Page
	 *
	 * @param string    $page_title
	 * @param string    $menu_title
	 * @param string    $capability
	 * @param string    $slug
	 * @param callable  $fn
	 * @param int       $priority
	 *
	 * @return string|bool
	 */
	public static function addSubMenuPage(
	    $page_title,
        $menu_title,
        $capability,
        $slug,
        $fn,
        $priority = 0
    ) {
		$instance = self::getInstance();
		return $instance::$adminMenu
            ->addSubMenuPage(
                $page_title,
                $menu_title,
                $capability,
                $slug,
                $fn,
                $priority
            );
	}

	/**
     * Get Extension Loader Object
     *
	 * @return Core\ExtensionLoader
	 */
	public static function getExtensionLoader()
	{
		$instance = self::getInstance();
		return $instance::$adminMenu->getExtensionLoader();
	}
}
