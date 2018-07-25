<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-25
 * Time: 上午11:05
 */

namespace W7\Core\Base;


use Psr\SimpleCache\CacheInterface;

abstract class CacheAbstract implements CacheInterface
{

    protected static $driverType;

    /**
     * @var array
     */
    private $drivers = [];

    /**
     * TODO add serializer mechanism
     * @var null|string
     */
    private $serializer = null;
    abstract public function getDriver(string $driverType = null);
    /**
     * @return array
     */
    protected function getDrivers(): array
    {
        return array_merge(static::$driverType, $this->defaultDrivers());
    }

    /**
     * @return array
     */
    protected function defaultDrivers()
    {
        return [
            'memory' => '',
        ];
    }
    abstract public function set($key, $value ,$ttl=null);
    abstract public function get($key, $default = null);
    public function delete($key){}
    public function clear(){}
    public function getMultiple($key, $default = null){}
    public function setMultiple($value, $ttl=null){}
    public function deleteMultiple($key, $default = null){}
    public function has($key){}

}