<?php

namespace W7\Core\Provider;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;

class IlluminateProvider extends ProviderAbstract {
	public function register() {
		$this->container->set(Container::class, function () {
			$container = new Container();
			$container->instance('config', new Fluent());
			return $container;
		});
	}
}