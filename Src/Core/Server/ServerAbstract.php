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
use W7\Core\Exception\CommandException;

abstract class ServerAbstract implements ServerInterface {
	public $server;

	/**
	 * ServerAbstract constructor.
	 * @throws CommandException
	 */
	public function __construct() {
		!App::$server && App::$server = $this;
		$this->server = $this;
	}

	public function getServer() {
		return $this->server;
	}

	public function registerService() {
		$this->registerServerEventListener();
	}

	abstract protected function registerServerEventListener();
}
