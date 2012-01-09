<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette
 */



/**
 * Nette environment and configuration.
 *
 * @author     David Grudl
 * @deprecated
 */
final class NEnvironment
{
	/** environment name */
	const DEVELOPMENT = 'development',
		PRODUCTION = 'production',
		CONSOLE = 'console';

	/** @var NConfigurator */
	private static $configurator;

	/** @var IDiContainer */
	private static $context;



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new NStaticClassException;
	}



	/**
	 * Sets "class behind Environment" configurator.
	 * @param  NConfigurator
	 * @return void
	 */
	public static function setConfigurator(NConfigurator $configurator)
	{
		self::$configurator = $configurator;
	}



	/**
	 * Gets "class behind Environment" configurator.
	 * @return NConfigurator
	 */
	public static function getConfigurator()
	{
		if (self::$configurator === NULL) {
			self::$configurator = ($tmp= NConfigurator::$instance) ? $tmp : new NConfigurator;
		}
		return self::$configurator;
	}



	/********************* environment modes ****************d*g**/



	/**
	 * Detects console (non-HTTP) mode.
	 * @return bool
	 */
	public static function isConsole()
	{
		return self::getContext()->params['consoleMode'];
	}



	/**
	 * Determines whether a server is running in production mode.
	 * @return bool
	 */
	public static function isProduction()
	{
		return self::getContext()->params['productionMode'];
	}



	/**
	 * Enables or disables production mode.
	 * @param  bool
	 * @return void
	 */
	public static function setProductionMode($value = TRUE)
	{
		self::getContext()->params['productionMode'] = (bool) $value;
	}



	/********************* environment variables ****************d*g**/



	/**
	 * Sets the environment variable.
	 * @param  string
	 * @param  mixed
	 * @param  bool
	 * @return void
	 */
	public static function setVariable($name, $value, $expand = TRUE)
	{
		if ($expand && is_string($value)) {
			$value = self::getContext()->expand($value);
		}
		self::getContext()->params[$name] = $value;
	}



	/**
	 * Returns the value of an environment variable or $default if there is no element set.
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 * @throws InvalidStateException
	 */
	public static function getVariable($name, $default = NULL)
	{
		if (isset(self::getContext()->params[$name])) {
			return self::getContext()->params[$name];
		} elseif (func_num_args() > 1) {
			return $default;
		} else {
			throw new InvalidStateException("Unknown environment variable '$name'.");
		}
	}



	/**
	 * Returns the all environment variables.
	 * @return array
	 */
	public static function getVariables()
	{
		return self::getContext()->params;
	}



	/**
	 * Returns expanded variable.
	 * @param  string
	 * @return string
	 * @throws InvalidStateException
	 */
	public static function expand($s)
	{
		return self::getContext()->expand($s);
	}



	/********************* context ****************d*g**/



	/**
	 * Sets initial instance of context.
	 * @return void
	 */
	public static function setContext(IDiContainer $context)
	{
		self::$context = $context;
	}



	/**
	 * Get initial instance of context.
	 * @return IDiContainer
	 */
	public static function getContext()
	{
		if (self::$context === NULL) {
			self::$context = self::getConfigurator()->getContainer();
		}
		return self::$context;
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @return object
	 */
	public static function getService($name)
	{
		return self::getContext()->getService($name);
	}



	/**
	 * Calling to undefined static method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return object  service
	 */
	public static function __callStatic($name, $args)
	{
		if (!$args && strncasecmp($name, 'get', 3) === 0) {
			return self::getContext()->getService(lcfirst(substr($name, 3)));
		} else {
			throw new MemberAccessException("Call to undefined static method NEnvironment::$name().");
		}
	}



	/**
	 * @return NHttpRequest
	 */
	public static function getHttpRequest()
	{
		return self::getContext()->httpRequest;
	}



	/**
	 * @return NHttpContext
	 */
	public static function getHttpContext()
	{
		return self::getContext()->httpContext;
	}



	/**
	 * @return NHttpResponse
	 */
	public static function getHttpResponse()
	{
		return self::getContext()->httpResponse;
	}



	/**
	 * @return NApplication
	 */
	public static function getApplication()
	{
		return self::getContext()->application;
	}



	/**
	 * @return NUser
	 */
	public static function getUser()
	{
		return self::getContext()->user;
	}



	/**
	 * @return NRobotLoader
	 */
	public static function getRobotLoader()
	{
		return self::getContext()->robotLoader;
	}



	/********************* service factories ****************d*g**/



	/**
	 * @param  string
	 * @return NCache
	 */
	public static function getCache($namespace = '')
	{
		return new NCache(self::getContext()->cacheStorage, $namespace);
	}



	/**
	 * Returns instance of session or session namespace.
	 * @param  string
	 * @return NSession
	 */
	public static function getSession($namespace = NULL)
	{
		return $namespace === NULL
			? self::getContext()->session
			: self::getContext()->session->getSection($namespace);
	}



	/********************* global configuration ****************d*g**/



	/**
	 * Loads global configuration from file and process it.
	 * @param  string
	 * @param  string
	 * @return NArrayHash
	 */
	public static function loadConfig($file = NULL, $section = NULL)
	{
		self::getConfigurator()->loadConfig($file, $section);
		return self::getConfig();
	}



	/**
	 * Returns the global configuration.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public static function getConfig($key = NULL, $default = NULL)
	{
		$params = NArrayHash::from(self::getContext()->params);
		if (func_num_args()) {
			return isset($params[$key]) ? $params[$key] : $default;
		} else {
			return $params;
		}
	}

}
