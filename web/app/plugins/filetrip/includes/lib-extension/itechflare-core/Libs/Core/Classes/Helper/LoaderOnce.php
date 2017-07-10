<?php
namespace iTechFlare\WP\Plugin\FileTrip\Core\Helper;

/**
 * Class LoaderOnce
 * @package iTechFlare\WP\Plugin\FileTrip\Core\Helper
 * @final
 */
final class LoaderOnce
{
	public static function load($file)
	{
		static $loaded_file = array();

		if (is_string($file)) {
			$path = realpath($file);
			if ($path) {
				if (! isset($loaded_file[$path])) {
					$loaded_file[ $path ] = File::getInstance()->isFile($path);
					if ($loaded_file[ $path ]) {
						self::loadProtectFile($path);
					}
				}
				return $loaded_file[$path];
			}
		}

		return false;
	}

	private static function loadProtectFile($path)
	{
		/** @noinspection PhpIncludeInspection */
		require_once $path;
	}
}
