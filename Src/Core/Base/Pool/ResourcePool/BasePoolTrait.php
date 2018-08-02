<?php
/**
 * author: alex
 * date: 18-8-1 下午4:03
 */

namespace W7\Core\Base\Pool\ResourcePool;


use W7\Core\Helper\Logger;

trait BasePoolTrait
{
    /**
     * @var string The pool name
     */
    protected $name = 'default';

    /**
     * metadata for connections
     * @var array[]
     * [
     *  'res id' => [
     *      'createAt' => int,
     *      'activeAt' => int, // Recent active time - 最近活跃时间
     *  ]
     * ]
     */
    protected $metas = [];

    /**
     * default 30 seconds
     * @var int
     */
    protected $expireTime = 30;

    /**
     * Initialize the pool size
     * @var int
     */
    protected $initSize = 0;

    /**
     * 扩大的增量(当资源不够时，一次增加资源的数量)
     * @var int
     */
    protected $stepSize = 1;

    /**
     * The maximum size of the pool resources
     * @var int
     */
    protected $maxSize = 200;

    /**
     * Maximum waiting time(ms) when get connection. - 获取资源等待超时时间
     * > 0  waiting time(ms)
     * 0    Do not wait
     * -1   Always waiting
     * @var int
     */
    protected $waitTimeout = 3000;

    /**
     * @var int The max free time(minutes) the free resource - 资源最大生命时长
     */
    protected $maxLifetime = 30;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getInitSize(): int
    {
        return $this->initSize;
    }

    /**
     * @param int $initSize
     */
    public function setInitSize(int $initSize)
    {
        $this->initSize = $initSize < 0 ? 0 : $initSize;
    }

    /**
     * @return int
     */
    public function getStepSize(): int
    {
        return $this->stepSize;
    }

    /**
     * @param int $stepSize
     */
    public function setStepSize(int $stepSize)
    {
        $this->stepSize = $stepSize < 1 ? 1 : $stepSize;
    }

    /**
     * @return int
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * @param int $maxSize
     * @throws \InvalidArgumentException
     */
    public function setMaxSize(int $maxSize)
    {
        if ($maxSize < 1) {
            throw new \InvalidArgumentException('The resource pool max size cannot lt 1');
        }

        $this->maxSize = $maxSize;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger( $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return int
     */
    public function getMaxLifetime(): int
    {
        return $this->maxLifetime;
    }

    /**
     * @param int $maxLifetime
     */
    public function setMaxLifetime(int $maxLifetime)
    {
        $this->maxLifetime = $maxLifetime;
    }

    /**
     * @param string $resId
     * @return array
     */
    public function getMeta(string $resId): array
    {
        return $this->metas[$resId] ?? [];
    }

    /**
     * @return array[]
     */
    public function getMetas(): array
    {
        return $this->metas;
    }
}