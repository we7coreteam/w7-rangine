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

use Swoole\Process;
use W7\App;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Exception\CommandException;

abstract class ServerAbstract implements ServerInterface {
	/**
	 * @var \Swoole\Server
	 */
	public $server;

	/**
	 * 配置
	 * @var
	 */
	public $setting;
	/**
	 * @var 连接配置
	 */
	public $connection;

	public static $canAddSubServer = true;

	/**
	 * ServerAbstract constructor.
	 * @throws CommandException
	 */
	public function __construct() {
		!App::$server && App::$server = $this;
		$setting = \iconfig()->getServer();
		$this->checkSetting($setting);
		$this->setting = array_merge([], $setting['common']);
		$this->enableCoroutine();
		$this->connection = $setting[$this->getType()];
	}

	protected function checkSetting($setting) {
		if (empty($setting[$this->getType()])) {
			throw new CommandException(sprintf('缺少服务配置 %s', $this->getType()));
		}
	}

	/**
	 * Get pname
	 *
	 * @return string
	 */
	public function getPname() {
		return $this->setting['pname'];
	}

	public function getStatus() {
		$pidFile = $this->setting['pid_file'];
		if (file_exists($pidFile)) {
			$pids = explode(',', file_get_contents($pidFile));
		}
		return [
			'host' => $this->connection['host'],
			'port' => $this->connection['port'],
			'type' => $this->connection['sock_type'],
			'mode' => $this->connection['mode'],
			'workerNum' => $this->setting['worker_num'],
			'masterPid' => !empty($pids[0]) ? $pids[0] : 0,
			'managerPid' => !empty($pids[1]) ? $pids[1] : 0,
		];
	}

	public function getServer() {
		return $this->server;
	}

	public function listener(\Swoole\Server $server) {
	}

	public function isRun() {
		$status = $this->getStatus();
		if (!empty($status['masterPid'])) {
			return true;
		} else {
			return false;
		}
	}

	protected function enableCoroutine() {
		$this->setting['enable_coroutine'] = true;
		$this->setting['task_enable_coroutine'] = true;
		$this->setting['task_ipc_mode'] = 1;
		$this->setting['message_queue_key'] = '';
	}

	public function stop() {
		$status = $this->getStatus();
		$timeout = 20;
		$startTime = time();
		$result = true;

		while (Process::kill($status['masterPid'], 0)) {
			$result = Process::kill($status['masterPid'], SIGTERM);
			if ($result) {
				break;
			}
			if (!$result) {
				if (time() - $startTime >= $timeout) {
					$result = false;
					break;
				}
				usleep(10000);
			}
		}

		if (!file_exists($this->setting['pid_file'])) {
			$result = true;
		} else {
			unlink($this->setting['pid_file']);
		}

		App::$server = null;
		return $result;
	}

	public function registerService() {
		$this->registerSwooleEventListener();
	}

	protected function registerSwooleEventListener() {
		iloader()->singleton(SwooleEvent::class)->register();

		$swooleEvents = iloader()->singleton(SwooleEvent::class)->getDefaultEvent();
		$eventTypes = [$this->getType(), 'task', 'manage'];
		foreach ($eventTypes as $name) {
			$event = $swooleEvents[$name];
			if (!empty($event)) {
				$this->registerEvent($event);
			}
		}
	}

	protected function registerEvent($event) {
		if (empty($event)) {
			return true;
		}
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			if ($eventName == SwooleEvent::ON_REQUEST) {
				$server = \W7\App::$server->server;
				$this->server->on(SwooleEvent::ON_REQUEST, function ($request, $response) use ($server) {
					iloader()->singleton(EventDispatcher::class)->dispatch(SwooleEvent::ON_REQUEST, [$server, $request, $response]);
				});
			} else {
				$this->server->on($eventName, function () use ($eventName) {
					iloader()->singleton(EventDispatcher::class)->dispatch($eventName, func_get_args());
				});
			}
		}
	}
}
