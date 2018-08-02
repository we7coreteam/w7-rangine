<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:34
 */

namespace W7\Core\Base\Pool;


use Swoole\Coroutine;
use W7\App\App;
use W7\Core\Base\Pool\ResourcePool\ResourcePool;

abstract class PoolAbstract implements PoolInterface
{
	/**
	 * 数据库名称
	 * @var string
	 */
	protected $connectionName = '';

	/**
	 * 最小连接数
	 * @var int
	 */
	protected $minActive = 5;

	/**
	 * 最大连接数据
	 * @var int
	 */
	protected $maxActive = 100;

	/**
	 * 最大等待数
	 * @var int
	 */
	protected $maxWait = 20;

	/**
	 * 最大空闲时间
	 * @var int
	 */
	protected $maxIdleTime = 60;

	/**
	 * 最大等待时间
	 * @var int
	 */
	protected $maxWaitTime = 3;

	/**
	 * 超时时间
	 * @var int
	 */
	protected $timeout = 3;
    /**
     * @var ResourcePool
     */
	protected $queue;
	protected $currentCount = 0;

	protected $mysqlProcess;

	public function __construct()
	{
//		$this->queue = new ResourcePool([
//		    'initSize'=>$this->maxActive,
//            'maxSize'=>$this->maxActive,
//        ]);
//
//		$this->init();
//        return $this->queue;
        $this->init();
	}

	protected function init()
	{
	}

	public function setQueue($queue)
    {
        $this->queue = $queue;
    }
	public function setMysqlProcess($process)
    {
        $this->mysqlProcess = $process;
        $this->mysqlProcess->push("yangshen");;
    }

	public function getConnection()
	{
		$connectQueue = [];
		$item = $this->queue->get();
//		ilogger()->info('queue - ' . json_encode($item));
//		ilogger()->info('get count - ' . $this->queue->count());
		if (!$this->queue->isEmpty()) {
			$connect = $this->getEffectiveConnection($this->queue->count());
			ilogger()->info('get by queue - ' . $connect->connectionId);
			ilogger()->info('get by uid - ' . Coroutine::getuid());
		}

		if (empty($connect)) {
			if ($this->currentCount >= $this->maxActive) {
				//throw new \RuntimeException('Connection pool queue is full. Please add the MaxActive value');
			}
			$connect = $this->createConnection();
			$this->currentCount++;
			ilogger()->info('currentCount - ' . $this->currentCount);
			ilogger()->info('create - ' . $connect->connectionId . ' at ' . $connect->createTime);
		}

		return $connect;
	}

	public function release($connection)
	{
//		ilogger()->info('release count - ' . $this->queue->count());
		if ($this->queue->count() < $this->maxActive) {
//			ilogger()->info('release - ' . $connection->connectionId);
			$this->queue->put($connection);
		}
//		ilogger()->info('release end count - ' . $this->queue->count());
		return true;
	}

	public function setConnectionName($name)
	{
		$this->connectionName = $name;
		return true;
	}

	private function getEffectiveConnection(int $queueNum)
	{
		if ($queueNum <= $this->minActive) {
			return $this->getConnectionFromPool();
		}

		$time = time();
		$moreActive = $queueNum - $this->minActive;
		$maxWaitTime = $this->maxWaitTime;

		for ($i = 0; $i < $moreActive; $i++) {
			$connection = $this->getConnectionFromPool();
			$lastTime = $connection->createTime;
			if ($time - $lastTime < $maxWaitTime) {
				return $connection;
			}
			$this->currentCount--;
		}

		return $this->getConnectionFromPool();
	}

	private function getConnectionFromPool()
	{
		return $this->queue->get();
	}
}