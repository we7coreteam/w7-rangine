<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:25
 */

namespace W7;

use Dotenv\Dotenv;
use W7\Core\Base\Logger;
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

    public static function getLoader()
    {
        if (empty(self::$loader)) {
            self::$loader = new \W7\Core\Helper\Loader();
        }
        return self::$loader;
    }

    public static function logInit()
    {
        $defineConfig = iconfig()->getUserConfig('define');
        $logfile = RUNTIME_PATH . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "w7.log";
        Logger::init($logfile, $defineConfig['log']['level'], $defineConfig['log']['flushInterval']);
    }


    public static function doteEnv()
    {
        iconfig()->getUserConfig('define');
        if (file_exists(BASE_PATH . DIRECTORY_SEPARATOR . ".env")) {
            $dotenv = new Dotenv(BASE_PATH);
            $dotenv->load();
        }
    }
}
