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

namespace W7\Core\Process;

use Swoole\Process\Pool as PoolManager;
use W7\App;
use W7\Core\Facades\Event;
use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\Pool\PoolAbstract;
use W7\Core\Server\ServerEvent;
use W7\Core\Server\SwooleServerAbstract;

abstract class ProcessServerAbstract extends SwooleServerAbstract {
	protected $masterServerType = ['manage'];

	public static $masterServer = false;
	public static $onlyFollowMasterServer = false;
	/**
	 * @var PoolAbstract
	 */
	protected $pool;

	abstract protected function register();

	protected function checkSetting() {
		$this->setting['host'] = $this->setting['host'] ?? '0.0.0.0';
		$this->setting['port'] = $this->setting['port'] ?? 'none';
		$this->setting['message_queue_key'] = $this->setting['message_queue_key'] ?? null;

		return parent::checkSetting();
	}

	protected function enableCoroutine() {
		$mqKey = $this->setting['message_queue_key'];
		parent::enableCoroutine();
		$this->setting['message_queue_key'] = $mqKey;
	}

	public function getStatus() {
		$pid = 0;
		if (file_exists($this->setting['pid_file'])) {
			$pid = file_get_contents($this->setting['pid_file']);
		}
		return [
			'host' => $this->setting['host'],
			'port' => $this->setting['port'],
			'type' => $this->setting['sock_type'],
			'workerNum' => $this->setting['worker_num'],
			'masterPid' => $pid
		];
	}

	public function getPool() {
		if ($this->pool) {
			return $this->pool;
		}

		if (App::$server instanceof ProcessServerAbstract) {
			$this->pool = new IndependentPool($this->getType(), $this->setting);
		} else {
			$this->pool = new DependentPool($this->getType(), $this->setting);
		}

		return $this->pool;
	}

	public function start() {
		$pool = $this->getPool();

		$this->registerService();
		$this->register();

		Event::dispatch(ServerEvent::ON_USER_BEFORE_START, [$pool]);

		return $pool->start();
	}

	public function listener(\Swoole\Server $server = null) {
		if (App::$server instanceof ProcessServerAbstract) {
			$pool = $this->pool = App::$server->getPool();
		} else {
			$pool = $this->getPool();
		}

		$this->register();

		if (!App::$server instanceof ProcessServerAbstract) {
			$pool->start();
		}

		return true;
	}

	protected function registerSwooleEvent($server, $event, $eventType) {
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			if (in_array($eventName, [ServerEvent::ON_WORKER_START, ServerEvent::ON_WORKER_STOP, ServerEvent::ON_MESSAGE])) {
				$this->pool->on($eventName, function (PoolManager $pool, $workerId) use ($eventName, $eventType) {
					Event::dispatch($this->getServerEventRealName($eventName, $eventType), [$this->getType(), $pool->getProcess(), $workerId, $this->pool->getProcessFactory(), $this->pool->getMqKey()]);
				});
			} else {
				$this->pool->on($eventName, function () use ($eventName, $eventType) {
					Event::dispatch($this->getServerEventRealName($eventName, $eventType), func_get_args());
				});
			}
		}
	}
}
