<?php
namespace iTechFlare\WP\Plugin\FileTrip\Core;

use iTechFlare\WP\Plugin\FileTrip\Core\Helper\File;
use iTechFlare\WP\Plugin\FileTrip\Core\Helper\LoaderOnce;
use iTechFlare\WP\Plugin\FileTrip\Core\Helper\ReflectorHelper;

final class ExtensionInitiator
{
	/**
	 * @var string
	 */
	protected $extension_directory;

	/**
	 * @var File
	 */
	protected $file_helper;

	/**
	 * @var bool
	 */
	protected $extension_directory_valid;

	/**
	 * @var string
	 */
	protected $name_extension;

	/**
	 * @var bool
	 */
	protected $has_loaded_call;

	/**
	 * @var array
	 */
	protected $available_extension = array();

	/**
	 * @var array
	 */
	protected $invalid_extension = array();

	/**
	 * @var string
	 */
	protected $option_name;

	/**
	 * @var bool
	 */
	protected $injectable;

	/**
	 * ExtensionLoader constructor.
	 *
	 * @param string $extensionDirectory
	 * @param string $name
	 * @param bool   $injectable
	 */
	public function __construct($extensionDirectory, $name = null, $injectable = false)
	{
		// prevent call __construct
		if (isset($this->extension_directory_valid)) {
			return;
		}
		$this->extension_directory_valid = false;
		$this->file_helper               = File::getInstance();
		if ($this->file_helper->isDir($extensionDirectory) && realpath($extensionDirectory)) {
			$this->setName($name);
			$this->injectable                = (bool) $injectable;
			$this->extension_directory       = realpath($extensionDirectory);
			$this->extension_directory_valid = true;
		}
	}

	/**
	 * @return bool
	 */
	public function isInjectable()
	{
		return $this->injectable;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		if (is_string($name) && !isset($this->name_extension)) {
			$this->option_name =  'itf_extension_option_'.md5($name);
			$this->name_extension = $name;
		}
	}

	/**
	 * @return string
	 */
	public function getOptionName()
	{
		return $this->option_name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name_extension;
	}

	public function getExtensionDirectory()
	{
		return $this->extension_directory;
	}

	/**
	 * @return bool
	 */
	public function hasCalledInit()
	{
		return $this->has_loaded_call;
	}

	/**
	 * Load init
	 */
	public function initLoad()
	{
		if (!$this->hasCalledInit()) {
			$this->has_loaded_call = true;
			if ($this->extension_directory_valid) {
				$ext_dir = $this->getExtensionDirectory();
				$directory_array = $this->file_helper->dirlist(
					$this->getExtensionDirectory(),
					false,
					true
				);

				$extension_namespace_no_quote = __NAMESPACE__ .'\\Abstracts\\FlareExtension';
				$extension_namespace = preg_quote($extension_namespace_no_quote);
				foreach ( $directory_array as $key => $item ) {
					if ($item['type'] !== 'd' || empty($item['files'])) {
						continue;
					}
					if (!preg_match('/[a-z][a-z0-9]/i', $key)) {
						continue;
					}
					if (empty($item['files']["{$key}.php"]) || $item['files']["{$key}.php"]['type'] != 'f') {
						continue;
					}

					$file = realpath("{$ext_dir}/{$key}/{$key}.php");
					if (!$file) {
						continue;
					}

					// load files
					LoaderOnce::load($file);
					// get Contents
					$string = substr($this->file_helper->getContents($file), 0, 2048);

					// validate
					if (trim($string) == '' || stripos($string, '<?php') !== 0
					    || stripos($string, $key) === false
						|| stripos($string, 'FlareExtension') == false
					) {
						continue;
					}
					$class = preg_quote($key, '/');

					if (
						preg_match('/class\s+'.$class.'\s+extends\s*\\?'.$extension_namespace.'\s*\{/im', $string)
						|| preg_match('/use\s+\\\?'. $extension_namespace .'(?:\s+as\s+([a-z][a-z0-9]+))?\s*\;/i', $string, $match)
						   && ! empty($match[0])
						   && (
							   ! empty($match[1])
							   && preg_match('/class\s+'.$class.'\s+extends\s+'.preg_quote($match[1], '/').'\s*\{/im', $string)
							   || preg_match('/class\s+'.$class.'\s+extends\s*FlareExtension\s*\{/im', $string)
						   )
					) {
						preg_match('/namespace\s+(.+)\;/i', $string, $match);
						$class = ! empty($match[1]) && trim($match[1]) != ''
							? ltrim($match[1], '\\') . '\\' . $key
							: $key;
						if (class_exists($class)) {
							/**
							 * @var ReflectorHelper
							 */
							$reflector = new ReflectorHelper( $class );
							if ($reflector->isSubclassOf($extension_namespace_no_quote)) {
								$this->available_extension[$key] = $reflector->getName();
								continue;
							}

							// set invalid
							$this->invalid_extension[$key] = $class;
							continue;
						}
						// set invalid
						$this->invalid_extension[$key] = $class;
				   }
				}

				unset($directory_array, $string);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getAvailableExtension()
	{
		return $this->available_extension;
	}

	/**
	 * @return array
	 */
	public function getInvalidExtension()
	{
		return $this->invalid_extension;
	}

	/**
	 * @return string
	 */
	public function getNonce()
	{
		static $nonce;

		if (!isset($nonce)) {
			$nonce = wp_create_nonce(md5($this->getName()));
		}

		return $nonce;
	}

	/**
	 * @param string $query
	 *
	 * @return bool|false|int
	 */
	public function verifyNonce($query = '_wpnonce')
	{
		if (!is_string($query)) {
			return false;
		}

		$nonce = isset($_REQUEST[$query]) ? $_REQUEST[$query] : false;
		return $nonce ? wp_verify_nonce($nonce, md5($this->getName())) : false;
	}

	/**
	 * @return array
	 */
	public function getDataOption()
	{
		$name = $this->getOptionName();
		$options = get_option($name, array());
		if (!is_array($options)) {
			$options = array();
			update_option($name, $options);
		}

		return $options;
	}

}
