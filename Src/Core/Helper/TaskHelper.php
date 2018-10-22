<?php
/**
 * author: alex
 * date: 18-8-3 上午9:59
 */

namespace W7\Core\Helper;

use W7\App;

class TaskHelper
{
	/**
	 * @param string $taskName
	 * @param string $methodName
	 * @param array  $params
	 * @param string $type
	 *
	 * @return string
	 */
	public function pack(string $taskName, string $methodName, array $params): string
	{
		$task = [
			'name'   => $taskName,
			'method' => $methodName,
			'params' => $params,
		];

		$contextObj = App::getApp()->getContext();
		$task['logid']  = $contextObj->getLogid();
		$task['spanid'] = $contextObj->getSpanid();

		return serialize($task);
	}

	public function unpack()
	{
	}
}
