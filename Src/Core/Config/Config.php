<?php
/**
 * @author donknap
 * @date 18-7-21 下午3:35
 */

namespace W7\Core\Config;

use W7\Core\Listener\ManageServerListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Process\ReloadProcess;
use W7\Http\Listener\RequestListener;

class Config {
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
			Event::ON_FINISH => TaskListener::class,
		],
		'http' => [
			Event::ON_REQUEST => RequestListener::class,
		],
		'manage' => [
			Event::ON_START => ManageServerListener::class,
			Event::ON_MANAGER_START => ManageServerListener::class,
		],
	];

	private $process = [
		ReloadProcess::class,
	];


	/**
	 * @return array
	 */
	public function getEvent() {
		if (!empty($this->event)) {
			return $this->event;
		}
		$this->event = array_merge([], $this->defaultEvent, $this->getUserConfig('event'));
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
	/**
	 * @return array
	 */
	public function getProcess() {
		return $this->process;
	}

	public function getUserConfig($type) {
		$appConfigFile = IA_ROOT . '/config/'.$type.'.php';
		if (file_exists($appConfigFile)) {
			$appConfig = include $appConfigFile;
		}
		return $appConfig;
	}
}