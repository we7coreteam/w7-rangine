<?php
/**
 * @author donknap
 * @date 18-7-21 下午3:35
 */

namespace W7\Core\Config;

use Dotenv\Dotenv;
use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStart;
use W7\Core\Listener\StartListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Process\MysqlPoolprocess;
use W7\Core\Process\ReloadProcess;
use W7\Http\Listener\RequestListener;

class Config
{
	const VERSION = '1.0.0';

	private $server;
	private $defaultServer = [
		'websocket' => [
			'host' => '0.0.0.0'
		]
	];




	private $event;
	private $defaultEvent = [
		'task' => [
			Event::ON_TASK => TaskListener::class,
			Event::ON_FINISH => FinishListener::class,
		],
		'http' => [
			Event::ON_REQUEST => RequestListener::class,
		],
		'manage' => [
			Event::ON_START => StartListener::class,
			Event::ON_MANAGER_START => ManagerStart::class,
		],
		'system' =>[
			Event::ON_USER_BEFORE_START,
			Event::ON_USER_BEFORE_REQUEST,
			Event::ON_USER_AFTER_REQUEST,
			Event::ON_USER_TASK_FINISH,

		],
	];

	private $process = [
		ReloadProcess::class,
		MysqlPoolprocess::class,
	];

	private $allow_type=[
		'server',
		'event',
		'app',
		'route',
		'define',
	];


	/**
	 * @return array
	 */
	public function getEvent()
	{
		if (!empty($this->event)) {
			return $this->event;
		}
		$this->event = array_merge([], $this->defaultEvent, $this->getUserCommonConfig('event'));

		return $this->event;
	}

	/**
	 * EnvHelper constructor.
	 */
	public function __construct()
	{
		if (file_exists(BASE_PATH . DIRECTORY_SEPARATOR . ".env")) {
			$dotenv = new Dotenv(BASE_PATH);
			$dotenv->load();
		}
	}
	/**
	 * @param array $config
	 * @return array
	 */
	public function overWrite(array $config)
	{
		foreach ($config as $key=>$value) {
			if (is_array($value)) {
				$config[$key] = $this->overWrite($value);
				continue;
			}
			$config[$key] = env(strtoupper($key), $value);
		}
		return $config;
	}

	/**
	 * @return array
	 */
	public function getServer()
	{
		if (!empty($this->server)) {
			return $this->server;
		}
		$this->server = array_merge([], $this->defaultServer, $this->getUserConfig('server'));
		return $this->server;
	}

	/**
	 * @return array
	 */
	public function getProcess()
	{
		return $this->process;
	}

	public function getUserConfig($type)
	{
		if (!in_array($type, $this->allow_type)) {
			return null;
		}
		$appConfigFile = IA_ROOT . '/config/'.$type.'.php';
		if (file_exists($appConfigFile)) {
			$appConfig = include $appConfigFile;
		}
		$appConfig = $this->overWrite($appConfig);
		return $appConfig;
	}

	public function getUserCommonConfig($name)
	{
		$commonConfig = $this->getUserConfig('app');
		if (isset($commonConfig[$name])) {
			return $commonConfig[$name];
		} else {
			return [];
		}
	}
}
