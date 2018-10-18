<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:15
 */

namespace W7\Core\Log\driver;


use Monolog\Handler\RotatingFileHandler;
use W7\Core\Log\HandlerInterface;

class DailyHandler implements HandlerInterface {
	public function getHanlder($config) {
		return new RotatingFileHandler($config['path'], $config['days'], $config['level']);
	}
}