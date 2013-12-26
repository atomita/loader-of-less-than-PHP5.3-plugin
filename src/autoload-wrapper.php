<?php

if (!function_exists('spl_autoload_register') && version_compare(PHP_VERSION, '5.0.0') >= 0) {

	// create spl_autoload_register

	class AutoLoadWrapper {

		private static $autoload_stack = array();

		static function register($callback = null, $throw = true, $prepend = false) {
			if ($callback === null) {
				$callback = function_exists('spl_autoload') ? 'spl_autoload' : '';
			}

			if (is_callable($callback)) {
				if (!$prepend) {
					self::$autoload_stack[] = $callback;
				}
				else {
					array_unshift(self::$autoload_stack, $callback);
				}
				return true;
			}
			if ($throw) {
				throw new BadFunctionCallException();
			}
			return false;
		}

		static function unregister($callback) {
			if (in_array($callback, self::$autoload_stack)) {
				$key = array_search($callback, self::$autoload_stack);
				unset(self::$autoload_stack[$key]);
				return true;
			}
			return false;
		}

		static function apply($className) {
			foreach (self::$autoload_stack as $callback) {
				if (class_exists($className, false)) {
					return;
				}
				call_user_func($callback, $className);
			}
		}

	}

	function spl_autoload_register($callback = null, $throw = true, $prepend = false) {
		return AutoLoadWrapper::register($callback, $throw, $prepend);
	}

	function spl_autoload_unregister($callback) {
		return AutoLoadWrapper::unregister($callback);
	}

	function __autoload($className) { // PHP 5
		AutoLoadWrapper::apply($className);
	}

}
