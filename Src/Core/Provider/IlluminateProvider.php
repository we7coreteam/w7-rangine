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

namespace W7\Core\Provider;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;

class IlluminateProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(Container::class, function () {
			$container = new Container();
			$container->instance('config', new Fluent());

			$container->singleton(\Illuminate\Contracts\Container\Container::class, function () use ($container) {
				return $container;
			});

			return $container;
		});
	}
}
