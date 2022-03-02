<?php
/**
 * 测试用例父类，
 */

namespace W7\Tests;

use W7\App;
use W7\Facade\FacadeAbstract;

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
	}
}