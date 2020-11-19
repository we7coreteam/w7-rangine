<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Server;

use W7\App;
use W7\Contract\Server\ServerInterface;
use W7\Core\Helper\Traiter\AppCommonTrait;

abstract class ServerAbstract implements ServerInterface {
	use AppCommonTrait;

	//表示当前服务是主服务
	public static $masterServer = true;
	//表示该服务只能跟随主服务启动
	public static $onlyFollowMasterServer = false;
	//表示该服务可以单独启动
	public static $aloneServer = false;

	public $server;

	/**
	 * ServerAbstract constructor.
	 */
	public function __construct() {
		!App::$server && App::$server = $this;
	}

	public function getServer() {
		return $this->server;
	}

	/**
	 * //执行一些公共操作，注册事件,provider等
	 */
	public function registerService() {
		$this->registerServerEvent($this->getServer());
	}

	abstract protected function registerServerEvent($byListener);
}
