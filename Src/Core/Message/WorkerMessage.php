<?php
/**
 * @author donknap
 * @date 18-11-24 下午9:40
 */

namespace W7\Core\Message;


/**
 * 用于 $server->sendMessage() 中的消息结构
 * @package W7\Core\Message
 */
class WorkerMessage extends MessageAbstract {
	/**
	 * 发起异步任务
	 */
	const OPERATION_TASK_ASYNC = '1';
	/**
	 * 发起协程任务
	 */
	const OPERATION_TASK_CO = '2';

	/**
	 * 要进行的操作，根据类中常量赋值
	 */
	public $operation;

	/**
	 * @var mixed 发起任务操作时，此为任务名
	 */
	public $data;

	/**
	 * 一些附加值
	 * @var array
	 */
	public $extra = [
		'callback' => [], //回调函数, [类名, 方法名]
	];

	public function __construct($data = []) {
		if (empty($data)) {
			return true;
		}

		$this->operation = $data['operation'];
		$this->data = $data['data'];
		$this->extra = $data['extra'];

	}

	public function pack() {
		$data = [
			'operation' => $this->operation,
			'data' => $this->data,
			'extra' => $this->extra,
		];

		return serialize($data);
	}

	public function isTaskAsync() {
		if ($this->operation == self::OPERATION_TASK_ASYNC) {
			return true;
		} else {
			return false;
		}
	}

	public function isTaskCo() {
		if ($this->operation == self::OPERATION_TASK_CO) {
			return true;
		} else {
			return false;
		}
	}
}