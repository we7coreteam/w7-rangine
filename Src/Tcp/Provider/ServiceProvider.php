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

namespace  W7\Tcp\Provider;

use W7\Core\Provider\ProviderAbstract;
use W7\Tcp\Packer\JsonPacker;
use W7\Tcp\Packer\PackerInterface;
use W7\Tcp\Collector\CollectorManager;
use W7\Tcp\Collector\SwooleRequestCollector;

class ServiceProvider extends ProviderAbstract {
	public function register() {
		$this->registerCollector();
		$this->registerDataPacker();
	}

	private function registerCollector() {
		icontainer()->get(CollectorManager::class)->addCollect(new SwooleRequestCollector());
	}

	private function registerDataPacker() {
		icontainer()->set(PackerInterface::class, function () {
			return new JsonPacker();
		});
	}
}
