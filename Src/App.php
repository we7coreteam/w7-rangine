<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use W7\Core\Base\ServerAbstract;
use W7\Core\Config\Config;
use W7\Http\Server\Server;

class App {

	const IA_ROOT = __DIR__;

    /**
     * 服务器对象
     *
     * @var Server
     */
	static $server;
	/**
	 * @var \W7\Core\Helper\Loader;
	 */
	static private $loader;
	static private $config;

	static public function getLoader() {
		if(empty(self::$loader)) {
			self::$loader = new \W7\Core\Helper\Loader();
		}
		return self::$loader;
	}


}

