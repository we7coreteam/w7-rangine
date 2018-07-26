<?php

namespace W7\Core\Base;

use Swoole\Coroutine;
use Swoole\Process as SwooleProcess;
use W7\Core\Exception\ProcessException;


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
     * @return Process
     */
    public static function create(string $name, $server): SwooleProcess
    {
        if (isset(self::$processes[$name])) {
            return self::$processes[$name];
        }

        $swooleProcess = new SwooleProcess(function (SwooleProcess $swooleProcess) use ($server, $name) {
            call_user_func([$name, "run"]);
        });
        $process = new Process($swooleProcess);
        self::$processes[$name] = $process;

        return $swooleProcess;
    }

}
