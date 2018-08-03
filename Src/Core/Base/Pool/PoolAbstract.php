<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:34
 */

namespace W7\Core\Base\Pool;


use Swoole\Coroutine;
use W7\App;

abstract class PoolAbstract implements PoolInterface
{
	/**
	 * 数据库名称
	 * @var string
	 */
	protected $connectionName = '';

	/**
	 * 最大连接数据
	 * @var int
	 */
	protected $maxActive = 100;

	/**
	 * 执行中连接队列
	 * @var \SplQueue $busyQueue
	 */
	protected $busyCount;

	/**
	 * 空间连接队列
	 * @var \SplQueue $idleQueue
	 */
	protected $idleQueue;

	/**
	 * 挂起协程ID队列，恢复时按顺序恢复
	 * @var \SplQueue $waitCoQueue
	 */
	protected $waitCoQueue;

	/**
	 * 等待数
	 * @var int
	 */
	protected $waitCount = 0;

	/**
	 * 正在执行数
	 * @var int
	 */
	protected $resumeCount = 0;

	public function __construct()
	{
		$this->busyCount = 0;
		$this->waitCount = 0;
		$this->resumeCount = 0;
		$this->idleQueue = new \SplQueue();
		$this->waitCoQueue = new \SplQueue();
		$this->init();
	}

	protected function init()
	{

	}

	public function getConnection()
	{

		ilogger()->info('coid ' . (Coroutine::getuid()));
		ilogger()->info('workid ' . (App::$server->server->worker_id));

		$connect = $this->createConnection();
		return $connect;

		//如果 空闲队列数+执行队列数 等于 最大连接数，则挂起协程
		ilogger()->info('busy count ' . $this->busyCount . '. queue count ' . $this->idleQueue->count() . ', maxactive' . $this->maxActive);
		if ($this->busyCount + $this->idleQueue->count() >= $this->maxActive) {
			//等待进程数++
			$this->waitCount++;
			ilogger()->info('suspend connection , count ' . $this->idleQueue->count() . '. wait count ' . $this->waitCount);
			//存放当前协程ID，以便恢复
			$coid = Coroutine::getuid();
			$this->waitCoQueue->push($coid);
			if (\Swoole\Coroutine::suspend($coid) == false) {
				//挂起失败时，抛出异常，恢复等待数
				$this->waitCount--;
				throw new \RuntimeException('Reach max connections! Cann\'t pending fetch!');
			}

			//回收连接时，恢复了协程，则从空闲中取出连接继续执行
			ilogger()->info('resume connection , count ' . $this->idleQueue->count() . '. resume count ' . $this->resumeCount);
			$this->resumeCount--;

			if ($this->idleQueue->count() > 0) {
				$connect = $this->getConnectionFromPool();
				$this->busyCount++;
				return $connect;
			} else {
				return false;
			}
		}

		$connect = $this->createConnection();
		$this->busyCount++;
		ilogger()->info('create connection , count ' . $this->idleQueue->count() . '. busy count ' . $this->busyCount . ' connect id ' . spl_object_hash($connect));

		return $connect;
	}

	public function release($connection)
	{
		ilogger()->info('release , count ' . $this->idleQueue->count() . '. busy count ' . $this->busyCount );
		$this->busyCount--;
		if ($this->idleQueue->count() < $this->maxActive)
		{
			$this->idleQueue->push($connection);
			if ($this->waitCount > 0)
			{
				$this->waitCount--;
				$this->resumeCount++;
				$coid = $this->getWaitCoFromPool();
				if (!empty($coid))
				{
					\Swoole\Coroutine::resume($coid);
				}
			}
			return true;
		}
	}

	public function setConnectionName($name)
	{
		$this->connectionName = $name;
		return true;
	}

	private function getConnectionFromPool()
	{
		return $this->idleQueue->shift();
	}

	private function getWaitCoFromPool()
	{
		return $this->waitCoQueue->shift();
	}
}