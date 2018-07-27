<?php
/**
 * author: alex
 * date: 18-7-27 下午6:02
 */
namespace W7\Core\Base;

use Swoole\Process as SwooleProcess;

/**
 * The process builder
 */
class ProcessBuilder
{
    /**
     * @var array
     */
    private static $processes = [];

    /**
     * @param string $name
     *
     * @return SwooleProcess
     */
    public static function create(string $name, $server): SwooleProcess
    {
        if (isset(self::$processes[$name])) {
            return self::$processes[$name];
        }

        $swooleProcess = new SwooleProcess(function (SwooleProcess $swooleProcess) use ($server, $name) {
            $processe = iloader()->singleton($name);
            $processe->run();
        });
        self::$processes[$name] = $swooleProcess;

        return $swooleProcess;
    }
}
