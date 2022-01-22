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

namespace W7\Core\Redis\Provider;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use W7\Contract\Redis\RedisFactoryInterface;
use W7\Core\Redis\ConnectionResolver;
use W7\Core\Redis\Pool\PoolFactory;
use W7\Core\Provider\ProviderAbstract;

class RedisProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(RedisFactoryInterface::class, function () {
			$connectionConfig = $this->config->get('app.redis', []);
			$poolConfig = $this->config->get('app.pool.redis', []);

			$connectionResolver = new ConnectionResolver(
				Container::getInstance(),
				Arr::pull($connectionConfig, 'client', 'phpredis'),
				$connectionConfig);
			$connectionResolver->setPoolFactory(new PoolFactory($poolConfig));

			return $connectionResolver;
		});
		$this->container->set('redis', function () {
			return $this->container->get(RedisFactoryInterface::class);
		});
		$this->container->set('redis.connection', function () {
			return $this->container->get(RedisFactoryInterface::class)->connection();
		});
	}

	public function providers(): array {
		return [RedisFactoryInterface::class, 'redis', 'redis.connection'];
	}
}
