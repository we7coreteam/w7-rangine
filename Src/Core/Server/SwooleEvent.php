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

use W7\App;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Helper\StringHelper;
use W7\Core\Listener\FinishListener;
use W7\Core\Listener\ManagerStartListener;
use W7\Core\Listener\ManagerStopListener;
use W7\Core\Listener\PipeMessageListener;
use W7\Core\Listener\ShutDownListener;
use W7\Core\Listener\StartListener;
use W7\Core\Listener\TaskListener;
use W7\Core\Listener\WorkerErrorListener;
use W7\Core\Listener\WorkerStartListener;
use W7\Core\Listener\WorkerStopListener;
use W7\Http\Listener\RequestListener;
use W7\Tcp\Listener\CloseListener;
use W7\Tcp\Listener\ConnectListener;
use W7\Tcp\Listener\ReceiveListener;
use W7\Core\Listener\ProcessMessageListener;
use W7\Core\Listener\ProcessStartListener;
use W7\Core\Listener\ProcessStopListener;
use W7\WebSocket\Listener\CloseListener as WebSocketCloseListener;
use W7\WebSocket\Listener\HandshakeListener;
use W7\WebSocket\Listener\MessageListener;
use W7\WebSocket\Listener\OpenListener;

class SwooleEvent {
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
	const ON_USER_AFTER_WORKER_SHUTDOWN = 'afterWorkerShutDown';
	const ON_USER_AFTER_WORKER_ERROR = 'afterWorkerError';
	const ON_USER_BEFORE_REQUEST = 'beforeRequest';
	const ON_USER_AFTER_REQUEST = 'afterRequest';
	const ON_USER_TASK_FINISH = 'afterTaskFinish';
	const ON_USER_BEFORE_HAND_SHAKE = 'beforeHandshake';
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
			self::ON_HAND_SHAKE => HandshakeListener::class,
			self::ON_OPEN => OpenListener::class,
			self::ON_CLOSE => WebSocketCloseListener::class,
			self::ON_MESSAGE => MessageListener::class
		],
		ServerEnum::TYPE_PROCESS => [
			self::ON_WORKER_START => ProcessStartListener::class,
			self::ON_WORKER_STOP => ProcessStopListener::class,
			self::ON_MESSAGE => ProcessMessageListener::class
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
			self::ON_WORKER_SHUTDOWN,
			self::ON_USER_BEFORE_REQUEST,
			self::ON_USER_AFTER_REQUEST,
			self::ON_USER_TASK_FINISH,
			self::ON_USER_BEFORE_HAND_SHAKE,
			self::ON_USER_AFTER_OPEN,
			self::ON_USER_AFTER_CLOSE
		];
	}

	private function registerSystemEvent($eventTypes) {
		$swooleEvents = $this->getDefaultEvent();
		foreach ($eventTypes as $name) {
			$events = $swooleEvents[$name] ?? [];
			foreach ($events as $name => $event) {
				iloader()->get(EventDispatcher::class)->listen($name, $event);
			}
		}
	}

	private function registerUserEvent() {
		foreach ($this->getUserEvent() as $eventName) {
			$listener = sprintf('\\W7\\Core\\Listener\\%sListener', ucfirst($eventName));
			iloader()->get(EventDispatcher::class)->listen($eventName, $listener);

			$listener = sprintf('\\W7\\%s\\Listener\\%sListener', StringHelper::studly(App::$server->getType()), ucfirst($eventName));
			iloader()->get(EventDispatcher::class)->listen($eventName, $listener);

			$listener = sprintf('\\W7\\App\\Listener\\%sListener', ucfirst($eventName));
			iloader()->get(EventDispatcher::class)->listen($eventName, $listener);
		}
	}

	public function addServerEvents($server, array $events) {
		static::$event[$server] = $events;
	}

	public function websocketSupportHttp() {
		self::$event[ServerEnum::TYPE_WEBSOCKET][self::ON_REQUEST] = RequestListener::class;
	}

	public function register($eventTypes) {
		$this->registerSystemEvent($eventTypes);
		$this->registerUserEvent();
	}
}
