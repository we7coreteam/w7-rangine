<?php


namespace W7\Core\Config;


use W7\Core\Container\RegisterAbstract;

class ConfigRegister extends RegisterAbstract {
	public function register() {
		iloader()->set('config', function () {
			return new Config();
		});
	}
}