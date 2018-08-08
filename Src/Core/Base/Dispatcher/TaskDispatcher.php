<?php
/**
 * author: alex
 * date: 18-8-3 上午9:38
 */

namespace W7\Core\Base\Dispatcher;

use W7\App\App;
use W7\Core\Exception\TaskException;
use W7\Core\Helper\Log\LogHelper;
use W7\Core\Helper\TaskHelper;

/**
 * Class TaskDispatcher
 * @package W7\Core\Helper\Dispather
 */
class TaskDispatcher extends DispatcherFacade
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
	public function register(...$param)
	{
		$taskName = $param[0];
		$methodName = $param[1];
		$params = $param[2];
		/**
		 * @var TaskHelper $taskHelper
		 */
		$taskHelper = iloader()->singleton(TaskHelper::class);
		$data   = $taskHelper->pack($taskName, $methodName, $params);

		if (!isWorkerStatus()) {
			throw new TaskException('Please deliver task by http!');
		}
		// Deliver async task
		return App::$server->getServer()->task($data);
	}


	public function run(...$param)
	{
		$taskData = $param[0];
		$task = null;
		$taskData = unserialize($taskData);

		$name   = $taskData['name'];
		$type   = $taskData['type'];
		$method = $taskData['method'];
		$params = $taskData['params'];
		$logid  = $taskData['logid'] ?? uniqid('', true);
		$spanid = $taskData['spanid'] ?? 0;
		$nameSpacePrefix = 'W7\App\Task';

		if (class_exists($name)) {
			$task = iloader()->singleton($name);
		}

		if (class_exists($nameSpacePrefix . "\\". ucfirst($name))) {
			$task = iloader()->singleton($nameSpacePrefix . "\\" . ucfirst($name));
		}
		if (empty($task)) {
			ilogger()->warning("task name is wrong name is " . $name);
			return false;
		}


		$result = $this->runSyncTask($task, $method, $params, $logid, $spanid, $name);

		return $result;
	}

	/**
	 * @param object $task
	 * @param string $method
	 * @param array  $params
	 * @param string $logid
	 * @param int	$spanid
	 * @param string $name
	 * @param string $type
	 *
	 * @return mixed
	 */
	private function runSyncTask($task, string $method, array $params, string $logid, int $spanid, string $name)
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
