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

namespace W7\Tests;

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteParser\Std;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Core\Route\RouteCollector;
use W7\Core\Route\UrlGenerator;
use W7\Facade\Router;
use W7\Http\Message\Server\Request;

class TestMiddleware extends MiddlewareAbstract {
}

class Test1Middleware extends MiddlewareAbstract {
}

class RouteConfigTest extends TestCase {
	public function testFuncAdd() {
		Router::post('/user', function () {
			return '/user';
		});
		Router::name('user1')->middleware('AppCheckMiddleware')->get('/user/{name}', function () {
			return '/user/{name}';
		});
		Router::post('/user/get', function () {
			return '/user';
		});

		Router::middleware('AppCheckMiddleware')->name('test2')->group('/module1', function (\W7\Core\Route\Router $route) {
			$route->post('/info', function () {
				return '/module1/info';
			});
			$route->name('test-colsure')->post('/build', function () {
				return '/module1/build';
			});
		});

		Router::name('test3')->group('/module3', function (\W7\Core\Route\Router $route) {
			$route->name('module3-info')->post('/info', 'Module\BuildController@info');
			$route->name('test-build')->post('/build', 'Module\BuildController@build');
		});

		Router::name('group-name')->middleware(['AppCheckMiddleware', 'GatewayCheckSiteMiddleware'])->group('/module2', function (\W7\Core\Route\Router $route) {
			$route->get('/info', function () {
				return '/module2/info';
			});
			$route->get('/info1', 'Module\InfoController@build');
			$route->name('test-info1')->get('/info2', 'Module\InfoController@build');
			$route->options('/info', function () {
				return '/module2/build';
			});
			$route->name('test4')->group('/module3', function (\W7\Core\Route\Router $route) {
				$route->name('test4.info')->post('/info', 'Module\InfoController@info');
				$route->name('test-build')->post('/build', 'Module\InfoController@build');
				$route->name('test-handle')->post('/handle', function () {
					return 'Module\InfoController@build';
				});
				$route->post('/handle1', function () {
					return 'Module\InfoController@build';
				});

				$route->middleware('CheckAccessTokenMiddleware')->name('test5')->group('/module4', function (\W7\Core\Route\Router $route) {
					$route->post('/info', 'Module\InfoController@info');
					$route->name('test-build')->post('/build', 'Module\InfoController@build');
					$route->name('test-handle')->post('/handle', function () {
						return 'Module\InfoController@build';
					});
					$route->post('/handle1', function () {
						return 'Module\InfoController@build';
					});
				});
				$route->group('/module5', function (\W7\Core\Route\Router $route) {
					$route->name('test-info')->post('/info/{info}', 'Module\InfoController@info');
					$route->post('/info1/{info}', 'Module\InfoController@info');
					$route->name('test-build')->post('/build', 'Module\InfoController@build');
					$route->name('test-handle')->post('/handle', function () {
						return 'Module\InfoController@build';
					});
					$route->post('/handle1', function () {
						return 'Module\InfoController@build';
					});
				});
			});
		});

		$routeInfo = Router::getData();
		$dispatch = new GroupCountBased($routeInfo);

		$result = $dispatch->dispatch('GET', '/user/mizhou');
		$this->assertEquals('/user/{name}', $result[1]['handler']());
		$this->assertEquals('user1', $result[1]['name']);
		$this->assertStringContainsString('AppCheckMiddleware', $result[1]['middleware']['before'][0]['class']);

		$result = $dispatch->dispatch('GET', '/user');
		$this->assertNotEquals('/user', $result[1]);
		$result = $dispatch->dispatch('POST', '/user');
		$this->assertSame('', $result[1]['name']);

		$result = $dispatch->dispatch('POST', '/module3/info');
		$this->assertSame('module3-info', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module3/build');
		$this->assertSame('test-build', (string)$result[1]['name']);

		$result = $dispatch->dispatch('POST', '/module2/module3/info');
		$this->assertSame('test4.info', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/build');
		$this->assertSame('test-build', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/handle');
		$this->assertSame('test-handle', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/handle1');
		$this->assertSame('', (string)$result[1]['name']);
		$this->assertStringContainsString('AppCheckMiddleware', $result[1]['middleware']['before'][0]['class']);
		$this->assertStringContainsString('GatewayCheckSiteMiddleware', $result[1]['middleware']['before'][1]['class']);

		$result = $dispatch->dispatch('POST', '/module2/module3/module4/build');
		$this->assertSame('test-build', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/module4/handle');
		$this->assertSame('test-handle', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/module4/handle1');
		$this->assertSame('', (string)$result[1]['name']);
		$this->assertStringContainsString('AppCheckMiddleware', $result[1]['middleware']['before'][0]['class']);
		$this->assertStringContainsString('GatewayCheckSiteMiddleware', $result[1]['middleware']['before'][1]['class']);
		$this->assertStringContainsString('CheckAccessTokenMiddleware', $result[1]['middleware']['before'][2]['class']);

		$result = $dispatch->dispatch('POST', '/module2/module3/module5/info/1');
		$this->assertSame('test-info', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/module5/build');
		$this->assertSame('test-build', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/module5/handle');
		$this->assertSame('test-handle', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module2/module3/module5/handle1');
		$this->assertSame('', (string)$result[1]['name']);

		$result = $dispatch->dispatch('GET', '/module2/info');
		$this->assertSame('', (string)$result[1]['name']);
		$result = $dispatch->dispatch('GET', '/module2/info2');
		$this->assertSame('test-info1', (string)$result[1]['name']);

		$result = $dispatch->dispatch('POST', '/module1/info');
		$this->assertSame('', (string)$result[1]['name']);
		$result = $dispatch->dispatch('POST', '/module1/build');
		$this->assertSame('test-colsure', (string)$result[1]['name']);

		$result = $dispatch->dispatch('POST', '/module1/info');
		$this->assertEquals('/module1/info', $result[1]['handler']());
		$this->assertStringContainsString('AppCheckMiddleware', $result[1]['middleware']['before'][0]['class']);

		$result = $dispatch->dispatch('POST', '/module1/build');
		$this->assertEquals('/module1/build', $result[1]['handler']());
		$this->assertStringContainsString('AppCheckMiddleware', $result[1]['middleware']['before'][0]['class']);
	}

	public function testGroup() {
		Router::middleware('GatewayCheckSiteMiddleware')->group('/app', function (\W7\Core\Route\Router $route) {
			$route->name('resource-test')->group('/module', function (\W7\Core\Route\Router $route) {
				$route->get('/info/index', 'Module\InfoController@index');
				$route->middleware('CheckUrlIsBlackListMiddleware')->group('/info', function (\W7\Core\Route\Router $route) {
					$route->get('/test1/index', 'Module\QueryController@index');
					$route->apiResource('test', 'Module\SettingController');
				});
			});
			$route->group('/module1', function (\W7\Core\Route\Router $route) {
				$route->get('/info1/index1', 'Module\SettingController@index');
			});
		});

		$routeInfo = Router::getData();
		$dispatch = new GroupCountBased($routeInfo);

		$result = $dispatch->dispatch('GET', '/app/module/info/test');
		$this->assertEquals('W7\App\Controller\Module\SettingController', $result[1]['handler'][0]);
		$this->assertEquals('index', $result[1]['handler'][1]);
		$result = $dispatch->dispatch('POST', '/app/module/info/test');
		$this->assertEquals('W7\App\Controller\Module\SettingController', $result[1]['handler'][0]);
		$this->assertEquals('store', $result[1]['handler'][1]);
		$result = $dispatch->dispatch('POST', '/app/module/info/test');
		$this->assertEquals('W7\App\Controller\Module\SettingController', $result[1]['handler'][0]);
		$this->assertSame('store', $result[1]['handler'][1]);

		$result = $dispatch->dispatch('GET', '/app/module1/info1/index1');
		$this->assertEquals('W7\App\Controller\Module\SettingController', $result[1]['handler'][0]);
		$this->assertSame('index', $result[1]['handler'][1]);
	}

	public function testRouteOption() {
		Router::option([
			'test' => 1
		])->post('/option', function () {
			return '/user';
		});

		Router::option([
			'test1' => 1
		])->name('group-option')->group('/option-g', function (\W7\Core\Route\Router $route) {
			$route->option([
				'test' => 1
			])->get('/info', function () {
				return '/module2/info';
			});
			$route->option([
				'test2' => 1
			])->name('test4')->group('/info1', function (\W7\Core\Route\Router $route) {
				$route->option([
					'test3' => 1
				])->get('/info', function () {
					return '/module2/info';
				});
			});
		});

		$routeInfo = Router::getData();
		$dispatch = new GroupCountBased($routeInfo);

		$result = $dispatch->dispatch('POST', '/option');
		$this->assertArrayHasKey('test', $result[1]['option']);

		$result = $dispatch->dispatch('GET', '/option-g/info');
		$this->assertArrayHasKey('test1', $result[1]['option']);
		$this->assertArrayHasKey('test', $result[1]['option']);

		$result = $dispatch->dispatch('GET', '/option-g/info1/info');
		$this->assertArrayHasKey('test1', $result[1]['option']);
		$this->assertArrayHasKey('test2', $result[1]['option']);
		$this->assertArrayHasKey('test3', $result[1]['option']);
	}

	public function testMulti() {
		Router::add('GET', '/multi', function () {
			return 'success';
		});

		try {
			Router::add('GET', '/multi', function () {
				return 'success';
			});
		} catch (\Throwable $e) {
			$this->assertSame('route "/multi" for method "GET" exists in system', $e->getMessage());
		}
	}

	public function testStaticRoute() {
		try {
			Router::get('/static', 'static/index.html');
		} catch (\Throwable $e) {
			$this->assertSame('route handler static/index.html error', $e->getMessage());
		}

		Router::get('/static', 'index.html');

		$router = new GroupCountBased(Router::getData());
		$route = $router->dispatch('GET', '/static');

		$this->assertEquals($route[1]['handler'][0], '\W7\Core\Controller\StaticResourceController');
		$this->assertSame('/static', $route[1]['uri']);
	}

	public function testRouteUrl() {
		Router::name('url_name_test')->post('/url/user/get', function () {
			return '/user';
		});
		Router::name('url_name_test_params')->post('/url/user/get/{name}', function () {
			return '/user';
		});
		Router::name('url_name_test1')->post('/url/user/get1', function () {
			return '/user';
		});
		$routeCollector = new RouteCollector(new Std(), new \FastRoute\DataGenerator\GroupCountBased());

		foreach (Router::getData()[0] as $routes) {
			foreach ($routes as $route) {
				if (!empty($route['name'])) {
					$routeCollector->addRouteByName($route['name'], $route);
				}
			}
		}
		foreach (Router::getData()[1] as $routeGroup) {
			foreach ($routeGroup as $routes) {
				foreach ($routes['routeMap'] as $route) {
					$route = $route[0];
					if (!empty($route['name'])) {
						$routeCollector->addRouteByName($route['name'], $route);
					}
				}
			}
		}

		$generator = new UrlGenerator($routeCollector, function () {
			$request = new Request('GET', 'http://test.domain.com/test');
			$request = $request->withUri($request->getUri()->withPort(80));
			return $request;
		});
		$this->assertSame('/url/user/get', $generator->route('url_name_test', [], false));
		$this->assertSame('/url/user/get/34', $generator->route('url_name_test_params', ['name' => 34], false));
		$this->assertSame('http://test.domain.com/url/user/get', $generator->route('url_name_test', [], true));
		$this->assertSame('/url/user/get1', $generator->route('url_name_test1', [], false));
		$this->assertSame('http://test.domain.com/test', $generator->current());
		$this->assertSame('http://test.domain.com/test/re', $generator->to('/test/re'));
		$this->assertSame('https://test.domain.com/test/re', $generator->secure('/test/re'));
	}
}
