<?php

namespace W7\Console\Command\Route;

use FastRoute\Dispatcher\GroupCountBased;
use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Route\Route;
use W7\Core\Route\RouteMapping;

class ListCommand extends CommandAbstract {
	protected function configure() {
		$this->addOption('--search', '-s', InputOption::VALUE_REQUIRED, '需要搜索的路由uri');
	}

	protected function handle($options) {
		iloader()->singleton(RouteMapping::class)->getMapping();
		$config = irouter()->getData();

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
		$this->output->writeTable($header, $routes);
	}

	private function parseRouteItem(&$routes, $item, $method) {
		$routeKey = implode('-', $item['handler']);
		if (empty($routes[$routeKey])) {
			$middleware = '';
			array_walk_recursive($item['middleware'],  function ($data) use (&$middleware) {
				$middleware .= str_replace("W7\\App\\Middleware\\", ' ', $data) . "\n";
			});
			$routes[$routeKey] = [
				'name' => $item['handler']['name'] ?? '',
				'uri' => $item['uri'],
				'handle' => str_replace("W7\App\Controller\\", '', $item['handler'][0]) . '@' . $item['handler'][1],
				'middleware' => rtrim($middleware, "\n")
			];
		}

		if (empty($routes[$routeKey]['methods'])) {
			$routes[$routeKey]['methods'] = '';
		}
		$routes[$routeKey]['methods'] .= $method . ' ';
	}

	private function parseRouteData($data) {
		$routes = [];
		foreach ($data[0] as $method => $route) {
			foreach ($route as $key => $item) {
				$this->parseRouteItem($routes, $item, $method);
			}
		}

		return $routes;
	}
}