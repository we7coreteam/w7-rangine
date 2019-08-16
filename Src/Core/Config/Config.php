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

use W7\Core\Process\CrontabProcess;
use W7\Core\Process\ReloadProcess;

class Config {
	const VERSION = '1.0.0';

	private $server;
	private $defaultServer = [];

	private $process = [
		ReloadProcess::class,
		CrontabProcess::class,
	];

	private $config = [];

	public function __construct() {
		//初始化evn配置数据
		/**
		 * @var Env $env
		 */
		$env = new Env(BASE_PATH);
		$env->load();
		unset($env);

		$this->initUserConst();
	}

	private function initUserConst() {
		//加载所有的配置到内存中
		$this->loadConfig('config');

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
	 * @return array
	 */
	public function getProcess() {
		return $this->process;
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
		$this->config['config'][$name] = $data;
	}

	public function getRouteConfig() {
		$this->loadConfig('route');
		return $this->config['route'];
	}

	/**
	 * 加载所有的配置文件到内存中
	 */
	private function loadConfig($section) {
		$allowSection = [
			'route',
			'config',
		];

		if (!in_array($section, $allowSection)) {
			throw new \RuntimeException('Path not allowed');
		}

		if (!empty($this->config) && !empty($this->config[$section])) {
			return $this->config[$section];
		}
		$this->config[$section] = [];

		$configFileTree = glob(BASE_PATH . '/' . $section . '/*.php');
		if (empty($configFileTree)) {
			return [];
		}
		foreach ($configFileTree as $path) {
			$key = pathinfo($path, PATHINFO_FILENAME);
			$appConfig = include $path;
			if (is_array($appConfig)) {
				$this->config[$section][$key] = $appConfig;
			}
		}
		return $this->config;
	}
}
