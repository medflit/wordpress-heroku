<?php
namespace iTechFlare\WP\Plugin\FileTrip\Core\Abstracts;

use iTechFlare\WP\Plugin\FileTrip\Core\Helper\ReflectorHelper;
use iTechFlare\WP\Plugin\FileTrip\Core\Interfaces\InterfaceExtension;

abstract Class FlareExtension implements InterfaceExtension
{
	/**
	 * @var string
	 */
	protected $extension_name;

	/**
	 * @var string
	 */
	protected $extension_uri;

	/**
	 * @var string
	 */
	protected $extension_author;

	/**
	 * @var string
	 */
	protected $extension_author_uri;

	/**
	 * @var string
	 */
	protected $extension_version;

	/**
	 * @var string
	 */
	protected $extension_description;

	/*
	 * ------------------------------------------
	 * Un override able
	 * ------------------------------------------
	 */

	/**
	 * @var string
	 */
	protected $extension_icon;

	/**
	 * @var string
	 */
	protected $extension_path;

	/**
	 * @var array
	 */
	private static $extension_instance = array();

	/**
	 * @var bool
	 */
	private $extension_loaded = false;

	/**
	 * Initialize after Module Loaded
	 * @return void
	 */
	public function init()
	{
		// doing init
	}

	/**
	 * Call Init Extensions - Only can call once
	 *
	 * @final
	 * @return bool
	 */
	final public function callInit()
	{
		if (!$this->extensionHasLoaded()) {
			// call init
			$this->init();
			// set loaded
			$this->extension_loaded = true;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	final public function extensionHasLoaded()
	{
		return $this->extension_loaded;
	}

	/**
	 * FlareExtension constructor.
	 */
	final public function __construct()
	{
		if (!self::$extension_instance) {
			$this->extensionGetInfo();
			self::$extension_instance[$this->extensionGetClassName()] = $this;
		}
	}

	/**
	 * @return FlareExtension|static
	 */
	final public static function extensionGetInstance()
	{
		$class = get_called_class();
		if (!isset(self::$extension_instance[$class])) {
			self::$extension_instance[$class] = new static;
		}

		return self::$extension_instance[$class];
	}

	/**
	 * @return ReflectorHelper
	 */
	final private function privateReflector()
	{
		static $reflector = array();
		$class = get_class($this);
		if (!isset($reflector[$class])) {
			$reflector[$class] = new ReflectorHelper($this);
		}
		return $reflector[$class];
	}

	/**
	 * @return ReflectorHelper
	 */
	final public function getReflector()
	{
		return clone $this->privateReflector();
	}

	/**
	 * get Module information
	 *
	 * @final
	 * @return array
	 */
	final public function extensionGetInfo()
	{
		return array(
			'extension_name' => $this->extensionGetName(),
			'extension_icon' => $this->extensionGetIcon(),
			'extension_uri'  => $this->extensionGetUri(),
			'extension_author' => $this->extensionGetAuthor(),
			'extension_author_uri' => $this->extensionGetAuthorUri(),
			'extension_version' => $this->extensionGetVersion(),
			'extension_class_name' => $this->extensionGetClassName(),
			'extension_path' => $this->extensionGetPath(),
			'extension_namespace' => $this->extensionGetNameSpace(),
		);
	}

	/**
	 * @return string
	 */
	public function extensionGetDirectoryUrl()
	{
		static $url;
		if (isset($url)) {
			return $url;
		}

		$base = realpath(ABSPATH);
		$directory = realpath(dirname($this->extensionGetPath()));
		$baseDir = str_replace('\\', '/', substr($directory, strlen($base)));
		$url = rtrim(home_url($baseDir), '/');
		return $url;
	}

	/**
	 * @return string
	 */
	public function extensionGetIcon()
	{
		static $icon;
		if (isset($icon)) {
			$this->extension_icon = $icon;
			return $icon;
		}
		if (is_string($this->extension_icon)) {
			$ext_icon = explode( '?', $this->extension_icon );
			$ext_icon = reset( $ext_icon );
			if ( ( substr( $ext_icon, - 4 ) == '.png'
			       || substr( $ext_icon, - 4 ) == '.jpg'
			     )
			     && strpos( substr( $ext_icon, 0, - 4 ), '/' ) !== false
			     && strpos( substr( $ext_icon, - 4 ), '.' ) !== false
			) {
				$this->extension_icon = esc_url_raw( $this->extension_icon, 'http' );
				if ( $ext_icon != '' ) {
					$icon = preg_replace( '#^(https?)\:#i', '', $this->extension_icon );
					return $icon;
				}
			}
		}

		$icon = '';
		$name = 'icon';
		$preferable = array('png', 'jpg');
		$directory = dirname($this->extensionGetPath());
		foreach ($preferable as $value) {
			if (is_file($directory . DIRECTORY_SEPARATOR . $name . ".{$value}")) {
				$icon = $this->extensionGetDirectoryUrl() . "/{$name}.{$value}";
				$icon = preg_replace('#^(https?)\:#i', '', $icon);
				break;
			}
		}

		$this->extension_icon = $icon;
		return $icon;
	}

	/**
	 * @return string
	 */
	public function extensionGetPath()
	{
		$this->extension_path = $this->privateReflector()->getFileName();
		return $this->extension_path;
	}

	/**
	 * @return string
	 */
	public function extensionGetNameSpace()
	{
		return $this->privateReflector()->getAbstractNameSpace();
	}

	/**
	 * Get Class Inheritance
	 *
	 * @final
	 * @return string
	 */
	final public function extensionGetClassName()
	{
		return $this->privateReflector()->getName();
	}

	/**
	 * @return string
	 */
	final public function extensionGetName()
	{
		if (!is_string($this->extension_name)) {
			$this->extension_name = $this->extensionGetClassName();
		}
		return $this->extension_name;
	}

	/**
	 * @return string
	 */
	final public function extensionGetUri()
	{
		static $extension_uri;
		if (!isset($extension_uri)) {
			$extension_uri = !is_string($this->extension_uri)
                || strpos($this->extension_uri, '.') === false
                || strpos($this->extension_uri, '://')
                    && preg_match('#(https?)?\:\/\/#i', $this->extension_uri, $match)
                    && empty($match[1])
				? ''
				: esc_url_raw($this->extension_uri, 'http');
		}

		$this->extension_uri = $extension_uri;

		return $this->extension_uri;
	}

	/**
	 * @return string
	 */
	final public function extensionGetAuthor()
	{
		static $author;
		if (!isset($author)) {
			$author = !is_string($this->extension_author)
				? ''
				: esc_html(strip_tags($this->extension_author));
		}

		$this->extension_author = $author;

		return $this->extension_author;
	}

	/**
	 * @return string
	 */
	final public function extensionGetAuthorUri()
	{
		static $extension_author_uri;
		if (!isset($extension_author_uri)) {
			$extension_author_uri = !is_string($this->extension_author_uri)
				|| strpos($this->extension_author_uri, '.') === false
				|| strpos($this->extension_author_uri, '://')
				   && preg_match('#(https?)?\:\/\/#i', $this->extension_author_uri, $match)
				   && empty($match[1])
				? ''
				: esc_url_raw($this->extension_author_uri, 'http');
		}

		$this->extension_author_uri = $extension_author_uri;

		return $this->extension_author_uri;
	}

	/**
	 * @return bool|string
	 */
	final public function extensionGetVersion()
	{
		static $extension_version;
		if (!isset($extension_version)) {
			$extension_version = false;
			if (in_array(gettype($this->extension_version), array('float', 'int', 'string'))) {
				$extension_version = trim($this->extension_version);
			}
		}

		$this->extension_version = $extension_version;

		return $this->extension_version;
	}

	/**
	 * @return string
	 */
	final public function extensionGetDescription()
	{
		static $description;
		if (!isset($description)) {
			$description = !is_string($this->extension_description)
				? ''
				: strip_tags($this->extension_description);
		}

		if (!is_string($this->extension_description)) {
			$this->extension_description = $description;
		}

		return trim($this->extension_description) != '' ? esc_html(__($this->extension_description, 'filetrip-plugin')) : '';
	}
}
