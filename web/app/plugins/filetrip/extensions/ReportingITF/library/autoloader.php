<?php
/**
 * PSR0 Auto Loader
 */
return spl_autoload_register(function ($originClassName) {	

	$baseDir   =  __DIR__ . '/classes/';

	$classFilePrefix = 'class-';
	$nameSpace = 'iTechFlare\WP\iTechFlareExtension\ReportingITF';
	
	$pathArr = explode('\\', $originClassName);
	$className = end($pathArr);

	// doing check
	if (class_exists($className)) {
		return;
	}

	$className = str_replace("_", "-", $className);
	$importFilename = $baseDir . $classFilePrefix . strtolower($className) . '.php';

	if (file_exists($importFilename)) {
		/** @noinspection PhpIncludeInspection */
		require_once $importFilename;
	}
});
