<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;
use W7\Core\Base\ListenerInterface;

class ManagerStart implements ListenerInterface
{
    public function run(Server $server)
    {
    }
}
