<?php
namespace iTechFlare\WP\Plugin\FileTrip\Core;

use iTechFlare\WP\Plugin\FileTrip\Core\Abstracts\FlareExtension;
use iTechFlare\WP\Plugin\FileTrip\Core\Helper\ReflectorHelper;

final class ExtensionLoader
{
	/**
	 * @var array
	 */
	public static $extension_lists = array();

	/**
	 * @var ExtensionLoader
	 */
	private static $instance;

	/**
	 * @var array
	 */
	public static $status_extension = array();

	/**
	 * ExtensionLoader constructor.
	 */
	public function __construct()
	{
		if (!isset(self::$instance)) {
			self::$instance = $this;
		}
	}

	/**
	 * @return ExtensionLoader
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			return new self();
		}
		return self::$instance;
	}

	/**
	 * @param ExtensionInitiator $extensionInitializer
	 *
	 * @return bool|ExtensionInitiator
	 */
	public function addExtensionsData(ExtensionInitiator $extensionInitializer)
	{
		$name = $extensionInitializer->getName();
		$extensionInitializer->initLoad();
		if (is_string($name)) {
			if (!isset(self::$extension_lists[$name])) {
				self::$status_extension[$name] = $extensionInitializer;
				foreach ($extensionInitializer->getAvailableExtension() as $key => $val) {
					/** @noinspection PhpUndefinedMethodInspection */
					self::$extension_lists[$name][$key] = $val::extensionGetInstance();
				}
			}

			return $extensionInitializer;
		}

		return false;
	}

	/**
	 * Inject Extension
	 *
	 * @param string $name
	 * @param string $extension_class
	 * @return string|bool
	 */
	public function injectExtensionTo($name, $extension_class)
	{
		if (is_string($extension_class) && class_exists($extension_class)
		    && $this->hasExtensions($name)
		    && self::$status_extension[$name]->isInjectable()
			&& is_subclass_of(
			    $extension_class,
                '\\iTechFlare\\WP\\Plugin\\FileTrip\\Core\\Abstracts\\FlareExtension'
            )
		) {
			$list_extensions_name  = array_keys(self::$extension_lists[$name]);
			$list_extensions_class = self::$status_extension[$name]->getAvailableExtension();
			$reflector = new ReflectorHelper($extension_class);
			/**
			 * @var FlareExtension
			 */
			$className = $reflector->getName();
			$className__ = trim($className, '\\');
			if (!in_array($className__, $list_extensions_class) && !in_array($className__, $list_extensions_name)) {
				/** @noinspection PhpUndefinedMethodInspection */
				self::$extension_lists[$name][$className__] = $className::extensionGetInstance();
			}
			return $name;

		}

		return false;
	}

	/**
	 * @param string $name
	 * @param string $selector
	 *
	 * @return mixed
	 */
	public function loadExtension($name, $selector)
	{
		if ($this->getExtension($name, $selector)) {
			$selector = trim($selector, '\\');
			/** @noinspection PhpUndefinedMethodInspection */
			self::$extension_lists[$name][$selector]->callInit();
			return self::$extension_lists[$name];
		}

		return false;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasExtensions($name)
	{
		return (is_string($name) && isset(self::$extension_lists[$name]));
	}

	/**
	 * @param string $name
	 *
	 * @return bool|array
	 */
	public function getExtensions($name)
	{
		if ($this->hasExtensions($name)) {
			return self::$extension_lists[$name];
		}

		return false;
	}

	/**
	 * @param string $name
	 * @param string $extension_name
	 *
	 * @return bool|FlareExtension
	 */
	public function getExtension($name, $extension_name)
	{
		if (!is_string($extension_name) || !is_string($name)) {
			return false;
		}
		$extension = $this->getExtensions($name);
		if (!empty($extension[$extension_name])) {
			return $extension[$extension_name];
		}

		return false;
	}
}
