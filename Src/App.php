<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use W7\Core\Helper\Logger;
use W7\Http\Server\Server;

class App
{
    const IA_ROOT = __DIR__;

    /**
     * 服务器对象
     *
     * @var Server
     */
    public static $server;
    /**
     * @var \W7\Core\Helper\Loader;
     */
    private static $loader;
    private static $logger;
    public static $dbPool;

    public static function getLoader()
    {
        if (empty(self::$loader)) {
            self::$loader = new \W7\Core\Helper\Loader();
        }
        return self::$loader;
    }

    /**
     * @return Logger
     */
    public static function getLogger()
    {
        $defineConfig = iconfig()->getUserConfig('app');
        if (!empty(static::$logger) && static::$logger instanceof Logger) {
            return static::$logger;
        }

        /**
         * @var Logger $logger
         */
        static::$logger = iloader()->singleton(Logger::class);
        static::$logger->init($defineConfig['log']['log_file'], $defineConfig['log']['level'], $defineConfig['log']['flushInterval']);
        return static::$logger;
    }
}
