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

namespace  W7\WebSocket\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\WebSocket\Collector\CollectorManager;
use W7\WebSocket\Collector\SwooleRequestCollector;
use W7\WebSocket\Packer\JsonPacker;
use W7\WebSocket\Packer\PackerInterface;

class ServiceProvider extends ProviderAbstract {
	public function register() {
		$this->registerCollector();
		$this->registerDataPacker();
	}

	private function registerCollector() {
		iloader()->get(CollectorManager::class)->addCollect(new SwooleRequestCollector());
	}

	private function registerDataPacker() {
		iloader()->set(PackerInterface::class, function () {
			return new JsonPacker();
		});
	}
}
