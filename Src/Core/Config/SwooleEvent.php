<?php
/**
 * @author donknap
 * @date 18-7-25 下午2:49
 */

namespace W7\Core\Config;

class SwooleEvent {
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
	const ON_BEFORE_START = 'beforeStart';
	const ON_OPEN = 'open';
	const ON_HAND_SHAKE = 'handshake';
	const ON_MESSAGE = 'message';
}