<?php

namespace W7\Console\Command;

use W7\Core\Route\RouteMapping;

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
		$cmd = array_shift($options);
		if ($cmd === 'route') {
			iloader()->singleton(RouteMapping::class)->getMapping();
			$config = $this->parseRouteData(irouter()->getData());
		} else if ($cmd === 'server') {
			$config = iconfig()->getServer();
		} else {
			$config = iconfig()->getUserAppConfig($cmd);
		}

		return $this->getData($options, $config);
	}

	private function parseRouteData($data) {
		$routes = [];
		foreach ($data[0] as $method => $route) {
			foreach ($route as $key => $item) {
				$routeKey = implode('-', $item);
				if (empty($routes[$routeKey])) {
					$routes[$routeKey] = [
						'handle' => str_replace("W7\App\Controller\\", '', $item[0]),
						'action' => $item[1],
						'uri' => $key
					];
				}

				if (empty($routes[$routeKey]['methods'])) {
					$routes[$routeKey]['methods'] = '';
				}
				$routes[$routeKey]['methods'] .= $method . ' ';
			}
		}

		foreach ($data[1] as $method => $regexRoute) {
			foreach ($regexRoute as $route) {
				foreach ($route['routeMap'] as $item)
					$routes[implode('-', $item[0])]['params'] = implode(' ', array_values($item[1]));
			}
		}
		$routes = array_combine(array_column($routes, 'uri'), $routes);

		return $routes;
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