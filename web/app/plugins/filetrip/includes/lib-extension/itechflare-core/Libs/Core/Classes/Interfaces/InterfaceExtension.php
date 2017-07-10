<?php
/**
 * Interface of Module
 */
namespace iTechFlare\WP\Plugin\FileTrip\Core\Interfaces;

interface InterfaceExtension
{
	public static function extensionGetInstance();
	public function extensionGetName();
	public function extensionGetUri();
	public function extensionGetAuthor();
	public function extensionGetAuthorUri();
	public function extensionGetVersion();
}
