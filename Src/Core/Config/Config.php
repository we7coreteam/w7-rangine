<?php
/**
 * @author donknap
 * @date 18-7-21 下午3:35
 */

namespace W7\Core\Config;

use W7\Core\Listener\HttpServerListener;
use W7\Core\Listener\TaskListener;

class Config {
	private $server;
	private $defaultServer = [
		'websocket' => [
			'host' => '0.0.0.0'
		]
	];

	private $event;
	private $defaultEvent = [
		'task' => [
			'task' => TaskListener::class,
			'finish' => TaskListener::class,
		],
		'http' => [
			'request' => HttpServerListener::class,
		],
        'system' => [
            'beforeServerStart' => '',
        ]
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

	public function getUserConfig($type) {
		$appConfigFile = IA_ROOT . '/config/'.$type.'.php';
		if (file_exists($appConfigFile)) {
			$appConfig = include_once $appConfigFile;
		}
		return $appConfig;
	}
}