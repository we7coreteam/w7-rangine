<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Log;

use Monolog\Logger as MonoLogger;
use Psr\Log\LoggerInterface;
use W7\Core\Log\Handler\HandlerAbstract;
use W7\Core\Log\Processor\SwooleProcessor;

/**
 * Class LogFactory
 * @package W7\Core\Log
 *
 * @method void emergency(string $message, array $context = [])
 * @method void alert(string $message, array $context = [])
 * @method void critical(string $message, array $context = [])
 * @method void error(string $message, array $context = [])
 * @method void warning(string $message, array $context = [])
 * @method void notice(string $message, array $context = [])
 * @method void info(string $message, array $context = [])
 * @method void debug(string $message, array $context = [])
 * @method void log($level, string $message, array $context = [])
 */
class LoggerFactory {
	protected $channelsConfig;
	protected $defaultChannel;
	protected $loggerMap = [];

	public function __construct($channelsConfig = [], $defaultChannel = 'stack') {
		$this->channelsConfig = $channelsConfig;
		$this->defaultChannel = $defaultChannel;
	}

	public function setDefaultChannel(string $channel) {
		$this->defaultChannel = $channel;
	}

	public function createLogger($channel, array $config) {
		$logger = new Logger($channel, [], []);
		$logger->bufferLimit = $config['buffer_limit'] ?? 1;

		$handler = $config['driver'];
		$handlers = [];
		if ($handler != 'stack') {
			/**
			 * @var HandlerAbstract $handler
			 */
			$handlers[] = new LogBuffer($handler::getHandler($config), $logger->bufferLimit, $config['level'], true, true);
		} else {
			$config['channel'] = (array)$config['channel'];
			foreach ($config['channel'] as $childChannel) {
				/**
				 * @var Logger $channelLogger
				 */
				$channelLogger = $this->getLogger($childChannel);
				$handlers = array_merge($handlers, $channelLogger->getHandlers());
			}
		}
		foreach ($handlers as $handler) {
			$logger->pushHandler($handler);
		}

		$config['processor'] = (array)(empty($config['processor']) ? [SwooleProcessor::class] : $config['processor']);
		foreach ($config['processor'] as $processor) {
			$logger->pushProcessor(new $processor);
		}

		return $logger;
	}

	public function registerLogger($channel, LoggerInterface $logger) {
		$this->loggerMap[$channel] = $logger;
	}

	/**
	 * 需调整
	 * @param string $channel
	 * @return LoggerInterface
	 */
	public function channel($channel = 'stack') : LoggerInterface {
		return $this->getLogger($channel);
	}

	protected function getLogger($channel) : LoggerInterface {
		if (empty($this->loggerMap[$channel]) && !empty($this->channelsConfig[$channel])) {
			$logger = $this->createLogger($channel, $this->channelsConfig[$channel]);
			$this->registerLogger($channel, $logger);
		}
		if (empty($this->loggerMap[$channel])) {
			$channel = $this->defaultChannel;
		}

		if (!empty($this->loggerMap[$channel]) && $this->loggerMap[$channel] instanceof MonoLogger) {
			return $this->loggerMap[$channel];
		}

		throw new \RuntimeException('logger channel ' . $channel . ' not support');
	}

	public function __call($name, $arguments) {
		return $this->channel()->$name(...$arguments);
	}
}
