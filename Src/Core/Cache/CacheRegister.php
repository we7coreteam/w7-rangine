<?php


namespace W7\Core\Cache;


use W7\Core\Container\RegisterAbstract;

class CacheRegister extends RegisterAbstract {
	public function register() {
		// TODO: Implement register() method.
		iloader()->set('cache', function () {
			return new Cache();
		});
	}
}