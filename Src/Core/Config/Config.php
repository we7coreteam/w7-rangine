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

namespace W7\Core\Config;

use Illuminate\Support\Arr;
use W7\App;
use W7\Core\Config\Env\Env;

class Config {
	private $server;
	private $payload = [];

	public function __construct(array $payload = []) {
		$this->payload = $payload;
	}

	public function getBuiltInConfigPath() {
		return BASE_PATH . '/vendor/composer/rangine/autoload/config';
	}

	public function load() {
		$loadDir = App::getApp()->getConfigCachePath();
		if (!file_exists($loadDir)) {
			$loadDir = BASE_PATH . '/config';
			(new Env(BASE_PATH))->load();
		}

		$this->loadConfig($this->getBuiltInConfigPath());
		$this->loadConfig($loadDir);
	}

	public function get($key, $default = null) {
		return Arr::get($this->payload, $key, $default);
	}

	public function set($key, $value) {
		return Arr::set($this->payload, $key, $value);
	}

	protected function loadConfig($configDir) {
		$configFileTree = glob($configDir . '/*.php');
		if (empty($configFileTree)) {
			return $this->payload;
		}

		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);
			$config = include $path;
			if (is_array($config)) {
				$this->payload[$key] = $this->payload[$key] ?? [];
				$this->payload[$key] = array_merge_recursive($this->payload[$key], $config);
			}
		}

		return $this->payload;
	}

	/**
	 * @return array
	 * @deprecated
	 */
	public function getServer() {
		if (!empty($this->server)) {
			return $this->server;
		}
		$this->server = $this->get('server');
		return $this->server;
	}

	/**
	 * @param $name
	 * @param $data
	 * @deprecated
	 */
	public function setUserConfig($name, $data) {
		if ($name === 'server') {
			$this->server = [];
		}
		$this->payload[$name] = $data;
	}

	/**
	 * 获取config目录下配置文件
	 * @param $type
	 * @return mixed|null
	 * @deprecated
	 */
	public function getUserConfig($type) {
		if (!empty($this->payload[$type])) {
			return $this->payload[$type];
		}
		return [];
	}

	/**
	 * 获取config/app.php中用户的配置
	 * @deprecated
	 * @param $name
	 * @return array
	 */
	public function getUserAppConfig($name) {
		return $this->get('app.' . $name, []);
	}
}
