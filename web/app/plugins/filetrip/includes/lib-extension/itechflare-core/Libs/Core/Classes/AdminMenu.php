<?php
namespace iTechFlare\WP\Plugin\FileTrip\Core;

use iTechFlare\WP\Plugin\FileTrip\iTechFlareCore;
use iTechFlare\WP\Plugin\FileTrip\Core\Filetrip;

final class AdminMenu
{
	/**
	 * @var ExtensionInitiator
	 */
	protected $extensionInitializer;

	/**
	 * @var ExtensionLoader
	 */
	protected $extensionLoader;

	/**
	 * @var string
	 */
	protected $extensionDirectory;

	/**
	 * @var string
	 */
	protected $shortName;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $slug;

	/**
	 * @var string
	 */
	protected $parent_slug;

	/**
	 * @var string
	 */
	protected $menu_title = 'ITF Core';

	/**
	 * @var string
	 */
	protected $page_title = 'ITF Core';

	/**
	 * @var string
	 */
	protected $capability = 'edit_posts';

	/**
	 * @var array
	 */
	protected $available_extensions = array();

	/**
	 * @var array
	 */
	protected $core_extension = array(
		'SupportCenter'
    );

	/**
	 * @var array
	 */
	protected $prior_extension = array(
	);

	/**
	 * @var array
	 */
	protected $active_extensions_additional = array(
	);
	/**
	 * @var callable
	 */
	protected $adminDisplay = null;

	/**
	 * @var bool
	 */
	protected $show_core_plugins = true;

	/**
	 * @var bool
	 */
	protected $extension_must_be_stopped = false;

	/**
	 * AdminMenu constructor.
	 *
	 * @param ExtensionInitiator $extensionInitializer
	 */
	public function __construct( ExtensionInitiator $extensionInitializer ) {
		$this->extensionDirectory = $extensionInitializer->getExtensionDirectory();
		if ($this->extensionDirectory && is_string($this->extensionDirectory) && is_dir($this->extensionDirectory)) {
			$name = $extensionInitializer->getName();
			if (!empty($name)) {
				$this->extensionLoader      = ExtensionLoader::getInstance();
				$this->extensionInitializer =& $extensionInitializer;
				$this->name                 = $name;
				$this->slug                 = \Filetrip_Constants::MAIN_MENU_SLUG ;
				$this->parent_slug  		= \Filetrip_Constants::MAIN_MENU_PARENT_SLUG;
			}
		}
	}

	/**
	 * @param $isEnable
	 */
	public function setEnableOrDisableCore($isEnable)
	{
		$this->show_core_plugins = (bool) $isEnable;
	}

	/**
	 * @return bool
	 */
	public function isCoreExtensionShown()
	{
		return $this->show_core_plugins;
	}

	/**
	 * @return bool
	 */
	public function isValid()
	{
		return (bool) $this->name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param callable $menuPage
	 */
	public function setAdminMenuPage($menuPage)
	{
		if (!$this->isValid()) {
			return;
		}
		if (is_callable($menuPage) && !is_callable($this->adminDisplay)) {
			$this->adminDisplay = $menuPage;
		}
	}

	/**
	 * @param array $core_extension
	 */
	public function setCoreExtensions(array $core_extension)
	{
		if (empty($core_extension)) {
			$this->core_extension = $core_extension;
		}
	}

	/**
	 * @param array $prior_extension
	 */
	public function setPriorExtension(array $prior_extension)
	{
		if (empty($prior_extension)) {
			$this->prior_extension = $prior_extension;
		}
	}

	/**
	 * @param string $name
	 */
	public function setMenuTitle($name)
	{
		$this->menu_title = $name;
	}

	/**
	 * @param string $name
	 */
	public function setPageTitle($name)
	{
		$this->page_title = $name;
	}

	/**
	 * @param string $name
	 */
	public function setCapability($name)
	{
		$this->capability = $name;
	}

	/**
	 * @return string|bool
	 */
	public function getSlugExtension()
	{
		if (!$this->isValid()) {
			return false;
		}
		return $this->getSlug().'_extension';
	}

	/**
	 * @return bool|string
	 */
	public function getSlug()
	{
		if (!$this->isValid()) {
			return false;
		}

		return $this->slug;
	}

	/**
	 * @return bool|string
	 */
	public function getParentSlug()
	{
		if (!$this->isValid()) {
			return false;
		}

		return $this->parent_slug;
	}

	/**
	 * @return string|bool
	 */
	public function getPageTitle()
	{
		if (!$this->isValid()) {
			return false;
		}
		return $this->page_title;
	}

	/**
	 * @return string
	 */
	public function getMenuTitle()
	{
		return $this->menu_title;
	}

	/**
	 * @return string
	 */
	public function getCapability()
	{
		return $this->capability;
	}

	/**
	 * @access private
	 */
	private function _extensionEnqueueScript()
	{
		wp_enqueue_style(
			'itf-wp-extension-css',
			dirname($this->libraryUrl()) . '/assets/css/admin-extension.css',
			array(),
			iTechFlareCore::VERSION
		);
		wp_enqueue_script(
			'itf-wp-extension-js',
			dirname($this->libraryUrl()) . '/assets/js/admin-extension.js',
			array('jquery'),
			iTechFlareCore::VERSION,
			true
		);
	}

	/**
	 * @param int|null $position
	 * @param string $icon_url
	 */
	public function renderAdminMenu($position = null, $icon_url = '')
	{
		if (!$this->isValid()) {
			return;
		}

		static $set;
		if ($set) {
			return;
		}

		$this->initResponseExtensions();
		$this->initExtensionRecondition();
		$this->loadActiveExtensions();
		$set = true;
		$c =& $this;
		add_action('admin_enqueue_scripts', function() use($c) {
			$c->_extensionEnqueueScript();
		});
		$adminMenuPage = $c->adminDisplay;
		add_action('admin_menu', function() use ($c, $position, $icon_url, $adminMenuPage) {
			if (isset($_GET['page']) && strpos($_GET['page'], $c->getSlug()) === 0) {
				/**
				 * Only Available if Admin Notices as Admin Slug
				 */
				/*global $wp_filter;
				if ( current_user_can( 'activate_plugins' ) ) :
					$wp_filter['admin_notices'] = isset( $wp_filter['admin_notices'][ $c->getSlug() ] )
						? array( $c->getSlugExtension() => $wp_filter['admin_notices'][ $c->getSlug() ] )
						: array();
					if ( isset( $wp_filter['all_admin_notices'] ) ) {
						unset( $wp_filter['all_admin_notices'] );
					}
				endif;*/
			}
			$adminDisplay = is_callable($adminMenuPage)
						? $adminMenuPage
						: false;

			// Main Page About
			/*add_menu_page(
				$c->getPageTitle(),
				$c->getMenuTitle(),
				$c->getCapability(),
				$c->getSlug(),
				'',
				$icon_url,
				$position
			);

			if ($adminDisplay !== false) {
				// Extension Page
				add_submenu_page(
					$c->getSlug(),
					$c->getPageTitle(),
					$c->getMenuTitle(),
					$c->getCapability(),
					$c->getSlug(),
					$adminDisplay
				);
			}*/

			// Extension Page
			add_submenu_page(
				$c->parent_slug,
				__('Extensions', 'filetrip-plugin'),
				__('Extensions', 'filetrip-plugin'),
				$this->capability,
				$c->getSlugExtension(),
				array($c, 'renderExtensionPage')
			);

			if ($adminDisplay === false) {
				remove_submenu_page( $c->getSlug(), $c->getSlug() );
			}
		}, 10);
	}

	/**
	 * @return void
	 */
	public function renderExtensionPage()
	{
		if (!$this->isValid()) {
			return;
		}
		require_once dirname(__DIR__) . '/Screens/Extension.php';
	}

	/**
	 * @return void
	 */
	public function initExtensions()
	{
		if (!$this->isValid()) {
			return;
		}

		if (!is_string($this->extensionDirectory)) {
			return;
		}

		static $loaded;
		if ($loaded) {
			return;
		}

		$loaded = true;
		$data = $this
            ->extensionLoader
            ->addExtensionsData( $this->extensionInitializer );
		$this->extensionInitializer =& $data;
	}

	/**
	 * @return void
	 */
	private function initResponseExtensions()
	{
		if (!$this->isValid()) {
			return;
		}

		// save available modules
		$available_modules = $this->extensionLoader->getExtensions(
			$this->extensionInitializer->getName()
		);

		if (is_array($available_modules)) {
			$this->available_extensions = array_keys($available_modules);
		}

		$options = $this->getDBActiveExtensions();
		$new_options = $this->getDBActiveExtensions();
		$new_options = array_unique($new_options);
		foreach ($options as $key => $value) {
			if (! is_string($value) || ! in_array($value, $this->available_extensions)) {
				unset($new_options[$value]);
				continue;
			}
			$this->active_extensions_additional[] = $value;
		}
		$this->active_extensions_additional = array_unique($this->active_extensions_additional);
		if ($options !== $new_options) {
			update_option($this->extensionInitializer->getOptionName(), array_values($new_options));
		}
	}


	/**
	 * @return void
	 */
	private function initExtensionRecondition()
	{
		if (!$this->isValid()) {
			return;
		}

		$extension_ = isset($_GET['extension']) && is_string($_GET['extension'])
			? str_replace('-', '\\', urldecode_deep($_GET['extension']))
			: null;
		if (isset($_SERVER['HTTP_REFERER'])
		    && $extension_
		    && strpos($_SERVER['HTTP_REFERER'], $this->getSlugExtension())
		    && isset($_GET['page']) && $_GET['page'] == $this->getSlugExtension()
		    && in_array($extension_, $this->available_extensions)
			&& $this->verifyNonce()
		) {
			if (! isset($_GET['extension_action'])
			    && isset($_GET['extension_status']) && is_string($_GET['extension_status'])
			    && in_array($_GET['extension_status'], array('activated', 'deactivated'))
			) {
				$extension_name = $this->extensionLoader->getExtension(
					$this->extensionInitializer->getName(),
					$extension_
				);

				/** @noinspection PhpUndefinedMethodInspection */
				$extension_name = empty( $extension_name )
					? $extension_
					: $extension_name->extensionGetName();

				add_action( 'admin_notices', function () use ( $extension_name ) {
					$extension_action = $_GET['extension_status'];
					$extension_action = $extension_action == 'activated'
						? __( 'activated', 'filetrip-plugin' )
						: __( 'deactivated', 'filetrip-plugin' );
					echo '<div class="notice notice-success is-dismissible"><p>'
					     . sprintf( __( 'Extension %1$s successfully %2$s', 'filetrip-plugin' ),
							"<strong>{$extension_name}</strong>",
							$extension_action
					     )
					     . '</p></div>';
				}, $this->getSlug() );
			}

			if (isset($_GET['extension_action'])
			    && is_string($_GET['extension_action'])
			    && in_array($_GET['extension_action'], array('activate', 'deactivate'))
			) {
				$extension_name = $extension_;
				$new_options = $this->getDBActiveExtensions();
				if (in_array($extension_name, $this->available_extensions) && ! in_array($extension_name, $this->core_extension)) {
					if ($_GET['extension_action'] == 'activate') {
						if (!in_array($extension_name, $this->active_extensions_additional)) {
							$new_options[]                        = $extension_name;
							$this->active_extensions_additional[] = $extension_name;
							update_option($this->extensionInitializer->getOptionName(), $new_options);
							if (!headers_sent()) {
								wp_safe_redirect(
									admin_url(
										'admin.php?page='
										. $this->getSlugExtension() . '&extension='
										. str_replace('\\', '-', $extension_name)
										. '&extension_status=activated&_wpnonce=' . $this->getNonce()
									)
								);
								exit;
							}
						}
					} else {
						if (in_array($extension_name, $this->active_extensions_additional)) {
							$key = array_search($extension_name, $this->active_extensions_additional);
							if (isset($this->active_extensions_additional[$key])) {
								unset($this->active_extensions_additional[$key]);
								update_option($this->extensionInitializer->getOptionName(), $this->active_extensions_additional);
								if (!headers_sent()) {
									wp_safe_redirect(
										admin_url(
											'admin.php?page='
											. $this->getSlugExtension() . '&extension='
											. str_replace('\\', '-', $extension_name) . '&extension_status=deactivated&_wpnonce=' . $this->getNonce()
										)
									);
									exit;
								}
							}
						}
					}
				}
			}
		}
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
	public function addSubMenuPage($page_title, $menu_title, $capability, $slug, $fn, $priority = 0)
	{
		if (!is_string($page_title) || !is_string($menu_title)
		    || !is_string($capability) || !is_callable($fn)
	        || !is_string($slug) || trim($slug) == ''
			|| !is_callable($fn)
		) {
			return false;
		}

		$c =& $this;
		$priority = ! is_numeric($priority) ? 0 : absint($priority);
		$priority = 20 + $priority;
		$slug = $this->getSlug() . '_' . $slug;
		add_action('admin_menu', function() use($c, $page_title, $menu_title, $capability, $slug, $fn) {
			add_submenu_page(
				$c->getParentSlug(),
				$page_title,
				$menu_title,
				$capability,
				$slug,
				$fn
			);
		}, $priority);

		return $slug;
	}

	/**
	 * @param string $name
	 *
	 * @return bool|mixed
	 */
	public function getExtension($name)
	{
		if (!$this->isValid()) {
			return false;
		}
		return $this->extensionLoader->getExtension($this->name, $name);
	}

	/**
	 * @return void
	 */
	private function loadActiveExtensions()
	{
		if (!$this->isValid()) {
			return;
		}

		static $load;
		if ($load) {
			return;
		}
		$load = true;
		$name = $this->extensionInitializer->getName();
		/**
		 * Loop
		 */
		foreach ($this->getAllActiveExtensions() as $extension) {
			$this->extensionLoader->loadExtension($name, $extension);
		}
	}

	/**
	 * @return array
	 */
	public function getAllAvailableExtensions()
	{
		return $this->available_extensions;
	}

	/**
	 * @return array
	 */
	public function getAllActiveExtensions()
	{
		$return_value = $this->core_extension;
		if (!empty($this->prior_extension) && !empty($this->active_extensions_additional)) {
			foreach ( $this->prior_extension as $k => $v ) {
				if ( ! in_array( $v, $this->active_extensions_additional ) ) {
					unset( $this->prior_extension[ $k ] );
				}
			}
			$this->prior_extension = array_values($this->prior_extension);
		}
		$return_value = array_merge($return_value, $this->prior_extension);
		$return_value = array_merge($return_value, $this->active_extensions_additional);
		return array_unique($return_value);
	}

	/**
	 * @return array
	 */
	public function getDBActiveExtensions()
	{
		return $this->extensionInitializer->getDataOption();
	}

	/**
	 * @return string
	 */
	public function getNonce()
	{
		return $this->extensionInitializer->getNonce();
	}

	/**
	 * @return bool|false|int
	 */
	public function verifyNonce()
	{
		return $this->extensionInitializer->verifyNonce('_wpnonce');
	}

	/**
	 * @return ExtensionLoader
	 */
	public function &getExtensionLoader()
	{
		return $this->extensionLoader;
	}

	/**
	 * @return array
	 */
	public function getCoreExtension()
	{
		return $this->core_extension;
	}

	/**
	 * @return string
	 */
	public function libraryUrl()
	{
		static $url;
		if (!$url) {
			$url = plugins_url('', dirname(__DIR__));
		}
		return $url;
	}

}
