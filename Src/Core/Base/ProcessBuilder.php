<?php

namespace W7\Core\Base;
;
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
     * @return SwooleProcess
     */
    public static function create(string $name, $server): SwooleProcess
    {
        if (isset(self::$processes[$name])) {
            return self::$processes[$name];
        }

        $swooleProcess = new SwooleProcess(function (SwooleProcess $swooleProcess) use ($server, $name) {
            call_user_func([$name, "run"]);
        });
        self::$processes[$name] = $swooleProcess;

        return $swooleProcess;
    }

}
