<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 18-7-26
 * Time: 下午3:27
 */

namespace W7\Core\Helper;


use Swoole\Coroutine\Redis;

class RedisDriver
{
    static protected $redis;

    public function __construct()
    {
        $defineConf = iconfig()->getUserConfig("define");
    }
}