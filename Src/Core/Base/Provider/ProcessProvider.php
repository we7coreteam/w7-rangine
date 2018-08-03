<?php
/**
 * author: alex
 * date: 18-8-3 上午9:40
 */

namespace W7\Core\Base\Provider;


use W7\Core\Base\Process\ProcessBuilder;

class ProcessProvider
{
    /**
     * @param string $name
     * @param $server
     * @return bool|\Swoole\Process
     */
    public function trigger(string $name, $server)
    {
        


        if (!class_exists($name))
        {
            ilogger()->warning("Process is worng name is %s", $name);
            return false;
        }

        $process = iloader()->singleton($name);
        $checkInfo = call_user_func([$process, "check"]);
        if (!$checkInfo) {
            return false;
        }

       return ProcessBuilder::create($name, $server);
    }
}