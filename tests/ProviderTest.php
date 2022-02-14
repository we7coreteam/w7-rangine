<?php

namespace W7\Tests;

use Symfony\Component\Console\Input\ArgvInput;
use W7\Console\Application;
use W7\Core\Controller\ControllerAbstract;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\Provider\ProviderManager;
use W7\Core\Route\RouteDispatcher;
use W7\Core\Route\RouteMapping;
use W7\Facade\Config;
use W7\Facade\Container;
use W7\Facade\Output;
use W7\Http\Message\Server\Request;

class RouterProviderController extends ControllerAbstract {
	public function index(Request $request) {

	}
}

class RouteProvider extends ProviderAbstract {
	public function boot() {
	}

	/**
	 * 发布配置到app/
	 */
	public function register() {
		$this->getRouter()->post('/provider/router/test', ['\W7\Tests\RouterProviderController', 'index']);
	}
}

class ConfigProvider extends ProviderAbstract {
	public function boot() {
	}

	/**
	 * 发布配置到app/
	 */
	public function register() {
		/**
		 * 加载该扩展包的路由
		 */
		$this->rootPath = __DIR__ . '/tmp';
		$this->registerConfig('test_provider.php', 'test_provider');
		$this->publishConfig('test_provider.php', 'test_provider.php');
	}
}


class ProviderTest extends TestCase {
	public function testMake() {
		/**
		 * @var Application $application
		 */
		$application = Container::get(Application::class);
		$command = $application->get('make:provider');
		$command->run(new ArgvInput([
			'input',
			'--name=test1'
		]), Output::getFacadeRoot());

		$this->assertFileExists(APP_PATH . '/Provider/Test1Provider.php');

		unlink(APP_PATH . '/Provider/Test1Provider.php');
	}

	public function testRoute() {
		/**
		 * @var ProviderManager $providerManager
		 */
		$providerManager = Container::get(ProviderManager::class);
		$providerManager->registerProvider(RouteProvider::class, 'test');

		//route
		$dispatch = RouteDispatcher::getDispatcherWithRouteMapping(RouteMapping::class, 'http');
		$result = $dispatch->dispatch('POST', '/provider/router/test');

		$this->assertSame('\W7\Tests\RouterProviderController', $result[1]['handler'][0]);
	}

	public function testConfig() {
		/**
		 * @var ProviderManager $providerManager
		 */
		$providerManager = Container::get(ProviderManager::class);
		$providerManager->registerProvider(ConfigProvider::class, 'test');

		$config = Config::get('test_provider');
		$this->assertArrayHasKey('test_provider', $config);
	}

	public function testConfigPublish() {
		/**
		 * @var ProviderManager $providerManager
		 */
		$providerManager = Container::get(ProviderManager::class);
		$providerManager->registerProvider(ConfigProvider::class, 'test');

		/**
		 * @var  Application $application
		 */
		$application = Container::get(Application::class);
		$application->get('vendor:publish')->run(new ArgvInput(
			[
				'input',
				'--provider=' . ConfigProvider::class
			]
		), Output::getFacadeRoot());

		$this->assertFileExists(BASE_PATH . '/config/test_provider.php');
		unlink(BASE_PATH . '/config/test_provider.php');
	}
}