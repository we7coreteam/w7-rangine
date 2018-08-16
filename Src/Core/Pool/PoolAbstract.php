<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:34
 */

namespace W7\Core\Pool;

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

	public function __construct()
	{
		$this->busyCount = 0;
		$this->waitCount = 0;
		//$this->idleQueue = new \SplQueue();
		$this->waitCoQueue = new \SplQueue();

		$this->idleQueue = new Coroutine\Channel($this->maxActive);
		ilogger()->info('pool construct ');
	}

	public function createConnection($config) {
		if (isCo()) {
			$connection = new \Swoole\Coroutine\MySQL();
			$connection->connect([
				'host' => $config['host'],
				'port' => !empty($config['port']) ? $config['port'] : '3306',
				'user' => $config['username'],
				'password' => $config['password'],
				'database' => $config['database'],
				'charset' => $config['charset'],
				'strict_type' => false,
				'fetch_mode' => true,
			]);
			ilogger()->info('connection id ' . spl_object_hash($connection));
			if ($connection === false || !empty($connection->connect_errno)) {
				throw new \RuntimeException($connection->connect_error);
			}
		} else {

		}
		return $connection;
	}

	public function getConnection($config)
	{
		ilogger()->info('coid ' . (Coroutine::getuid()));
		ilogger()->info('workid ' . (App::$server->server->worker_id));

		/**
		 * 如果当前有空闲连接，并且连接大于要执行的数，直接返回连接
		 */
		if (!$this->idleQueue->isEmpty() && $this->idleQueue->length() > 0) {
			ilogger()->info('get by queue, count ' . $this->idleQueue->length());
			$connect = $this->getConnectionFromPool();
			$this->busyCount++;
			return $connect;
		}

		//如果 空闲队列数+执行队列数 等于 最大连接数，则挂起协程
		ilogger()->info('busy count ' . $this->busyCount . '. queue count ' . $this->idleQueue->length() . ', maxactive' . $this->maxActive);
		if ($this->busyCount + $this->idleQueue->length() >= $this->maxActive) {
			//等待进程数++
			$this->waitCount++;
			ilogger()->info('suspend connection , count ' . $this->idleQueue->length() . '. wait count ' . $this->waitCount);
			//存放当前协程ID，以便恢复
			$coid = Coroutine::getuid();
			$this->waitCoQueue->push($coid);
			if (\Swoole\Coroutine::suspend($coid) == false) {
				//挂起失败时，抛出异常，恢复等待数
				$this->waitCount--;
				throw new \RuntimeException('Reach max connections! Cann\'t pending fetch!');
			}

			//回收连接时，恢复了协程，则从空闲中取出连接继续执行
			ilogger()->info('resume connection , count ' . $this->idleQueue->length());
		}

		$connect = $this->createConnection($config);
		$this->busyCount++;
		ilogger()->info('create connection , count ' . $this->idleQueue->length() . '. busy count ' . $this->busyCount);

		return $connect;
	}

	public function release($connection)
	{
		$this->busyCount--;
		ilogger()->info('release connection , count ' . $this->idleQueue->length() . '. busy count ' . $this->busyCount);
		if ($this->idleQueue->length() < $this->maxActive) {
			$this->idleQueue->push($connection);

			ilogger()->info('release push connection , count ' . $this->idleQueue->length() . '. busy count ' . $this->busyCount);
			if ($this->waitCount > 0) {
				$this->waitCount--;
				$coid = $this->getWaitCoFromPool();
				if (!empty($coid)) {
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

	/**
	 * @param int $maxActive
	 */
	public function setMaxActive(int $maxActive) {
		$this->maxActive = $maxActive;
	}

	private function getConnectionFromPool()
	{
		return $this->idleQueue->pop();
	}

	private function getWaitCoFromPool()
	{
		return $this->waitCoQueue->shift();
	}
}
