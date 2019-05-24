<?php

namespace W7\Console\Command\Route;

use FastRoute\Dispatcher\GroupCountBased;
use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Route\Route;
use W7\Core\Route\RouteMapping;

class ListCommand extends CommandAbstract {
	protected function configure() {
		$this->addOption('--search', '-s', InputOption::VALUE_REQUIRED, 'the routing uri to search for');
		$this->setDescription('get routing information');
	}

	protected function handle($options) {
		$config = iloader()->singleton(RouteMapping::class)->getMapping();

		$routes = [];
		$key = $options['search'] ?? '';
		if (!$key) {
			$routes = $this->parseRouteData($config);
		} else {
			$dispatch = new GroupCountBased($config);
			foreach (Route::METHOD_ALL as $method) {
				$result = $dispatch->dispatch($method, $key);
				if (!empty($result[1]['handler'])) {
					$this->parseRouteItem($routes, $result[1], $method);
				}
			}
		}

		$header = ['name', 'uri', 'handle', 'middleware', 'methods'];
		$this->output->table($header, $routes);
	}

	private function parseRouteItem(&$routes, $item, $method) {
		if ($item['handler'] instanceof \Closure) {
			$item['handler'] = 'closure';
			$routeKey = $item['uri'] . ':Closure';
		} else {
			$routeKey = implode('-', $item['handler']);
			$item['handler'] = str_replace("W7\App\Controller\\", '', $item['handler'][0]) . '@' . $item['handler'][1];
		}

		if (empty($routes[$routeKey])) {
			$middleware = '';
			array_walk_recursive($item['middleware'],  function ($data) use (&$middleware) {
				$middleware .= str_replace("W7\\App\\Middleware\\", ' ', $data) . "\n";
			});
			$routes[$routeKey] = [
				'name' => $item['name'] ?? '',
				'uri' => $item['uri'],
				'handle' => $item['handler'],
				'middleware' => rtrim($middleware, "\n")
			];
		}

		if (empty($routes[$routeKey]['methods'])) {
			$routes[$routeKey]['methods'] = '';
		}
		if (strpos($routes[$routeKey]['methods'], $method) === false) {
			$routes[$routeKey]['methods'] .= $method . ' ';
		}
	}

	private function parseRouteData($data) {
		$routes = [];
		foreach ($data[0] as $method => $route) {
			foreach ($route as $key => $item) {
				$this->parseRouteItem($routes, $item, $method);
			}
		}

		foreach ($data[1] as $method => $routeGroup) {
			foreach ($routeGroup as $route) {
				foreach ($route['routeMap'] as $item) {
					$item = $item[0];
					$this->parseRouteItem($routes, $item, $method);
				}
			}
		}

		uasort($routes, function ($item1, $item2) {
			if($item1['uri']<$item2['uri']){
				return -1;
			}else if($item1['uri']>$item2['uri']){
				return 1;
			}else{
				return 0;
			}
		});
		return $routes;
	}
}