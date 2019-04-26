<?php

namespace W7\Console\Command\Route;

use FastRoute\Dispatcher\GroupCountBased;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use W7\Core\Route\Route;
use W7\Core\Route\RouteMapping;

class ListCommand extends Command {
	protected function configure() {
		$this->addOption('--search', null, InputOption::VALUE_REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		iloader()->singleton(RouteMapping::class)->getMapping();
		$config = irouter()->getData();

		$routes = [];
		$key = $input->getOption('search');
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

		$table = new Table($output);
		$table->setHeaders([ 'uri', 'handle','params', 'middleware', 'methods']);
		$table->setRows($routes);
		$table->render();
	}

	private function parseRouteItem(&$routes, $item, $method) {
		$routeKey = implode('-', $item['handler']);
		if (empty($routes[$routeKey])) {
			$middleware = '';
			array_walk_recursive($item['middleware'],  function ($data) use (&$middleware) {
				$middleware .= $data . ' ';
			});
			$routes[$routeKey] = [
				'uri' => $item['uri'],
				'handle' => str_replace("W7\App\Controller\\", '', $item['handler'][0]) . '@' . $item['handler'][1],
				'params' => '',
				'middleware' => $middleware
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

		foreach ($data[1] as $method => $regexRoute) {
			foreach ($regexRoute as $route) {
				foreach ($route['routeMap'] as $item)
					$routes[implode('-', $item[0]['handler'])]['params'] = implode(' ', array_values($item[1]));
			}
		}
		$routes = array_combine(array_column($routes, 'uri'), $routes);

		return $routes;
	}
}