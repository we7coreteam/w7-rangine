<?php
/**
 * @author donknap
 * @date 18-7-21 下午3:35
 */

namespace W7\Core\Config;

use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStartListener;
use W7\Core\Listener\PipeMessageListener;
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

		$this->initConst();
	}

	private function initConst() {
		//在加载配置前定义需要的常量
		!defined('RELEASE') && define('RELEASE', 0);
		!defined('DEBUG') && define('DEBUG', 1);
		!defined('CLEAR_LOG') && define('CLEAR_LOG', 2);
		!defined('DEVELOPMENT') && define('DEVELOPMENT', DEBUG | CLEAR_LOG);
		!defined('RANGINE_FRAMEWORK_PATH') && define('RANGINE_FRAMEWORK_PATH', dirname(__FILE__, 3));

		//在加载配置前定义需要的常量
		!defined('HTTP') && define('HTTP', 1);
		!defined('TCP') && define('TCP', 2);
		!defined('PROCESS') && define('PROCESS', 4);
		!defined('CRONTAB') && define('CRONTAB', 8);

		//加载所有的配置到内存中
		$this->loadConfig('config');
		$this->checkSetting();
	}

	private function checkSetting() {
		$setting = $this->getUserAppConfig('setting');

		if (defined('ENV')) {
			$env = ENV;
		} else {
			$env = $setting['env'] ?? '';
		}
		if (!is_numeric($env) || ((RELEASE|DEVELOPMENT) & $env) !== $env) {
			throw new \Exception("config setting['env'] error, please use the constant RELEASE, DEVELOPMENT, DEBUG, CLEAR_LOG, BACKTRACE instead");
		}
		!defined('ENV') && define('ENV', $env);


		if (defined('SERVER')) {
			$server = SERVER;
		} else {
			$server = $setting['server'] ?? HTTP|PROCESS|CRONTAB;
		}
		if (!is_numeric($server) || ((HTTP|TCP|PROCESS|CRONTAB) & $server) !== $server) {
			throw new \Exception("config setting['server'] error, please use the constant HTTP, TCP, PROCESS, CRONTAB instead");
		}
		!defined('SERVER') && define('SERVER', $server);
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
