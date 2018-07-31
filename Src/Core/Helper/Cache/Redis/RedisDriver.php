<?php
/**
 * author: alex
 * date: 18-7-27 下午6:02
 */
namespace W7\Core\Helper\Cache\Redis;

use W7\Core\Helper\Cache\AbstractRedisDriver;

/**
 * Redis
 * key and string
 * @method int append($key, $value)
 * @method int decr($key)
 * @method int decrBy($key, $value)
 * @method string getRange($key, $start, $end)
 * @method int incr($key)
 * @method int incrBy($key, $value)
 * @method float incrByFloat($key, $increment)
 * @method int strlen($key)
 * @method bool mset(array $array)
 * @method int ttl($key)
 * @method int expire($key, $seconds)
 * @method int pttl($key)
 * @method int persist($key)
 * hash
 * @method int hSet($key, $hashKey, $value)
 * @method bool hSetNx($key, $hashKey, $value)
 * @method string hGet($key, $hashKey)
 * @method int hLen($key)
 * @method int hDel($key, $hashKey1, $hashKey2 = null, $hashKeyN = null)
 * @method array hKeys($key)
 * @method array hVals($key)
 * @method array hGetAll($key)
 * @method bool hExists($key, $hashKey)
 * @method bool hIncrBy($key, $hashKey, $value)
 * @method bool hIncrByFloat($key, $field, $increment)
 * @method bool hMset($key, $hashKeys)
 * list
 * @method array brPop(array $keys, $timeout)
 * @method array blPop(array $keys, $timeout)
 * @method int lLen($key)
 * @method int lPush($key, $value1, $value2 = null, $valueN = null)
 * @method string lPop($key)
 * @method array lRange($key, $start, $end)
 * @method int lRem($key, $value, $count)
 * @method bool lSet($key, $index, $value)
 * @method int rPush($key, $value1, $value2 = null, $valueN = null)
 * @method string rPop($key)
 * set
 * @method int sAdd($key, $value1, $value2 = null, $valueN = null)
 * @method array|bool scan(&$iterator, $pattern = null, $count = 0)
 * @method int sCard($key)
 * @method array sDiff($key1, $key2, $keyN = null)
 * @method array sInter($key1, $key2, $keyN = null)
 * @method int sInterStore($dstKey, $key1, $key2, $keyN = null)
 * @method int sDiffStore($dstKey, $key1, $key2, $keyN = null)
 * @method array sMembers($key)
 * @method bool sMove($srcKey, $dstKey, $member)
 * @method bool sPop($key)
 * @method string|array sRandMember($key, $count = null)
 * @method int sRem($key, $member1, $member2 = null, $memberN = null)
 * @method array sUnion($key1, $key2, $keyN = null)
 * @method int sUnionStore($dstKey, $key1, $key2, $keyN = null)
 * sort
 * @method int zAdd($key, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null)
 * @method array zRange($key, $start, $end, $withscores = null)
 * @method int zRem($key, $member1, $member2 = null, $memberN = null)
 * @method array zRevRange($key, $start, $end, $withscore = null)
 * @method array zRangeByScore($key, $start, $end, array $options = array())
 * @method array zRangeByLex($key, $min, $max, $offset = null, $limit = null)
 * @method int zCount($key, $start, $end)
 * @method int zRemRangeByScore($key, $start, $end)
 * @method int zRemRangeByRank($key, $start, $end)
 * @method int zCard($key)
 * @method float zScore($key, $member)
 * @method int zRank($key, $member)
 * @method float zIncrBy($key, $value, $member)
 * @method int zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
 * @method int zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
 * pub/sub
 * @method int publish($channel, $message)
 * @method string|array psubscribe($patterns, $callback)
 * @method string|array subscribe($channels, $callback)
 * @method array|int pubsub($keyword, $argument)
 * script
 * @method mixed eval($script, $args = array(), $numKeys = 0)
 * @method mixed evalSha($scriptSha, $args = array(), $numKeys = 0)
 * @method mixed script($command, $script)
 * @method string getLastError()
 * @method bool clearLastError()
 */
class RedisDriver extends AbstractRedisDriver
{
    
    /**
     * RedisDriver constructor.
     * @throws RedisException
     */
    public function __construct()
    {
        if (static::$redis instanceof \Redis) {
            return static::$redis;
        }
        $defineConf = iconfig()->getUserConfig("app");
        $redisConfUrl  = $defineConf['cache']['redis']['url'];
        $timeout = $defineConf['cache']['redis']['timeout'];
        $redisConf = $this->parseUri($redisConfUrl);
        $host = $redisConf['host'];
        $port = (int)$redisConf['port'];
        /**
         * @var Redis static::$redis
         */
        static::$redis = new \Redis();
        static::$redis->connect($host, $port);
        if (isset($config['auth']) && false === static::$redis->auth($redisConf['auth'])) {
            $error = sprintf('Redis connection authentication failed host=%s port=%d auth=%s', $host, (int)$port, (string)$redisConf['auth']);
            throw new RedisException($error);
        }

        if (isset($config['database']) && $redisConf['database'] < 16 && false === static::$redis->select($redisConf['database'])) {
            $error = sprintf('Redis selection database failure host=%s port=%d database=%d', $host, (int)$port, (int)$redisConf['database']);
            throw new RedisException($error);
        }

        if (!static::$redis) {
            $error = sprintf('Redis connection failure host=%s port=%d', $host, $port);
            throw new RedisException($error);
        }
        $this->setPrefix();
        return static::$redis;
    }
}
