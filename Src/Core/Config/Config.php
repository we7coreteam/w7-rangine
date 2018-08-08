<?php
/**
 * @author donknap
 * @date 18-7-21 下午3:35
 */

namespace W7\Core\Config;

use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStart;
use W7\Core\Listener\StartListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Listener\WorkerStart;
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
			Event::ON_WORKER_START => WorkerStart::class,
		],
		'system' =>[
			Event::ON_USER_BEFORE_START,
			Event::ON_USER_BEFORE_REQUEST,
			Event::ON_USER_AFTER_REQUEST,
			Event::ON_USER_TASK_FINISH,
		],
	];

	private $process = [
		ReloadProcess::class
	];

	private $allow_user_config = [
		'server',
		'event',
		'app',
		'route',
	];

	public function __construct()
	{

	}

	/**
	 * @return array
	 */
	public function getEvent()
	{
		if (!empty($this->event)) {
			return $this->event;
		}
		$this->event = array_merge([], $this->defaultEvent, $this->getUserAppConfig('event'));

		return $this->event;
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

	/**
	 * 获取config目录下配置文件
	 * @param $type
	 * @return mixed|null
	 */
	public function getUserConfig($type)
	{
		if (!in_array($type, $this->allow_user_config)) {
			return null;
		}
		$appConfigFile = IA_ROOT . '/config/'.$type.'.php';
		if (file_exists($appConfigFile)) {
			$appConfig = include $appConfigFile;
		}
		return $appConfig;
	}

	/**
	 * 获取config/app.php中用户的配置
	 * @param $name
	 * @return array
	 */
	public function getUserAppConfig($name)
	{
		$commonConfig = $this->getUserConfig('app');
		if (isset($commonConfig[$name])) {
			return $commonConfig[$name];
		} else {
			return [];
		}
	}
}
