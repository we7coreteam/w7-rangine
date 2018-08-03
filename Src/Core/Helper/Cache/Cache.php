<?php
/**
 * author: alex
 * date: 18-7-27 下午6:02
 */

namespace W7\Core\Helper\Cache;

use W7\Core\Base\Cache\CacheAbstract;
use W7\Core\Helper\Cache\Redis\RedisCoroutineDriver;
use W7\Core\Helper\Cache\Redis\RedisDriver;
use W7\Core\Helper\MemoryCache;

/**
 * @method string|bool get($key, $default = null)
 * @method bool delete($key)
 * @method bool clear()
 * @method array getMultiple($keys, $default = null)
 * @method bool setMultiple($values, $ttl = null)
 * @method bool deleteMultiple($keys)
 * @method int has($key)
 */
class Cache
{
    /**
     * @var string
     */
    private $driver = 'redis';

    const CONTEXT_DATA_KEY = 'cacheDirver';



    const FIVE_MINUTE_MILLISECOND = '300000';


    const FIVE_MINUTE_SCOND = '300';


    const ONE_DAY_MILLISECOND = '86400000000';


    /**
     * Persists data in the Cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param  string                $key   The key of the item to store.
     * @param int|double|string|bool $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and the driver
     *                                      supports TTL then the library may set a default value for it or let the
     *                                      driver take care of that.
     * @return bool True on success and false on failure.
     * @throws \InvalidArgumentException If the $value string is not a legal value
     */
    public function set($key, $value, $ttl = null): bool
    {
        $valueType = \gettype($value);
        if (! \in_array($valueType, ['integer', 'double', 'boolean', 'string'], true)) {
            throw new \InvalidArgumentException('Invalid value type');
        }
        try {
            $ret = $this->getDriver()->set($key, $value, $ttl);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw new \InvalidArgumentException('Invalid value type');
        }
        return $ret;
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     * @throws \RuntimeException If the $method does not exist
     * @throws \InvalidArgumentException If the driver dose not exist
     */
    public function __call($method, $arguments)
    {
        $availableMethods = [
            'has',
            'get',
            'set',
            'delete',
            'getMultiple',
            'setMultiple',
            'deleteMultiple',
            'clear',
        ];
        if (! \in_array($method, $availableMethods, true)) {
            throw new \RuntimeException(sprintf('Method not exist, method=%s', $method));
        }
        $driver = $this->getDriver();
        return $driver->$method(...$arguments);
    }

    /**
     * @param string|null $driver
     * @throws \InvalidArgumentException When driver does not exist
     * @return CacheAbstract
     */
    public function getDriver(string $driver = null)
    {
        $currentDriver = $driver ?? $this->driver;
        $this->driver  = $currentDriver;
        $drivers = $this->getDrivers();
        if (! isset($drivers[$currentDriver])) {
            throw new \InvalidArgumentException(sprintf('Driver %s not exist', $currentDriver));
        }
        //TODO If driver component not loaded, throw an exception.
        if (isCo()) {
            $driverObj = iloader()->singleton($drivers[$currentDriver]['co']);
        } else {
            $driverObj = iloader()->singleton($drivers[$currentDriver]['sync']);
        }
        return $driverObj;
    }

    /**
     * @return array
     */
    protected function getDrivers(): array
    {
        return [
            'memory'=> MemoryCache::class,
            'redis' => [
                "co" => RedisCoroutineDriver::class,
//                "sync" => SyncRedisDriver::class,
                "sync" => RedisDriver::class,
            ],
        ];
    }
}
