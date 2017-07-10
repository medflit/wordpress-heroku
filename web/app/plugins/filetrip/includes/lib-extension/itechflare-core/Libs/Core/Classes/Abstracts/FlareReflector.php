<?php
namespace iTechFlare\WP\Plugin\FileTrip\Core\Abstracts;

/**
 * Class FlareReflector
 * @package iTechFlare\WP\Plugin\FileTrip\Core\Abstracts
 * @abstract
 */
abstract class FlareReflector
{
	/**
	 * @var string
	 */
	private $abstractObjectName;

	/**
	 * @var string
	 */
	private $abstractShortName;

	/**
	 * @var string
	 */
	private $abstractNameSpace;

	/**
	 * @var \ReflectionClass
	 */
	private $abstractReflection;

	/**
	 * @var string
	 */
	private $abstractPath;

	/**
	 * ReflectorAbstract constructor.
	 *
	 * @param object|string $class
	 */
	final public function __construct($class)
	{
		if (is_object($class) || is_string($class) && class_exists($class)) {
			$this->abstractObjectName = is_object($class) ? get_class($class) : $class;
		}

		if ($this->isValid()) {
			$this->abstractReflection = new \ReflectionClass($class);
			// override with real value
			$this->abstractObjectName = $this->abstractReflection->getName();
			$this->abstractShortName  = $this->abstractReflection->getShortName();
			$this->abstractNameSpace  = ! $this->abstractReflection->inNamespace()
				?: $this->abstractReflection->getNamespaceName();
			$this->abstractPath       = $this->abstractReflection->getFileName();
		}
	}

	/**
	 * Check if Class Valid
	 *
	 * @return bool
	 */
	final public function isValid()
	{
		return is_string($this->abstractObjectName);
	}

	/**
	 * @return \ReflectionClass|bool
	 */
	final public function getAbstractReflection()
	{
		if ($this->isValid()) {
			return new \ReflectionClass($this->abstractObjectName);
		}

		return false;
	}

	/**
	 * @return string
	 */
	final public function getName()
	{
		return $this->abstractObjectName;
	}

	/**
	 * @return string
	 */
	final public function getAbstractShortName()
	{
		return $this->abstractShortName;
	}

	/**
	 * @return string
	 */
	final public function getShortName()
	{
		return $this->abstractShortName;
	}

	/**
	 * @return string
	 */
	final public function getAbstractObjectName()
	{
		return $this->abstractObjectName;
	}

	/**
	 * @return bool
	 */
	final public function hasNameSpace()
	{
		return $this->abstractNameSpace !== false;
	}

	/**
	 * @return bool
	 */
	final public function inNameSpace()
	{
		return $this->hasNameSpace();
	}

	/**
	 * @return bool|string
	 */
	final public function getAbstractNameSpace()
	{
		return $this->abstractNameSpace;
	}

	/**
	 * @return bool|string
	 */
	final public function getNameSpace()
	{
		return $this->abstractNameSpace;
	}

	/**
	 * @return null|string
	 */
	final public function getFileName()
	{
		return $this->abstractPath;
	}

	/**
	 * @return bool|string
	 */
	final public function getAbstractPath()
	{
		return $this->abstractPath;
	}

	/**
	 * @return bool|string
	 */
	final public function getPath()
	{
		return $this->abstractPath;
	}

	/**
	 * @return bool|string
	 */
	final public function getDirName()
	{
		return $this->abstractPath ? dirname($this->abstractPath) : false;
	}

	/**
	 * @return bool|string
	 */
	final public function getDirectory()
	{
		return $this->getDirName();
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	final public function hasMethod($name)
	{
		return is_string($name) && $this->abstractReflection && $this->abstractReflection->hasMethod($name);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	final public function hasProperty($name)
	{
		return is_string($name) && $this->abstractReflection && $this->abstractReflection->hasProperty($name);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	final public function hasConstant($name)
	{
		return is_string($name) && $this->abstractReflection && $this->abstractReflection->hasConstant($name);
	}

	/**
	 * @return bool
	 */
	final public function isAnonymous()
	{
		return $this->abstractReflection && $this->abstractReflection->isAnonymous();
	}

	/**
	 * @return bool
	 */
	final public function isClosure()
	{
		return $this->abstractReflection && $this->abstractObjectName == 'Closure';
	}

	/**
	 * @return bool
	 */
	final public function isAbstract()
	{
		return $this->abstractReflection && $this->abstractReflection->isAbstract();
	}

	/**
	 * @return bool
	 */
	final public function isInterface()
	{
		return $this->abstractReflection && $this->abstractReflection->isInterface();
	}

	/**
	 * @return bool
	 */
	final public function isTrait()
	{
		return $this->abstractReflection && $this->abstractReflection->isTrait();
	}

	/**
	 * @return bool
	 */
	final public function isFinal()
	{
		return $this->abstractReflection && $this->abstractReflection->isFinal();
	}

	/**
	 * @return bool
	 */
	final public function isInstantiable()
	{
		return $this->abstractReflection && $this->abstractReflection->isInstantiable();
	}

	/**
	 * @param string|object $class
	 *
	 * @return bool
	 */
	final public function isSubclassOf($class)
	{
		if ($this->abstractReflection && (is_string($class) || is_object($class))) {
			if (is_string($class) && class_exists($class)) {
				$class = is_object($class) ? get_class($class) : $class;
				return $this->abstractReflection->isSubclassOf($class);
			}
		}

		return false;
	}
}
