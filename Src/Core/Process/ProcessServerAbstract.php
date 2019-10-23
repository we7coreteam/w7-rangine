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
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\Pool\PoolAbstract;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\SwooleEvent;

abstract class ProcessServerAbstract extends ServerAbstract {
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

	public function start() {
		$this->pool = new IndependentPool($this->getType(), $this->setting);
		$this->register();

		$this->registerService();

		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this->pool]);

		return $this->pool->start();
	}

	public function listener(\Swoole\Server $server = null) {
		$this->pool = new DependentPool($this->getType(), $this->setting);
		$this->register();
		return $this->pool->start();
	}

	protected function registerEvent($event) {
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			if ($eventName == SwooleEvent::ON_START || $eventName == SwooleEvent::ON_MANAGER_START) {
				$this->pool->on($eventName, function () use ($eventName) {
					iloader()->get(EventDispatcher::class)->dispatch($eventName, func_get_args());
				});
			} else {
				$this->pool->on($eventName, function (PoolManager $pool, $workerId) use ($eventName) {
					iloader()->get(EventDispatcher::class)->dispatch($eventName, [$this->getType(), $pool->getProcess(), $workerId, $this->pool->getProcessFactory(), $this->pool->getMqKey()]);
				});
			}
		}
	}
}
