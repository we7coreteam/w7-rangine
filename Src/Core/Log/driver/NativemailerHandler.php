<?php
/**
 * @author donknap
 * @date 18-10-18 下午6:27
 */

namespace W7\Core\Log\driver;


use W7\Core\Log\HandlerInterface;

class NativemailerHandler implements HandlerInterface {
	public function getHanlder($config) {
		if (empty($config['to']) || empty($config['subject']) || empty($config['from'])) {
			return null;
		}
		return new \Monolog\Handler\NativeMailerHandler($config['to'], $config['subject'], $config['from'], $config['level']);
	}
}