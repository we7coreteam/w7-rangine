<?php

namespace W7\Console\Command;

class ConfigCommand extends CommandAbstract {
	public function dispatch($action, $options = []) {
		ioutputer()->writeList($this->formatData($this->getConfig($action), $action));
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
		$commandList = [
			'Arguments:' => [
				'route' => 'app route config',
				'server' => 'app server config',
				'database' => [
					'app database config',
					':default' => 'app database default config'
				],
				'cache' => [
					'app cache config',
					':default' => 'app cache default config'
				]
			],
			'Options:' => [
				'-h, --help' => 'Display help information',
				'-v, --version' => 'Display version information'
			]
		];
		ioutputer()->writeList($commandList);
	}
}