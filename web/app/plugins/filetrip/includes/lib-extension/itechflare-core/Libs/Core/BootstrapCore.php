<?php
/**
 * PSR0 Auto Loader
 */
return spl_autoload_register(function ($className) {
	$baseDir   = str_replace('\\', '/', __DIR__) . '/Classes/';
	$nameSpace = 'iTechFlare\\WP\\Plugin\\FileTrip\\Core';
	$className = ltrim($className, '\\');
	// doing check
	if (class_exists($className) || stripos($className, $nameSpace) !== 0) {
		return;
	}
	$className = substr($className, strlen($nameSpace));
	$className = str_replace('\\', '/', ltrim($className, '\\'));
	if (file_exists($baseDir . $className . '.php')) {
		/** @noinspection PhpIncludeInspection */
		require_once $baseDir . $className . '.php';
	}
});
