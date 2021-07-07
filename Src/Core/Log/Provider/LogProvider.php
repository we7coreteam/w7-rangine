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

use ErrorException;
use Monolog\Logger as MonoLogger;
use W7\App;
use W7\Contract\Logger\LoggerFactoryInterface;
use W7\Core\Log\LoggerFactory;
use W7\Core\Log\Processor\SwooleProcessor;
use W7\Core\Provider\ProviderAbstract;

class LogProvider extends ProviderAbstract {
	public function register() {
		$this->clearLog();

		$this->container->set(LoggerFactoryInterface::class, function () {
			$config = $this->config->get('log', []);
			$config['channel'] = $config['channel'] ?? [];
			foreach ($config['channel'] as $name => &$setting) {
				if (!empty($setting['level'])) {
					$setting['level'] = MonoLogger::toMonologLevel($setting['level']);
				}

				$setting['driver'] = $setting['driver'] ?? 'daily';
				$setting['driver'] = $this->config->get('handler.log.' . $setting['driver'], $setting['driver']);

				$setting['processor'] = $setting['processor'] ?? [];
				array_unshift($setting['processor'], SwooleProcessor::class);
			}

			return new LoggerFactory($config['channel'], $config['default'] ?? 'stack');
		});
	}

	private function clearLog() {
		if ((ENV & CLEAR_LOG) !== CLEAR_LOG) {
			return false;
		}
		$logPath = App::getApp()->getRuntimePath() . '/logs/*';
		$tree = glob($logPath);
		if (!empty($tree)) {
			foreach ($tree as $file) {
				if (strstr($file, '.log') !== false) {
					try {
						unlink($file);
					} catch (ErrorException $e) {
						//Ignore the exception
					}
				}
			}
		}
	}

	public function providers(): array {
		return [LoggerFactoryInterface::class];
	}
}
