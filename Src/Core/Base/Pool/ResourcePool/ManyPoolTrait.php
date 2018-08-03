<?php
/**
 * author: alex
 * date: 18-8-1 下午4:04
 */

namespace W7\Core\Base\Pool\ResourcePool;

trait ManyPoolTrait
{
    use BasePoolTrait;

    /**
     * @var int Minimum free/idle connection. 最小空闲连接
     */
    protected $minIdle = 3;

    /**
     * @var int Maximum free/idle connection. 最大空闲连接
     */
    protected $maxIdle = 10;

    /**
     * @var int The max free/idle timeout(minutes) the free resource - 资源最大空闲时间
     */
    protected $maxIdleTime = 10;

    /**
     * @var bool Whether validate resource on get
     */
    protected $validateOnGet = true;

    /**
     * @var bool Whether validate resource on put
     */
    protected $validateOnPut = true;

    /**
     * @return int
     */
    public function getMinIdle(): int
    {
        return $this->minIdle;
    }

    /**
     * @param int $minIdle
     */
    public function setMinIdle(int $minIdle)
    {
        $this->minIdle = $minIdle;
    }

    /**
     * @return int
     */
    public function getMaxIdle(): int
    {
        return $this->maxIdle;
    }

    /**
     * @param int $maxIdle
     */
    public function setMaxIdle(int $maxIdle)
    {
        $this->maxIdle = $maxIdle;
    }

    /**
     * @return int
     */
    public function getWaitTimeout(): int
    {
        return $this->waitTimeout;
    }

    /**
     * @param int $maxWait
     */
    public function setWaitTimeout(int $maxWait)
    {
        $this->waitTimeout = $maxWait;
    }

    /**
     * @return int
     */
    public function getMaxIdleTime(): int
    {
        return $this->maxIdleTime;
    }

    /**
     * @param int $maxIdleTime
     */
    public function setMaxIdleTime(int $maxIdleTime)
    {
        $this->maxIdleTime = $maxIdleTime;
    }

    /**
     * @return int
     */
    public function getExpireTime(): int
    {
        return $this->expireTime;
    }

    /**
     * @param int $expireTime
     */
    public function setExpireTime(int $expireTime)
    {
        $this->expireTime = $expireTime;
    }

    /**
     * @return bool
     */
    public function isValidateOnGet(): bool
    {
        return $this->validateOnGet;
    }

    /**
     * @param bool $validateOnGet
     */
    public function setValidateOnGet(bool $validateOnGet)
    {
        $this->validateOnGet = $validateOnGet;
    }

    /**
     * @return bool
     */
    public function isValidateOnPut(): bool
    {
        return $this->validateOnPut;
    }

    /**
     * @param bool $validateOnPut
     */
    public function setValidateOnPut(bool $validateOnPut)
    {
        $this->validateOnPut = $validateOnPut;
    }
}
