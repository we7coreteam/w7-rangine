<?php
/**
 * @author donknap
 * @date 18-10-18 下午4:25
 */

namespace W7\Core\Log\driver;

use W7\Core\Log\HandlerInterface;

class StreamHandler implements HandlerInterface {
	public function getHanlder($config) {
		return new \Monolog\Handler\StreamHandler($config['path'], $config['level']);
	}
}