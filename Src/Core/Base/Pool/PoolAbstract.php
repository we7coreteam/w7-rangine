<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:34
 */

namespace W7\Core\Base\Pool;

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
    protected $maxActive = 10;

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
    protected $queue;
    protected $currentCount = 0;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function getConnection()
    {
        if (!$this->queue->isEmpty()) {
            $connect = $this->getEffectiveConnection($this->queue->count());
        }

        if (empty($connect)) {
            if ($this->currentCount >= $this->maxActive) {
                throw new \RuntimeException('Connection pool queue is full. Please add the MaxActive value');
            }
            $connect = $this->createConnection();
            $this->currentCount++;
        }

        return $connect;
    }

    public function release($connection)
    {
        if ($this->queue->count() < $this->maxActive) {
            $this->queue->push($connection);
        }
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
        return $this->queue->shift();
    }
}
