<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:18
 */

namespace W7\Core\Listener;

use Swoole\Http\Server;
use W7\App\Listener\TaskFinishListener;
use W7\Core\Base\ListenerInterface;
use W7\Core\Config\Event;

class FinishListener implements ListenerInterface
{
    public function run(Server $server, int $task_id, string $data)
    {
        ievent(Event::ON_USER_TASK_FINISH, [$data]);
    }
}
