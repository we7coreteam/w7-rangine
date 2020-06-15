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

namespace W7\Core\Log\Provider;

use Monolog\Logger as MonoLogger;
use W7\Core\Facades\Event;
use W7\Core\Log\Handler\HandlerAbstract;
use W7\Core\Log\LogBuffer;
use W7\Core\Log\Logger;
use W7\Core\Log\Processor\SwooleProcessor;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Server\ServerEvent;

class LogProvider extends ProviderAbstract {
	protected $logConfig = [];
	protected $logProcessors = [];
	protected $logChannel = [];

	public function register() {
		$config = $this->config->get('log', []);
		$config['channel'] = $config['channel'] ?? [];
		foreach ($config['channel'] as $name => &$setting) {
			if (!empty($setting['level'])) {
				$setting['level'] = MonoLogger::toMonologLevel($setting['level']);
			}
		}
		$this->logConfig = $config;
		$this->logProcessors = [new SwooleProcessor()];

		$this->registerLogChannel();

		$this->logProcessors = null;
		$this->logChannel = null;
		$this->logConfig = [];

		Event::listen(ServerEvent::ON_USER_AFTER_START, function () {
			if ((ENV & CLEAR_LOG) !== CLEAR_LOG) {
				return false;
			}
			$logPath = RUNTIME_PATH . DS. 'logs/*';
			$tree = glob($logPath);
			if (!empty($tree)) {
				foreach ($tree as $file) {
					if (strstr($file, '.log') !== false) {
						unlink($file);
					}
				}
			}
		});
	}

	private function registerLogChannel() {
		$stack = [];
		//先初始化单个通道，记录下相关的Handler，再初始化复合通道
		foreach ($this->logConfig['channel'] as $name => $channel) {
			if (empty($channel['driver'])) {
				continue;
			}
			if ($channel['driver'] == 'stack') {
				$stack[$name] = $channel;
			} else {
				$this->addChannel($name, $channel['driver'], $channel);
			}
		}

		if (!empty($stack)) {
			foreach ($stack as $name => $setting) {
				$logger = $this->getLoggerInstanceByChannel($name);

				if (is_array($setting['channel'])) {
					foreach ($setting['channel'] as $channel) {
						if (!empty($this->logChannel[$channel]) && !is_null($this->logChannel[$channel]['handler'])) {
							$logger->pushHandler($this->logChannel[$channel]['handler']);
						}
					}
				} else {
					if (!is_null($this->logChannel[$setting['channel']]['handler'])) {
						$logger->pushHandler($this->logChannel[$setting['channel']]['handler']);
					}
				}

				$this->container->set('logger-' . $name, $logger);
			}
		}
	}

	private function addChannel($name, $driver, $options = []) {
		$this->logConfig['channel'][$name] = array_merge($options, ['driver' => $driver]);

		/**
		 * @var HandlerAbstract $handlerClass
		 */
		$handlerClass = $this->config->get('handler.log.' . $driver, '');
		if (!$handlerClass || !class_exists($handlerClass)) {
			throw new \RuntimeException('log handler ' . $driver . ' is not support');
		}

		$bufferLimit = $options['buffer_limit'] ?? 1;
		$handler = new LogBuffer($handlerClass::getHandler($options), $bufferLimit, $options['level'], true, true);

		$logger = null;
		if (!is_null($handler)) {
			$logger = $this->getLoggerInstanceByChannel($name);

			$logger->pushHandler($handler);

			$this->logChannel[$name]['handler'] = $handler;
			$this->container->set('logger-' . $name, $logger);
		}
	}

	private function getLoggerInstanceByChannel($channel) {
		$logger = new Logger($channel, [], []);
		$logger->bufferLimit = $this->logConfig['channel'][$channel]['buffer_limit'] ?? 1;

		foreach ($this->logProcessors as $processor) {
			$logger->pushProcessor($processor);
		}

		return $logger;
	}
}
