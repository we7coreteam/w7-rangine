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

namespace W7\Core\Bootstrap;

use W7\App;
use W7\Core\Cache\Provider\CacheProvider;
use W7\Core\Database\Provider\DatabaseProvider;
use W7\Core\Event\Provider\EventProvider;
use W7\Core\Log\Provider\LogProvider;
use W7\Core\Provider\IlluminateProvider;
use W7\Core\Provider\ProviderManager;
use W7\Core\Route\Provider\RouterProvider;
use W7\Core\Session\Provider\SessionProvider;
use W7\Core\Task\Provider\TaskProvider;
use W7\Core\Validation\Provider\ValidationProvider;
use W7\Core\View\Provider\ViewProvider;

class ProviderBootstrap implements BootstrapInterface {
	//该map可优化,涉及到config:cache命令中的获取
	public static $providerMap = [
		'illuminate' => IlluminateProvider::class,
		'event' => EventProvider::class,
		'log' => LogProvider::class,
		'router' => RouterProvider::class,
		'database' => DatabaseProvider::class,
		'cache' => CacheProvider::class,
		'task' => TaskProvider::class,
		'view' => ViewProvider::class,
		'validate' => ValidationProvider::class,
		'session' => SessionProvider::class
	];

	public function bootstrap(App $app) {
		$app->getContainer()->set(ProviderManager::class, function () use ($app) {
			return new ProviderManager($app->getContainer());
		});

		$providers = $app->getConfigger()->get('provider.providers', []);
		$providers = array_merge(self::$providerMap, $providers);
		$deferredProviders = $app->getConfigger()->get('provider.deferred', []);

		$app->getContainer()->registerDeferredService(array_keys($deferredProviders));

		/**
		 * @var ProviderManager $providerManager
		 */
		$providerManager = $app->getContainer()->singleton(ProviderManager::class);
		$providerManager->setDeferredProviders($deferredProviders);
		$providerManager->register($providers)->boot();
	}
}
