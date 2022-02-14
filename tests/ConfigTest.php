<?php
/**
 * @author donknap
 * @date 19-4-9 下午2:27
 */

namespace W7\Tests;


use W7\Core\Config\Env\Env;
use W7\Facade\Config;

class ConfigTest extends TestCase {
	public function testDefaultEnv() {
		$this->assertEquals(getenv('CACHE_DEFAULT_HOST'), '127.0.0.1');
	}

	public function testDevelopEnv() {
		putenv('ENV_NAME=develop');
		(new Env(BASE_PATH))->load();

		$this->assertEquals(getenv('TEST_DEVELOP'), 1);
	}

	public function testLoadConfig() {
		$log = Config::get('log');
		$this->assertEquals('stack', $log['default']);
	}

	public function testSetConfig() {
		Config::set('app.test', 1);
		$this->assertSame(1, Config::get('app.test'));
	}
}