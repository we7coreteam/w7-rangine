<?php
/**
 * @author donknap
 * @date 18-7-25 下午2:49
 */

namespace W7\Core\Config;

class Event {
	/**
	 * swoole 事件
	 */
	const ON_START = 'start';
	const ON_WORKER_START = 'workerStart';
	const ON_MANAGER_START = 'managerStart';
	const ON_REQUEST = 'request';
	const ON_TASK = 'task';
	const ON_FINISH = 'finish';
	const ON_PIPE_MESSAGE = 'pipeMessage';
	const ON_CONNECT = 'connect';
	const ON_RECEIVE = 'receive';
	const ON_CLOSE = 'close';

	/**
	 * 自定义事件
	 */
	const ON_USER_BEFORE_START = 'beforeStart';

}