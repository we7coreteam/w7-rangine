<?php
/**
 * author: alex
 * date: 18-8-3 上午9:40
 */

namespace W7\Core\Base\Provider;

use W7\App;
use W7\Core\Exception\TaskException;
use W7\Core\Helper\TaskHelper;

class TaskProvider
{
    /**
     * @param string $taskName
     * @param string $methodName
     * @param array $params
     * @param string $type
     * @param int $timeout
     * @return false|int
     * @throws TaskException
     */
    public function trigger(string $taskName, string $methodName, array $params = [], string $type = self::TYPE_CO, $timeout = 3)
    {

        /**
         * @var TaskHelper $taskHelper
         */
        $taskHelper = iloader()->singleton(TaskHelper::class);
        $data   = $taskHelper->pack($taskName, $methodName, $params, $type);

        if (!isWorkerStatus()) {
            throw new TaskException('Please deliver task by http!');
        }


        // Deliver async task
        return App::$server->getServer()->task($data);
    }
}
