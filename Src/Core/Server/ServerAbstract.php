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
use W7\Core\Provider\ProviderManager;

abstract class ServerAbstract implements ServerInterface {
	protected $providerMap = [];
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
		$this->registerProvider();
		$this->registerServerEvent($this->getServer());
	}

	abstract protected function registerServerEvent($byListener);

	protected function registerProvider() {
		/**
		 * @var ProviderManager $providerManager
		 */
		$providerManager = icontainer()->singleton(ProviderManager::class);
		$providerManager->registerProviders($this->providerMap);
	}
}
