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

namespace W7\Reload\Server;

use W7\App;
use W7\Core\Process\ProcessServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Reload\Process\ReloadProcess;

class Server extends ProcessServerAbstract {
	public static bool $onlyFollowMasterServer = true;

	public function __construct() {
		$this->getConfig()->set('server.' . $this->getType(), [
			'worker_num' => 1
		]);

		parent::__construct();
	}

	public function getType() {
		return ServerEnum::TYPE_RELOAD;
	}

	protected function register(): void {
		$this->pool->registerProcess('reload', ReloadProcess::class, 1);
	}

	public function start(): void {
		throw new \Exception('cannot start alone');
	}

	public function listener(\Swoole\Server $server = null): void {
		if ((ENV & DEBUG) !== DEBUG) {
			return ;
		}
		if (App::$server instanceof ProcessServerAbstract) {
			return ;
		}
		parent::listener($server);
	}
}
