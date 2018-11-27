<?php
/**
 * @author donknap
 * @date 18-11-26 下午6:56
 */

namespace W7\Core\Message;


class Message {
	const MESSAGE_TYPE_TASK = 'task';
	const MESSAGE_TYPE_CRONTAB = 'crontab';

	static public function unpack($data) {
		$dataTmp = unserialize($data);
		if (empty($dataTmp['class'])) {
			throw new \RuntimeException('Invalid message structure');
		}

		$message = new $dataTmp['class']($dataTmp);
		if (empty($dataTmp) || !is_array($dataTmp)) {
			throw new \RuntimeException('Invalid message structure');
		}
		foreach ($dataTmp as $name => $value) {
			$message->$name = $value;
		}
		return $message;
	}
}