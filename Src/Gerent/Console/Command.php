<?php
/**
 * @author donknap
 * @date 18-11-6 下午2:31
 */

namespace W7\Gerent\Console;

class Command {
	public function run($option) {
		$action = $option['action'];
		if (!$action) {
			return $this->defaultCommand();
		}
		return $this->formatData($this->getConfig($action), $action);
	}

	private function getConfig($option) {
		$options = explode(':', $option);
		if ($options[0] === 'route') {
			$config = iconfig()->getRouteConfig();
		} else if ($options[0] === 'server') {
			$config = iconfig()->getServer();
		} else {
			$config = iconfig()->getUserAppConfig($options[0]);
		}

		array_shift($options);

		return $this->getData($options, $config);
	}

	private function getData($options, $config) {
		foreach ($options as $item) {
			if (empty($config[$item])) {
				return [];
			}
			$config = $config[$item];
		}
		return $config;
	}

	private function formatData($data, $action) {
		return [
			'your app ' . $action. ' config:' => $data
		];
	}

	private function defaultCommand() {
		return  [
			'Arguments:' => [
				'route' => 'app route config',
				'database' => [
					'app database config',
					':default' => 'app database default config'
				],
				'cache' => [
					'app cache config',
					':default' => 'app cache default config'
				]
			]
		];
	}
}