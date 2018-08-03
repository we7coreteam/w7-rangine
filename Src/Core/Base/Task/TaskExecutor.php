<?php
/**
 * author: alex
 * date: 18-8-3 上午10:34
 */

namespace W7\Core\Base\Task;


use W7\App\Task\Test;
use W7\Core\Helper\Log\LogHelper;

class TaskExecutor
{
    public function run($taskData)
    {
        $task = null;
        $taskData = unserialize($taskData);

        $name   = $taskData['name'];
        $type   = $taskData['type'];
        $method = $taskData['method'];
        $params = $taskData['params'];
        $logid  = $taskData['logid'] ?? uniqid('', true);
        $spanid = $taskData['spanid'] ?? 0;
        $nameSpacePrefix = 'W7\App\Task';

        if (class_exists($name))
        {
            $task = iloader()->singleton($name);
        }

        if (class_exists( $nameSpacePrefix . "\\". ucfirst($name)))
        {

            $task = iloader()->singleton($nameSpacePrefix . "\\" . ucfirst($name));
        }
        if (empty($task))
        {
            ilogger()->warning("task name is wrong name is " . $name);
            return false;
        }


        $result = $this->runSyncTask($task, $method, $params, $logid, $spanid, $name, $type);

        return $result;
    }

    /**
     * @param object $task
     * @param string $method
     * @param array  $params
     * @param string $logid
     * @param int    $spanid
     * @param string $name
     * @param string $type
     *
     * @return mixed
     */
    private function runSyncTask($task, string $method, array $params, string $logid, int $spanid, string $name, string $type)
    {
        $this->beforeTask($logid, $spanid, $name, $method);
        $result = call_user_func_array([$task, $method], $params);
        $this->afterTask();

        return $result;
    }

    private function beforeTask($logid, $spanid, $name, $method)
    {

        /**
         * @var LogHelper $logerHelper
         */
        $logerHelper = iloader()->singleton(LogHelper::class);
        $logerHelper->beforeTask($logid, $spanid, $name, $method);
    }

    protected function afterTask()
    {

    }
}