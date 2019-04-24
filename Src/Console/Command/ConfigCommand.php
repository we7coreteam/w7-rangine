<?php

namespace W7\Console\Command;

class ConfigCommand implements CommandInterface {
	public function run($action, $options = []) {
		return $this->formatData($this->getConfig($action), $action);
	}

	private function formatData($data, $action) {
		return [
			'your app ' . $action. ' config:' => $data
		];
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

	public function help() {
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