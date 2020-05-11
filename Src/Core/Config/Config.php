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

class Config {
	const VERSION = '2.2.3';

	private $server;
	private $defaultServer = [];

	private $config = [];

	public function __construct() {
		//初始化evn配置数据
		/**
		 * @var Env $env
		 */
		$env = new Env(BASE_PATH);
		$env->load();
		unset($env);

		//加载所有的配置到内存中
		$this->loadAutoLoadConfig();
		$this->loadUserConfig('config');
		$this->initUserConst();
	}

	private function initUserConst() {
		$setting = $this->getUserAppConfig('setting');
		!defined('ENV') && define('ENV', $setting['env'] ?? DEVELOPMENT);

		$this->checkSetting();
	}

	private function checkSetting() {
		if (!is_numeric(ENV) || ((RELEASE|DEVELOPMENT) & ENV) !== ENV) {
			throw new \RuntimeException("config setting['env'] error, please use the constant RELEASE, DEVELOPMENT, DEBUG, CLEAR_LOG, BACKTRACE instead");
		}
	}

	/**
	 * @return array
	 */
	public function getServer() {
		if (!empty($this->server)) {
			return $this->server;
		}
		$this->server = array_merge([], $this->defaultServer, $this->getUserConfig('server'));
		return $this->server;
	}

	/**
	 * 获取config目录下配置文件
	 * @param $type
	 * @return mixed|null
	 */
	public function getUserConfig($type) {
		if (!empty($this->config['config'][$type])) {
			return $this->config['config'][$type];
		}
		return [];
	}

	/**
	 * 获取config/app.php中用户的配置
	 * @param $name
	 * @return array
	 */
	public function getUserAppConfig($name) {
		$commonConfig = $this->getUserConfig('app');
		if (isset($commonConfig[$name])) {
			return $commonConfig[$name];
		} else {
			return [];
		}
	}

	public function setUserConfig($name, $data) {
		if ($name === 'server') {
			$this->server = [];
		}
		$this->config['config'][$name] = $data;
	}

	public function getRouteConfig() {
		$this->loadUserConfig('route');
		return $this->config['route'];
	}

	public function get($key, $default = null) {
		return Arr::get($this->config['config'], $key, $default);
	}

	public function set($key, $value) {
		return Arr::set($this->config['config'], $key, $value);
	}

	/**
	 * 加载所有的配置文件到内存中
	 */
	private function loadUserConfig($section) {
		$allowSection = [
			'route',
			'config',
		];

		if (!in_array($section, $allowSection)) {
			throw new \RuntimeException('Path not allowed');
		}

		$this->loadConfig(BASE_PATH, $section);
	}

	private function loadAutoLoadConfig() {
		$this->loadConfig(BASE_PATH . '/vendor/composer/rangine/autoload', 'config');
	}

	private function loadConfig($configDir, $section) {
		$this->config[$section] = $this->config[$section] ?? [];
		$configFileTree = glob($configDir . '/' . $section . '/*.php');
		if (empty($configFileTree)) {
			return $this->config[$section];
		}

		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);
			$appConfig = include $path;
			if (is_array($appConfig)) {
				$this->config[$section][$key] = $this->config[$section][$key] ?? [];
				$this->config[$section][$key] = array_merge_recursive($this->config[$section][$key], $appConfig);
			}
		}

		return $this->config[$section];
	}
}
