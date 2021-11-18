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

use Illuminate\Contracts\Container\BindingResolutionException;
use W7\App;
use W7\Core\Exception\HandlerExceptions;

class RegisterHandleExceptionsBootstrap implements BootstrapInterface {
	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	public function bootstrap(App $app): void {
		$this->registerExceptionHandlers($app);
		$this->registerUserExceptionHandler($app);
	}

	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	private function registerExceptionHandlers(App $app): void {
		$setting = $app->getConfigger()->get('app.setting');
		$errorLevel = $setting['error_reporting'] ?? ((ENV & RELEASE) === RELEASE ? E_ALL^E_NOTICE^E_WARNING : -1);
		error_reporting($errorLevel);

		((ENV & DEBUG) === DEBUG) && ini_set('display_errors', 'On');

		/**
		 * 设置错误信息接管
		 */
		$app->getContainer()->get(HandlerExceptions::class)->registerErrorHandle();
	}

	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	private function registerUserExceptionHandler(App $app): void {
		$userHandler = $app->getAppNamespace() . '\Handler\Exception\ExceptionHandler';
		if (class_exists($userHandler)) {
			$app->getContainer()->get(HandlerExceptions::class)->setHandler($userHandler);
		}
	}
}
