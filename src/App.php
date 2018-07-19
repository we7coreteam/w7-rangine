<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

class App {

	/**
	 * @var \W7\Core\Helper\Loader;
	 */
	static private $loader;
	static private $setting;

	static public function getLoader() {
		if(empty(self::$loader)) {
			self::$loader = new \W7\Core\Helper\Loader();
		}
		return self::$loader;
	}

	static public function config() {
		if (!empty(self::$setting)) {
			return self::$setting;
		}
		self::$setting = [];
		self::$setting['server'] = include_once IA_ROOT . '/config/server.php';
		return self::$setting;
	}
}