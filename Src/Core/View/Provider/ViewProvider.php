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

namespace W7\Core\View\Provider;

use W7\App;
use W7\Console\Application;
use W7\Core\Exception\DumpException;
use W7\Core\View\Handler\HandlerAbstract;
use W7\Reload\Process\ReloadProcess;
use W7\Core\Provider\ProviderAbstract;
use W7\Core\View\View;

class ViewProvider extends ProviderAbstract {
	public function register() {
		$config = $this->config->get('app.view', []);
		$config['debug'] = (ENV & DEBUG) === DEBUG;

		$this->container->set(View::class, function () use ($config) {
			$view = new View($config);
			$this->registerSystemConst($view, $config);
			$this->registerSystemFunction($view);
			$this->registerSystemObject($view);
		});

		isCli() && $this->registerReloadDir($config);
	}

	protected function registerSystemFunction(View $view) {
		$view->registerFunction('irandom', function () {
			return irandom(...func_get_args());
		});
		$view->registerFunction('getClientIp', function () {
			return getClientIp();
		});
		$view->registerFunction('ienv', function () {
			return ienv(...func_get_args());
		});
		$view->registerFunction('idd', function () {
			try {
				idd(...func_get_args());
			} catch (DumpException $e) {
				echo $e->getMessage();
			}
		});
	}

	protected function registerSystemConst(View $view, $config) {
		$view->registerConst(HandlerAbstract::__STATIC__, $config['static'] ?? '/static/');
		$view->registerConst(HandlerAbstract::__CSS__, $config['css'] ?? '/static/css/');
		$view->registerConst(HandlerAbstract::__JS__, $config['js'] ?? '/static/js/');
		$view->registerConst(HandlerAbstract::__IMAGES__, $config['images'] ?? '/static/images/');
	}

	protected function registerSystemObject(View $view) {
		$view->registerObject('iconfig', App::getApp()->getConfigger());
		$view->registerObject('icache', App::getApp()->getCacher());
	}

	protected function registerReloadDir($config) {
		ReloadProcess::addType(empty($config['suffix']) ? 'html' : $config['suffix']);
		//用户自定义目录
		$userTemplatePath = (array)($config['template_path'] ?? []);
		foreach ($userTemplatePath as $path) {
			$path = (array)$path;
			foreach ($path as $item) {
				ReloadProcess::addDir($item);
			}
		}
	}

	public function providers(): array {
		return [View::class, Application::class];
	}
}
