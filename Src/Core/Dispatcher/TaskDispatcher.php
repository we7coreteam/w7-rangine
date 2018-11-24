<?php
/**
 * author: alex
 * date: 18-8-3 上午9:38
 */

namespace W7\Core\Dispatcher;

use W7\App;
use W7\Core\Exception\TaskException;

/**
 * 派发任务的时候，需要先注册任务，然后在OnTask事件中具体调用
 * Class TaskDispatcher
 * @package W7\Core\Helper\Dispather
 */
class TaskDispatcher extends DispatcherAbstract {

	/**
	 * 注册一个异步任务
	 * @param string $taskName
	 * @param string $methodName
	 * @param array $params
	 * @param string $type
	 * @param int $timeout
	 * @return false|int
	 * @throws TaskException
	 */
	public function register(...$params) {
		$taskName = $params[0];
		$taskMethod = $params[1];
		$taskParams = !empty($params[2]) ? $params[2] : [];

		$data = $this->pack($taskName, $taskMethod, $taskParams);

		if (!isWorkerStatus()) {
			throw new TaskException('Please deliver task by http!');
		}

		if (!class_exists($taskName)) {
			throw new TaskException('Task ' . $taskName . ' not found');
		}
		return App::$server->getServer()->task($data);
	}

	/**
	 * 注册一个协程任务
	 */
	public function registerCo(...$params) {

	}


	/**
	 * 在OnTask事件中执行具体任务
	 * @param mixed ...$params
	 * @return bool|mixed|void
	 */
	public function dispatch(...$params) {
		$taskData = unserialize($params[0]);
		$taskId = $params[1];
		$workId = $params[2];

		$name = $taskData['name'];
		$type = $taskData['type'] ?? '';
		$method = $taskData['method'] ?? 'run';
		$params = $taskData['params'] ?? [];

		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $workId);
		$context->setContextDataByKey('coid', $taskId);

		if (!class_exists($name)) {
			$name = "W7\\App\\Task\\". ucfirst($name);
		}

		if (!class_exists($name)) {
			ilogger()->warning("task name is wrong name is " . $name);
			return false;
		}

		$task = iloader()->singleton($name);
		$result = call_user_func_array([$task, $method], [$params]);
		return $result;
	}

	/**
	 * @param string $taskName
	 * @param string $methodName
	 * @param array  $params
	 * @param string $type
	 *
	 * @return string
	 */
	private function pack(string $taskName, string $methodName, array $params) {
		$task = [
			'name'   => $taskName,
			'method' => $methodName,
			'params' => $params,
		];
		return serialize($task);
	}

	public function unpack() {
	}
}
