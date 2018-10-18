<?php
/**
 * @author donknap
 * @date 18-10-18 下午3:40
 */

namespace W7\Core\Log;

use Monolog\Logger as MonoLogger;

class LogManager {
	private $channel = [];
	private $handler;
	private $logLevel = [
		'debug' => MonoLogger::DEBUG,
		'info' => MonoLogger::INFO,
		'notice' => MonoLogger::NOTICE,
		'warning' => MonoLogger::WARNING,
		'error'  => MonoLogger::ERROR,
		'critical' => MonoLogger::CRITICAL,
		'alert' => MonoLogger::ALERT,
		'emergency' => MonoLogger::EMERGENCY,
	];
	private $config;

	public function __construct() {
		$this->init();
	}

	/**
	 * 初始化通道
	 */
	public function init() {
		$config = $this->getConfig();
		if (empty($config['channel'])) {
			throw new \RuntimeException('Invalid log config');
		}
		$this->initChannel($config['channel']);
	}

	public function getDefaultChannel() {
		if (empty($this->config['default'])) {
			throw new \RuntimeException('It is not set default logger');
		}
		return $this->channel[$this->config['default']]['logger'];
	}

	public function getChannel($name) {
		if (isset($this->channel[$name]) && $this->channel[$name]['logger'] instanceof MonoLogger) {
			return $this->channel[$name]['logger'];
		} else {
			throw new \RuntimeException('It is not set ' . $name . ' log handler');
		}
	}

	private function initChannel($channelConfig) {
		$stack = [];
		//先初始化单个通道日志对象，保存起来Handler，然后处理多种通道时直接使用
		foreach ($channelConfig as $name => $channel) {
			if (empty($channel['driver'])) {
				continue;
			}
			if ($channel['driver'] == 'stack') {
				$stack[$name] = $channel;
			} else {
				$handlerClass = sprintf("\\W7\\Core\\Log\\driver\\%sHandler", ucfirst($channel['driver']));
				$handler = (new $handlerClass())->getHanlder($channel);

				if (!is_null($handler)) {
					$logger = $this->getLogger($name);
					$logger->pushHandler($handler);
				}

				$this->channel[$name]['handler'] = $handler;
				$this->channel[$name]['logger'] = $logger;
			}
		}

		if (!empty($stack)) {
			foreach ($stack as $name => $setting) {
				$logger = $this->getLogger($name);

				if (is_array($setting['channel'])) {
					foreach ($setting['channel'] as $channel) {
						if (!empty($this->channel[$channel])) {
							$logger->pushHandler($this->channel[$channel]['handler']);
						}
					}
				} else {
					$logger->pushHandler($this->channel[$setting['channel']]['handler']);
				}
				$this->channel[$name]['logger'] = $logger;
			}
		}
		return true;
	}

	private function getConfig() {
		if (!empty($this->config)) {
			return $this->config;
		}
		$this->config = iconfig()->getUserConfig('log');
		if (!empty($this->config['channel'])) {
			foreach ($this->config['channel'] as $name => &$setting) {
				if (!empty($setting['level'])) {
					$setting['level'] = $this->logLevel[$setting['level']];
				}
			}
		}
		return $this->config;
	}

	private function getLogger($name) {
		return new Logger($name);
	}
}