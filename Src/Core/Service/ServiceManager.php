<?php


namespace W7\Core\Service;

use W7\Console\ConsoleRegister;
use W7\Core\Cache\CacheRegister;
use W7\Core\Container\Container;
use W7\Core\Validation\ValidateRegister;
use W7\Core\Database\DatabaseRegister;
use W7\Core\Exception\ExceptionRegister;
use W7\Core\Provider\ProviderRegister;

class ServiceManager {
	private $serviceMap = [
		ConsoleRegister::class,
		ExceptionRegister::class,
		CacheRegister::class,
		DatabaseRegister::class,
		ProviderRegister::class,
		ValidateRegister::class
	];
	private static $service;
	private $container;

	public function __construct() {
		$this->getContainer();
	}

	public function getContainer() {
		if (!$this->container) {
			$this->container = new Container();
		}
		return $this->container;
	}

	public function register() {
		foreach ($this->serviceMap as $service) {
			$this->registerService($service);
		}
	}

	public function registerService($service) {
		if (is_string($service)) {
			$service = $this->getService($service);
		}
		static::$service[get_class($service)] = $service;
		$service->register();
	}

	/**
	 * 扩展包全部注册完成后执行
	 */
	public function boot() {
		foreach (static::$service as $service => $obj) {
			$obj->boot();
		}
	}

	private function getService($service) : ServiceAbstract {
		return new $service();
	}
}