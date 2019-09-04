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

use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStartListener;
use W7\Core\Listener\PipeMessageListener;
use W7\Core\Listener\ProcessMessageListener;
use W7\Core\Listener\ProcessStartListener;
use W7\Core\Listener\ProcessStopListener;
use W7\Core\Listener\StartListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Listener\WorkerErrorListener;
use W7\Core\Listener\WorkerStartListener;
use W7\Core\Listener\WorkerStopListener;
use W7\Http\Listener\RequestListener;
use W7\Tcp\Listener\CloseListener;
use W7\Tcp\Listener\ConnectListener;
use W7\Tcp\Listener\ReceiveListener;

class Config {
	const VERSION = '1.0.0';

	private $server;
	private $defaultServer = [];

	private $event;
	/**
	 * 系统内置的一些事件侦听，用户也可以在config/app.php中进行附加配置
	 */
	private $defaultEvent = [
		'task' => [
			Event::ON_TASK => TaskListener::class,
			Event::ON_FINISH => FinishListener::class,
		],
		'http' => [
			Event::ON_REQUEST => RequestListener::class,
		],
		'tcp' => [
			Event::ON_RECEIVE => ReceiveListener::class,
			Event::ON_CONNECT => ConnectListener::class,
			Event::ON_CLOSE => CloseListener::class,
		],
		'manage' => [
			Event::ON_START => StartListener::class,
			Event::ON_MANAGER_START => ManagerStartListener::class,
			Event::ON_WORKER_START => WorkerStartListener::class,
			Event::ON_WORKER_STOP => WorkerStopListener::class,
			Event::ON_WORKER_ERROR => WorkerErrorListener::class,
			Event::ON_PIPE_MESSAGE => PipeMessageListener::class,
		],
		'system' =>[
			Event::ON_USER_BEFORE_START,
			Event::ON_USER_BEFORE_REQUEST,
			Event::ON_USER_AFTER_REQUEST,
			Event::ON_USER_TASK_FINISH,
			Event::ON_USER_AFTER_REQUEST
		],
		'process' => [
			Event::ON_WORKER_START => ProcessStartListener::class,
			Event::ON_WORKER_STOP => ProcessStopListener::class,
			Event::ON_PROCESS_MESSAGE => ProcessMessageListener::class
		]
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
			throw new \Exception("config setting['env'] error, please use the constant RELEASE, DEVELOPMENT, DEBUG, CLEAR_LOG, BACKTRACE instead");
		}
	}

	/**
	 * @return array
	 */
	public function getEvent() {
		if (!empty($this->event)) {
			return $this->event;
		}
		$this->event = array_merge([], $this->defaultEvent, $this->getUserAppConfig('event'));

		return $this->event;
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
			$this->server = null;
		}
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
