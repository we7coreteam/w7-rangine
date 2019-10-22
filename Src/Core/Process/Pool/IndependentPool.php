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

namespace W7\Core\Process\Pool;

use Swoole\Process;
use Swoole\Process\Pool as PoolManager;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Server\SwooleEvent;

/**
 * 该进程池由独立的process manager管理
 * Class IndependentPool
 * @package W7\Core\Process\Pool
 */
class IndependentPool extends PoolAbstract {
	private $ipcType = SWOOLE_IPC_NONE;
	private $pidFile;
	private $daemon;

	protected function init() {
		$this->pidFile = $this->config['pid_file'];
		$this->daemon = $this->config['daemonize'] ?? false;
	}

	private function setDaemon() {
		if ($this->daemon) {
			Process::daemon(true, false);
		}
	}

	public function start() {
		if ($this->processFactory->count() == 0) {
			return false;
		}

		$this->setDaemon();

		if (SWOOLE_VERSION >= '4.4.0') {
			$server = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey, true);
		} else {
			$server = new PoolManager($this->processFactory->count(), $this->ipcType, $this->mqKey);
		}

		$swooleEvents = iloader()->get(SwooleEvent::class)->getDefaultEvent();
		$eventTypes = [$this->serverType, 'manage'];
		foreach ($eventTypes as $name) {
			$event = $swooleEvents[$name];
			if ($name ==$this->serverType) {
				if ($this->ipcType == 0 || SWOOLE_VERSION >= '4.4.0') {
					unset($event['message']);
				}
			}
			if (!empty($event)) {
				$this->registerEvent($server, $event);
			}
		}

		file_put_contents($this->pidFile, getmypid());

		$server->start();
	}

	protected function registerEvent($server, array $event) {
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			if ($eventName == SwooleEvent::ON_START || $eventName == SwooleEvent::ON_MANAGER_START) {
				$server->on($eventName, function () use ($eventName) {
					iloader()->get(EventDispatcher::class)->dispatch($eventName, func_get_args());
				});
			} else {
				$server->on($eventName, function (PoolManager $pool, $workerId) use ($eventName) {
					iloader()->get(EventDispatcher::class)->dispatch($eventName, [$this->serverType, $pool->getProcess(), $workerId, $this->processFactory, $this->mqKey]);
				});
			}
		}
	}
}
