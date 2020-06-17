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
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Server\ServerEvent;

class LogProvider extends ProviderAbstract {
	public function register() {
		$config = $this->config->get('log', []);
		$config['channel'] = $config['channel'] ?? [];
		foreach ($config['channel'] as $name => &$setting) {
			if (!empty($setting['level'])) {
				$setting['level'] = MonoLogger::toMonologLevel($setting['level']);
			}
		}

		$this->registerLoggers($config);

		//如果env中包含CLEAR_LOG，启动后先执行清空日志
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

	private function registerLoggers($config) {
		$stack = [];
		//先初始化单个通道，记录下相关的Handler，再初始化复合通道
		foreach ($config['channel'] as $name => $channel) {
			if (empty($channel['driver'])) {
				continue;
			}
			if ($channel['driver'] == 'stack') {
				$stack[$name] = $channel;
			} else {
				$this->registerLogger($name, $channel['driver'], $channel);
			}
		}

		if (!empty($stack)) {
			foreach ($stack as $name => $setting) {
				$this->registerLogger($name, null, $setting, true);
			}
		}
	}
}
