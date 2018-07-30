<?php
/**
 * author: alex
 * date: 18-7-27 下午6:02
 */

namespace W7\Core\Helper\Cache\Redis;

use Swoole\Redis;
use W7\Core\Helper\Cache\AbstractRedisDriver;

class SyncRedisDriver extends AbstractRedisDriver
{
    /**
     * RedisDriver constructor.
     * @throws RedisException
     */
    public function __construct()
    {
        if (static::$redis instanceof Redis) {
            return static::$redis;
        }
        $defineConf = iconfig()->getUserConfig("define");
        $redisConfUrl  = $defineConf['cache']['redis']['url'];
        $timeout = $defineConf['cache']['redis']['timeout'];
        $redisConf = $this->parseUri($redisConfUrl);
        $host = $redisConf['host'];
        $port = (int)$redisConf['port'];

        $options['timeout'] = 1.5;
        if (isset($config['auth'])) {
            $options['password'] = $config['auth'];
        }

        if (isset($config['database']) && $redisConf['database'] < 16) {
            $options['database'] = $config['database'];
        }
        $client = new Redis($options);
        $client->connect($host, $port, function ($client, $result) {
            if ($result === false) {
                throw new \RedisException("SyncRedis is wrong");
            }
        });
        static::$redis = $client;
        if (!static::$redis) {
            $error = sprintf('Redis connection failure host=%s port=%d', $host, $port);
            throw new RedisException($error);
        }
        return static::$redis;
    }
    /**
     * call method by redis client
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    protected function call(string $method, array $params)
    {
        $client = static::$redis;
        $callback = function ($client, $result) {
        };
        $params[] = $callback;
        static::$redis->$method(...$params);
        return $result;
    }
}
