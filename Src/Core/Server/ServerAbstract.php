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
use W7\Core\Process\Pool\PoolAbstract;

/**
 * Class ServerAbstract
 * @package W7\Core\Server
 *
 * @property PoolAbstract $processPool
 */
#[\AllowDynamicProperties]
abstract class ServerAbstract implements ServerInterface {
	use AppCommonTrait;

	//Indicates that the current service is the master service
	public static $masterServer = true;
	//Indicates that the service can only be started with the master service
	public static $onlyFollowMasterServer = false;
	//Indicates that the service can be started separately
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

	public function registerService() {
		$this->registerServerEvent($this->getServer());
	}

	abstract protected function registerServerEvent($byListener);
}
