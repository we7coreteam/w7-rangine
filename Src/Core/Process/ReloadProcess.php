<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:03
 */

namespace W7\Core\Process;

use W7\App;
use W7\Core\Base\ProcessInterface;
use W7\Core\Base\Reload;

class ReloadProcess implements ProcessInterface {
    public function check() {
        $serverConfig = iconfig()->getServer();
        if (!$serverConfig['autoReload'])
        {
            return true;
        }
        return true;
    }

    public function run()
    {
        $pname = App::$server->getPname();
        $processName = sprintf('%s reload process', $pname);
        /**
         * @var Reload $reload
         */
        $reload = iloader()->singleton(Reload::class);
        $reload->run();
    }
}