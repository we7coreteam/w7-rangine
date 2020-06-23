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

namespace W7\Core\Provider;

use Illuminate\Filesystem\Filesystem;
use W7\App;
use W7\Console\Application;
use W7\Core\Config\Config;
use W7\Core\Container\Container;
use W7\Core\Facades\Event;
use W7\Core\Helper\StringHelper;
use W7\Core\Facades\Logger as LoggerFacade;
use W7\Core\Log\LogBuffer;
use W7\Core\Log\Logger;
use W7\Core\Facades\Router as RouterFacade;
use W7\Core\Log\Processor\SwooleProcessor;
use W7\Core\Route\Router;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Core\Facades\View as ViewFacade;
use W7\Core\View\View;

/**
 * Class ProviderAbstract
 * @package W7\Core\Provider
 * @property-read Config $config
 * @property-read Router $router
 * @property-read Container $container
 * @property-read Logger $logger
 */
abstract class ProviderAbstract {
	protected $name;

	//composer包名
	protected $packageName;
	//composer包的namespace
	protected $packageNamespace;

	public static $publishes = [];
	public static $publishGroups = [];
	protected $rootPath;

	final public function __construct($name = null) {
		if (!$name) {
			$name = get_called_class();
		}
		$this->name = $name;
		if ($this->packageName) {
			$this->rootPath = BASE_PATH . '/vendor/' . $this->packageName;
			!$this->packageNamespace && $this->packageNamespace = str_replace('/', '\\', StringHelper::studly($this->packageName));
		} else {
			$reflect = new \ReflectionClass($this);
			$this->packageNamespace = $reflect->getNamespaceName();
			$this->rootPath = dirname($reflect->getFileName(), 2);
		}
	}

	/**
	 * Register any application services.
	 * @return void
	 */
	public function register() {
	}

	/**
	 * boot any application services
	 * @return mixed
	 */
	public function boot() {
	}

	protected function registerBaseDir($dir) {
		$dir = (array)$dir;
		$appBasedir = $this->getConfig()->get('app.setting.basedir', []);
		$appBasedir = array_merge($appBasedir, $dir);
		$this->getConfig()->set('app.setting.basedir', $appBasedir);
	}

	protected function registerProvider($provider) {
		$this->getContainer()->singleton(ProviderManager::class)->registerProvider($provider);
	}

	protected function registerConfig($fileName, $key) {
		$this->mergeConfigFrom($this->rootPath . '/config/' . $fileName, $key);
	}

	protected function publishConfig($sourceFileName, $targetFileName = null, $group = null) {
		if (!isCli()) {
			return false;
		}

		if (!$targetFileName) {
			$targetFileName = $sourceFileName;
		}
		$this->publishes([
			$this->rootPath . '/config/' . $sourceFileName => BASE_PATH . '/config/' . $targetFileName
		], $group);
	}

	protected function registerLogger($name, $driver, $config = [], $isStack = false) {
		$logger = new Logger($name, [], []);
		$logger->bufferLimit = $config['buffer_limit'] ?? 1;

		$config['processor'] = (array)(empty($config['processor']) ? [] : $config['processor']);
		foreach ((array)$config['processor'] as $processor) {
			$logger->pushProcessor(new $processor);
		}

		$handlers = [];
		if (!$isStack) {
			$handler = $this->getConfig()->get('handler.log.' . $driver, $driver);
			if (!$handler || !class_exists($handler)) {
				throw new \RuntimeException('log handler ' . $driver . ' is not support');
			}
			$handlers[] = new LogBuffer($handler::getHandler($config), $logger->bufferLimit, $config['level'], true, true);
		} else {
			$config['channel'] = (array)$config['channel'];
			foreach ($config['channel'] as $channel) {
				/**
				 * @var Logger $channelLogger
				 */
				$channelLogger = $this->getContainer()->get('logger-' . $channel);
				$handlers = array_merge($handlers, is_object($channelLogger) ? $channelLogger->getHandlers() : []);
			}
		}
		foreach ($handlers as $handler) {
			$logger->pushHandler($handler);
		}

		$this->container->set('logger-' . $name, $logger);

		return $logger;
	}

	protected function registerRoute($fileName, $options = []) {
		$routeConfig = [
			'namespace' => $this->packageNamespace,
			'module' => $this->name,
			'name' => $this->name
		];
		$routeConfig = array_merge($routeConfig, $options);

		$this->getRouter()->name($routeConfig['name'])->middleware($routeConfig['middleware'] ?? [])->group($routeConfig, function () use ($fileName) {
			$this->loadRouteFrom($this->rootPath . '/route/' . $fileName);
		});
	}

	protected function registerStaticResource() {
		$documentRoot = $this->getConfig()->get('server.common.document_root');
		if (!$documentRoot) {
			throw new \RuntimeException("please set server['common']['document_root']");
		}

		$filesystem = new Filesystem();
		$documentRoot = rtrim($documentRoot, '/');
		$flagFilePath = $documentRoot . '/' . $this->name . '/resource.lock';
		if ($filesystem->exists($this->rootPath . '/resource') && !$filesystem->exists($flagFilePath)) {
			$filesystem->copyDirectory($this->rootPath . '/resource', $documentRoot . '/' . $this->name);
			$filesystem->put($flagFilePath, '');
		}
	}

	protected function registerEvent($event, $listener) {
		Event::listen($event, $listener);
	}

	protected function registerView($namespace) {
		$this->getView()->addTemplatePath($namespace, $this->rootPath . '/view/');
	}

	protected function registerCommand($namespace = '') {
		if (!isCli()) {
			return false;
		}
		/**
		 * @var  Application $application
		 */
		$application = $this->getContainer()->singleton(Application::class);
		$application->autoRegisterCommands($this->rootPath . '/src/Command', $this->packageNamespace, $namespace);
	}

	protected function registerServer($name, $class) {
		ServerEnum::registerServer($name, $class);
	}

	/**
	 * @param $name
	 * @param array $events
	 * @param bool $cover 是否覆盖已注册的事件
	 */
	protected function registerServerEvent($name, array $events, $cover = false) {
		/**
		 * @var ServerEvent $event
		 */
		$event = $this->getContainer()->singleton(ServerEvent::class);
		$event->addServerEvents($name, $events, $cover);
	}

	protected function setRootPath($path) {
		$this->rootPath = $path;
	}

	/**
	 * Merge the given configuration with the existing configuration.
	 * @param $path
	 * @param $key
	 */
	protected function mergeConfigFrom($path, $key) {
		$config = $this->getConfig()->get($key, []);
		$this->getConfig()->set($key, array_merge(require $path, $config));
	}

	/**
	 * Load the given routes file
	 * 不支持配置文件的路由配置方式
	 * @param $path
	 */
	protected function loadRouteFrom($path) {
		include $path;
	}

	/**
	 * Register paths to be published by the publish command.
	 * @param $paths
	 * @param null $groups
	 */
	protected function publishes($paths, $groups = null) {
		$this->ensurePublishArrayInitialized($class = static::class);

		static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);

		if (! is_null($groups)) {
			foreach ((array) $groups as $group) {
				$this->addPublishGroup($group, $paths);
			}
		}
	}

	/**
	 * Ensure the publish array for the service provider is initialized.
	 *
	 * @param  string  $class
	 * @return void
	 */
	protected function ensurePublishArrayInitialized($class) {
		if (! array_key_exists($class, static::$publishes)) {
			static::$publishes[$class] = [];
		}
	}

	/**
	 * Add a publish group / tag to the service provider.
	 *
	 * @param  string  $group
	 * @param  array  $paths
	 * @return void
	 */
	protected function addPublishGroup($group, $paths) {
		if (! array_key_exists($group, static::$publishGroups)) {
			static::$publishGroups[$group] = [];
		}

		static::$publishGroups[$group] = array_merge(
			static::$publishGroups[$group],
			$paths
		);
	}

	/**
	 * Get the paths to publish.
	 *
	 * @param  string  $provider
	 * @param  string  $group
	 * @return array
	 */
	public static function pathsToPublish($provider = null, $group = null) {
		if (! is_null($paths = static::pathsForProviderOrGroup($provider, $group))) {
			return $paths;
		}
		return [];
	}

	/**
	 * Get the paths for the provider or group (or both).
	 *
	 * @param  string|null  $provider
	 * @param  string|null  $group
	 * @return array
	 */
	protected static function pathsForProviderOrGroup($provider, $group) {
		if ($provider && $group) {
			return static::pathsForProviderAndGroup($provider, $group);
		} elseif ($group && array_key_exists($group, static::$publishGroups)) {
			return static::$publishGroups[$group];
		} elseif ($provider && array_key_exists($provider, static::$publishes)) {
			return static::$publishes[$provider];
		} elseif ($group || $provider) {
			return [];
		}
	}

	/**
	 * Get the paths for the provider and group.
	 *
	 * @param  string  $provider
	 * @param  string  $group
	 * @return array
	 */
	protected static function pathsForProviderAndGroup($provider, $group) {
		if (! empty(static::$publishes[$provider]) && ! empty(static::$publishGroups[$group])) {
			return array_intersect_key(static::$publishes[$provider], static::$publishGroups[$group]);
		}
		return [];
	}

	protected function getContainer() {
		return App::getApp()->getContainer();
	}

	protected function getConfig() {
		return App::getApp()->getConfigger();
	}

	protected function getRouter() : Router {
		return RouterFacade::getFacadeRoot();
	}

	protected function getLogger() {
		return new LoggerFacade();
	}

	protected function getView() : View {
		return ViewFacade::getFacadeRoot();
	}

	public function __get($name) {
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}

		throw new \RuntimeException('property ' . $name . ' not exists');
	}

	public function providers() : array {
		return [];
	}
}
