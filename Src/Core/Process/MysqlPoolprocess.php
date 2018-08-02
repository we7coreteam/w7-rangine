<?php
/**
 * author: alex
 * date: 18-8-1 下午6:22
 */

namespace W7\Core\Process;


use Swoole\Process;
use W7\Core\Base\Process\ProcessInterface;

class MysqlPoolprocess implements ProcessInterface
{

    protected $shmKey  = '30';

    
    public function check()
    {
        $serverConfig = iconfig()->getServer();
        if (!$serverConfig['common']['autoReload']) {
            return true;
        }
        return true;
    }

    public function run(Process $process)
    {
    }
}