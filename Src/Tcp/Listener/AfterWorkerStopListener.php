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

namespace W7\Tcp\Listener;

use W7\App;
use W7\Core\Listener\ListenerAbstract;
use W7\Tcp\Collector\CollectorManager;
use W7\Tcp\Collector\SwooleRequestCollector;

class AfterWorkerStopListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var CollectorManager $collectManager
		 */
		$collectManager = iloader()->get(CollectorManager::class);
		$requestCollect = $collectManager->getCollector(SwooleRequestCollector::getName());
		foreach ($requestCollect->all() as $fd => $request) {
			App::$server->getServer()->disconnect($fd, 0, '');
		}
	}
}
