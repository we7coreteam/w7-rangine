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

namespace W7\Core\Server;

use Illuminate\Support\Str;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStartListener;
use W7\Core\Listener\ManagerStopListener;
use W7\Core\Listener\PipeMessageListener;
use W7\Core\Listener\ShutDownListener;
use W7\Core\Listener\StartListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Listener\WorkerErrorListener;
use W7\Core\Listener\WorkerExitListener;
use W7\Core\Listener\WorkerStartListener;
use W7\Core\Listener\WorkerStopListener;
use W7\Http\Listener\RequestListener;
use W7\Tcp\Listener\CloseListener;
use W7\Tcp\Listener\ConnectListener;
use W7\Tcp\Listener\ReceiveListener;
use W7\Core\Listener\ProcessStartListener;
use W7\Core\Listener\ProcessStopListener;
use W7\WebSocket\Listener\CloseListener as WebSocketCloseListener;
use W7\WebSocket\Listener\HandShakeListener;
use W7\WebSocket\Listener\MessageListener;
use W7\WebSocket\Listener\OpenListener;

class ServerEvent {
	use AppCommonTrait;

	/**
	 * swoole 事件
	 */
	const ON_START = 'start';
	const ON_SHUTDOWN = 'shutdown';

	const ON_WORKER_START = 'workerStart';
	const ON_WORKER_STOP = 'workerStop';
	const ON_WORKER_EXIT = 'workerExit';
	const ON_WORKER_SHUTDOWN = 'workerShutDown';

	const ON_MANAGER_START = 'managerStart';
	const ON_MANAGER_STOP = 'managerStop';
	const ON_WORKER_ERROR = 'workerError';

	const ON_CONNECT = 'connect';
	const ON_RECEIVE = 'receive';
	const ON_PACKET = 'packet';
	const ON_CLOSE = 'close';

	const ON_TASK = 'task';
	const ON_FINISH = 'finish';
	const ON_PIPE_MESSAGE = 'pipeMessage';

	const ON_REQUEST = 'request';

	const ON_HAND_SHAKE = 'handshake';
	const ON_OPEN = 'open';
	const ON_MESSAGE = 'message';

	/**
	 * 自定义事件
	 */
	const ON_USER_BEFORE_START = 'beforeStart';
	const ON_USER_AFTER_START = 'afterStart';
	const ON_USER_AFTER_SHUTDOWN = 'afterShutDown';
	const ON_USER_AFTER_MANAGER_START = 'afterManagerStart';
	const ON_USER_AFTER_MANAGER_STOP = 'afterManagerStop';
	const ON_USER_AFTER_WORKER_START = 'afterWorkerStart';
	const ON_USER_AFTER_WORKER_STOP = 'afterWorkerStop';
	const ON_USER_AFTER_WORKER_EXIT = 'afterWorkerExit';
	const ON_USER_AFTER_WORKER_SHUTDOWN = 'afterWorkerShutDown';
	const ON_USER_AFTER_WORKER_ERROR = 'afterWorkerError';
	const ON_USER_AFTER_PIPE_MESSAGE = 'afterPipeMessage';
	const ON_USER_BEFORE_REQUEST = 'beforeRequest';
	const ON_USER_AFTER_REQUEST = 'afterRequest';
	const ON_USER_AFTER_TASK = 'afterTask';
	const ON_USER_TASK_FINISH = 'afterTaskFinish';
	const ON_USER_BEFORE_HAND_SHAKE = 'beforeHandShake';
	const ON_USER_AFTER_OPEN = 'afterOpen';
	const ON_USER_AFTER_CLOSE = 'afterClose';

	private static $event = [
		'manage' => [
			self::ON_START => StartListener::class,
			self::ON_MANAGER_START => ManagerStartListener::class,
			self::ON_MANAGER_STOP => ManagerStopListener::class,
			self::ON_WORKER_ERROR => WorkerErrorListener::class,
			self::ON_SHUTDOWN => ShutDownListener::class
		],
		'worker' => [
			self::ON_WORKER_START => WorkerStartListener::class,
			self::ON_WORKER_STOP => WorkerStopListener::class,
			self::ON_PIPE_MESSAGE => PipeMessageListener::class,
			self::ON_WORKER_EXIT => WorkerExitListener::class
		],
		'task' => [
			self::ON_TASK => TaskListener::class,
			self::ON_FINISH => FinishListener::class
		],
		ServerEnum::TYPE_HTTP => [
			self::ON_REQUEST => RequestListener::class
		],
		ServerEnum::TYPE_TCP => [
			self::ON_RECEIVE => ReceiveListener::class,
			self::ON_CONNECT => ConnectListener::class,
			self::ON_CLOSE => CloseListener::class
		],
		ServerEnum::TYPE_WEBSOCKET => [
			self::ON_HAND_SHAKE => HandShakeListener::class,
			self::ON_OPEN => OpenListener::class,
			self::ON_CLOSE => WebSocketCloseListener::class,
			self::ON_MESSAGE => MessageListener::class
		],
		ServerEnum::TYPE_PROCESS => [
			self::ON_WORKER_START => ProcessStartListener::class,
			self::ON_WORKER_STOP => ProcessStopListener::class
		]
	];

	public function getDefaultEvent() {
		return self::$event;
	}

	public function getUserEvent() {
		return [
			self::ON_USER_BEFORE_START,
			self::ON_USER_AFTER_START,
			self::ON_USER_AFTER_SHUTDOWN,
			self::ON_USER_AFTER_MANAGER_START,
			self::ON_USER_AFTER_WORKER_ERROR,
			self::ON_USER_AFTER_MANAGER_STOP,
			self::ON_USER_AFTER_WORKER_START,
			self::ON_USER_AFTER_WORKER_STOP,
			self::ON_USER_AFTER_WORKER_EXIT,
			self::ON_WORKER_SHUTDOWN,
			self::ON_USER_AFTER_PIPE_MESSAGE,
			self::ON_USER_BEFORE_REQUEST,
			self::ON_USER_AFTER_REQUEST,
			self::ON_USER_TASK_FINISH,
			self::ON_USER_BEFORE_HAND_SHAKE,
			self::ON_USER_AFTER_OPEN,
			self::ON_USER_AFTER_CLOSE
		];
	}

	/**
	 * 注册服务必须的事件
	 * @param $eventTypes
	 */
	public function registerServerEvent($eventTypes) {
		$swooleEvents = $this->getDefaultEvent();
		foreach ((array)$eventTypes as $eventType) {
			$events = $swooleEvents[$eventType] ?? [];
			foreach ($events as $name => $event) {
				$this->getEventDispatcher()->listen($eventType . ':' . $name, $event);
			}
		}
	}

	/**
	 * 注册服务用户层事件
	 * @param $servers
	 */
	public function registerServerUserEvent() {
		//注册用户层和系统的公共事件
		foreach ($this->getUserEvent() as $eventName) {
			$listener = sprintf('\\W7\\Core\\Listener\\%sListener', ucfirst($eventName));
			$this->getEventDispatcher()->listen($eventName, $listener);

			$listener = sprintf('\\W7\\App\\Listener\\%sListener', ucfirst($eventName));
			$this->getEventDispatcher()->listen($eventName, $listener);
		}
	}

	/**
	 * 注册服务的自定义事件
	 * @param $server
	 */
	public function registerServerCustomEvent($server) {
		//注册server下的自定义事件
		foreach ($this->getUserEvent() as $eventName) {
			$listener = sprintf('\\W7\\%s\\Listener\\%sListener', Str::studly($server), ucfirst($eventName));
			$this->getEventDispatcher()->listen($eventName, $listener);
		}
	}

	public function addServerEvents($server, array $events, $cover = false) {
		if ($cover) {
			static::$event[$server] = $events;
		} else {
			static::$event[$server] = static::$event[$server] ?? [];
			static::$event[$server] = array_merge(static::$event[$server], $events);
		}
	}
}
