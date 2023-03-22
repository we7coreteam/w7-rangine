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

use W7\App;
use W7\Facade\FacadeAbstract;

function go(\Closure $closure) {
	$closure();
}

class TestCase extends \PHPUnit\Framework\TestCase {
	public function setUp() :void {
		parent::setUp();

		! defined('BASE_PATH') && define('BASE_PATH', __DIR__ . '/project');
		! defined('APP_BUILTIN_CONFIG_PATH') && define('APP_BUILTIN_CONFIG_PATH', __DIR__ . '/../vendor/composer/rangine/autoload/config');
		! defined('APP_PATH') && define('APP_PATH', __DIR__ . '/project/app');
		! defined('RUNTIME_PATH') && define('RUNTIME_PATH', __DIR__ . '/project/runtime');

		$this->initApp();
	}

	public function initApp() {
		App::$self = null;
		new App();
		//这里加清空的原因是，如果多个测试用例同时运行，如果使用门面，会有实例无法释放问题
		FacadeAbstract::$resolvedInstance = [];

		$this->initHandlerDefaultConfig();
	}

	public function initHandlerDefaultConfig() {
		App::$self->getConfigger()->set('handler.log', [
			'errorlog' => 'W7\\Core\\Log\\Handler\\ErrorlogHandler',
			'syslog' => 'W7\\Core\\Log\\Handler\\SyslogHandler',
			'daily' => 'W7\\Core\\Log\\Handler\\DailyHandler',
			'stream' => 'W7\\Core\\Log\\Handler\\StreamHandler',
		]);
		App::$self->getConfigger()->set('handler.cache', [
			'redis' => 'W7\\Core\\Cache\\Handler\\RedisHandler',
		]);
		App::$self->getConfigger()->set('handler.view', [
			'twig' => 'W7\\Core\\View\\Handler\\TwigHandler',
		]);
		App::$self->getConfigger()->set('handler.session', [
			'file' => 'W7\\Core\\Session\\Handler\\FileHandler',
		]);
	}
}
