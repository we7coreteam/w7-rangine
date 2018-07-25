<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:03
 */

namespace W7\Core\Process;

use Swoft\Process\Bootstrap\Reload;
use W7\App;
use W7\Core\Base\Process;
use W7\Core\Base\ProcessInterface;

class ReloadProcess implements ProcessInterface {
	public function check() {
	    $serverConfig = iconfig()->getServer();
	    if (!$serverConfig['autoReload'])
	    {
	        return false;
        }
        return true;
	}

	public function run(Process $process)
    {
        $pname = App::$server->getPname();
        $processName = sprintf('%s reload process', $pname);
        $process->name($processName);
        $reload = new Reload();
        $reload->run();
	}
}