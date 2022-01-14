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

namespace W7\Core\Pool;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use W7\Contract\Pool\PoolInterface;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Pool\Event\MakeConnectionEvent;
use W7\Core\Pool\Event\PopConnectionEvent;
use W7\Core\Pool\Event\PushConnectionEvent;
use W7\Core\Pool\Event\ResumeConnectionEvent;
use W7\Core\Pool\Event\SuspendConnectionEvent;

abstract class CoPoolAbstract implements PoolInterface {
	use AppCommonTrait;

	protected $poolName;

	protected $type;

	/**
	 * Maximum number of connections
	 * @var int
	 */
	protected $maxActive = 100;

	/**
	 * Connection in execution
	 * @var \SplQueue $busyQueue
	 */
	protected $busyCount;

	/**
	 * Idle connection queue
	 * @var Channel $idleQueue
	 */
	protected $idleQueue;

	/**
	 * Suspend the coroutine ID queue and restore in order
	 * @var \SplQueue
	 */
	protected $waitQueue;

	/**
	 * 等待数
	 * @var int
	 */
	protected $waitCount = 0;

	public function __construct($name) {
		$this->poolName = $name;

		$this->busyCount = 0;
		$this->waitCount = 0;

		$this->waitQueue = new \SplQueue();
	}

	public function getPoolName() {
		return $this->poolName;
	}

	abstract public function createConnection();

	public function getConnection() {
		//If the number of execution queues is equal to the maximum number of connections, the coroutine is suspended
		if ($this->busyCount >= $this->getMaxCount()) {
			//No. of waiting processes++
			$this->waitCount++;

			$this->getEventDispatcher()->dispatch(new SuspendConnectionEvent($this->type, $this->poolName, $this));

			if ($this->suspendCurrentCo() == false) {
				//When a hang fails, an exception is thrown to restore the wait count
				$this->waitCount--;
				throw new \RuntimeException('Reach max connections! Cann\'t pending fetch!');
			}
			//When the connection is retracted, the coroutine is restored, and the connection is removed from idle to continue execution
			$this->getEventDispatcher()->dispatch(new ResumeConnectionEvent($this->type, $this->poolName, $this));
		}

		if ($this->getIdleCount() > 0) {
			$this->getEventDispatcher()->dispatch(new PopConnectionEvent($this->type, $this->poolName, $this));

			$connect = $this->getConnectionFromPool();
			$this->busyCount++;
			return $connect;
		}

		$connect = $this->createConnection();
		$this->busyCount++;

		$this->getEventDispatcher()->dispatch(new MakeConnectionEvent($this->type, $this->poolName, $this));

		return $connect;
	}

	public function releaseConnection($connection) {
		$this->busyCount--;
		if ($this->getIdleCount() < $this->getMaxCount()) {
			$this->setConnectionFormPool($connection);
			$this->getEventDispatcher()->dispatch(new PushConnectionEvent($this->type, $this->poolName, $this));

			if ($this->waitCount > 0) {
				$this->waitCount--;
				$this->resumeCo();
			}
			return true;
		}
	}

	public function getMaxCount() {
		return $this->maxActive;
	}

	public function setMaxCount(int $maxActive) {
		$this->maxActive = $maxActive;
		$this->idleQueue = new Channel($this->maxActive);
	}

	private function suspendCurrentCo() {
		$coid = Coroutine::getuid();
		$this->waitQueue->push($coid);
		return Coroutine::suspend($coid);
	}

	private function resumeCo() {
		$coid = $this->waitQueue->shift();
		if (!empty($coid)) {
			Coroutine::resume($coid);
		}
		return true;
	}

	private function getConnectionFromPool() {
		return $this->idleQueue->pop();
	}

	private function setConnectionFormPool($connection) {
		return $this->idleQueue->push($connection);
	}

	public function getIdleCount() {
		return $this->idleQueue->length();
	}

	public function getBusyCount() {
		return $this->busyCount;
	}

	public function getWaitCount() {
		return $this->waitCount;
	}
}
