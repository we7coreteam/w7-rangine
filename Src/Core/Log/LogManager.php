<?php
/**
 * @author donknap
 * @date 18-10-18 下午3:40
 */

namespace W7\Core\Log;

use Monolog\Logger as MonoLogger;
use Monolog\Processor\IntrospectionProcessor;
use W7\Core\Log\Processor\SwooleProcessor;

class LogManager {
	private $channel = [];
	private $config;
	private $commonProcessor;
	private $commonHandler;

	public function __construct() {
		$this->config = $this->getConfig();
		$development = iconfig()->getUserAppConfig('setting')['development'];
		//如果是开发模式，每次启动清理日志文件
		if (!empty($development) && $development == '2') {
			$this->cleanLogFile();
		}
		if (empty($this->config['channel'])) {
			throw new \RuntimeException('Invalid log config');
		}
		//初始化全局附加的Handler, Processor, Formatter
		$this->commonProcessor = $this->initCommonProcessor();

		$this->initChannel();
	}

	public function getDefaultChannel() {
		if (empty($this->config['default'])) {
			throw new \RuntimeException('It is not set default logger');
		}
		return $this->getChannel($this->config['default']);
	}

	public function getChannel($name) {
		if (isset($this->channel[$name]) && $this->channel[$name]['logger'] instanceof MonoLogger) {
			return $this->channel[$name]['logger'];
		} else {
			throw new \RuntimeException('It is not set ' . $name . ' log handler');
		}
	}

	/**
	 * 初始化通道，
	 * @param $channelConfig
	 * @return bool
	 */
	private function initChannel() {
		$stack = [];
		$channelConfig = $this->config['channel'];

		//先初始化单个通道，记录下相关的Handler，再初始化复合通道
		foreach ($channelConfig as $name => $channel) {
			if (empty($channel['driver'])) {
				continue;
			}
			if ($channel['driver'] == 'stack') {
				$stack[$name] = $channel;
			} else {
				$handlerClass = sprintf("\\W7\\Core\\Log\\Driver\\%sHandler", ucfirst($channel['driver']));
				$handler = (new $handlerClass())->getHandler($channel);

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
						if (!empty($this->channel[$channel]) && !is_null($this->channel[$channel]['handler'])) {
							$logger->pushHandler($this->channel[$channel]['handler']);
						}
					}
				} else {
					if (!is_null($this->channel[$channel]['handler'])) {
						$logger->pushHandler($this->channel[$setting['channel']]['handler']);
					}
				}
				$this->channel[$name]['logger'] = $logger;
			}
		}
		return true;
	}

	private function initCommonProcessor() {
		$swooleProcessor = iloader()->singleton(SwooleProcessor::class);
		$introProcessor = iloader()->singleton(IntrospectionProcessor::class);
		return [
			$swooleProcessor,
			$introProcessor
		];
	}

	private function getConfig() {
		$config = iconfig()->getUserConfig('log');
		if (!empty($this->config['channel'])) {
			foreach ($this->config['channel'] as $name => &$setting) {
				if (!empty($setting['level'])) {
					$setting['level'] = MonoLogger::toMonologLevel($setting['level']);
				}
			}
		}
		return $config;
	}

	private function getLogger($name) {
		$logger = new Logger($name, [], []);
		if (!empty($this->commonProcessor)) {
			foreach ($this->commonProcessor as $processor) {
				$logger->pushProcessor($processor);
			}
		}
		return $logger;
	}

	private function cleanLogFile() {
		$logPath = RUNTIME_PATH . DS. 'logs/*';
		$tree = glob($logPath);
		if (!empty($tree)) {
			foreach ($tree as $file) {
				if (strstr($file, '.log') !== false) {
					unlink($file);
				}
			}
		}
		return true;
	}
}