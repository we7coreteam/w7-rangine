<?php
/**
 * @author donknap
 * @date 18-7-21 下午3:35
 */

namespace W7\Core\Config;

use Illuminate\Support\Str;
use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStartListener;
use W7\Core\Listener\PipeMessageListener;
use W7\Core\Listener\StartListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Listener\WorkerErrorListener;
use W7\Core\Listener\WorkerStartListener;
use W7\Core\Process\CrontabProcess;
use W7\Core\Process\MysqlPoolprocess;
use W7\Core\Process\ReloadProcess;
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
	];

	private $process = [
		ReloadProcess::class,
		CrontabProcess::class,
	];

	private $allow_user_config = [
		'server',
		'event',
		'app',
		'log',
		'crontab',
	];

	private $config = [];
	private $routeConfig = [];

	private $path = BASE_PATH . '/config/';

	public function __construct() {
		//初始化evn配置数据
		/**
		 * @var Env $env
		 */
		$env = new Env(BASE_PATH);
		$env->load();
		unset($env);
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
		if (!in_array($type, $this->allow_user_config)) {
			return null;
		}

		if (!empty($this->config[$type])) {
			return $this->config[$type];
		}

		$appConfigFile = BASE_PATH . '/config/'.$type.'.php';
		$appConfig = [];
		if (file_exists($appConfigFile)) {
			$appConfig = $this->config[$type] = include $appConfigFile;
		}
		return $appConfig;
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

	public function getRouteConfig() {
		if (!empty($this->routeConfig)) {
			return $this->routeConfig;
		}

		$configFileTree = glob(BASE_PATH . '/route/*.php');

		if (empty($configFileTree)) {
			return [];
		}

		foreach ($configFileTree as $path) {
			$appConfig = include $path;
			if (is_array($appConfig)) {
				$this->routeConfig[] = $appConfig;
			}
		}
		return $this->routeConfig;
	}
}
