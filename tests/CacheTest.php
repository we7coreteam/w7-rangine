<?php


namespace W7\Tests;

use W7\Core\Cache\Handler\RedisHandler;
use W7\Facade\Cache;
use W7\Facade\Config;
use W7\Facade\Redis;

class TestCache1 {
	public function ok() {

	}
}


class CacheTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
		Config::set('handler.cache.redis', RedisHandler::class);
	}

	public function testCache() {
		Cache::set('test', 'test1');
		$ret = Cache::get('test');
		$this->assertSame('test1', $ret);

		Cache::set('test', [
			'test1' => 1
		]);
		$ret = Cache::get('test');
		$this->assertArrayHasKey('test1', $ret);

		$obj = new TestCache1();
		Cache::set('obj', $obj);
		$ret = Cache::get('obj');
		$this->assertTrue(method_exists($ret, 'ok'));

		Cache::set('obj', serialize($obj));
		$ret = Cache::get('obj');
		$this->assertTrue(method_exists(unserialize($ret), 'ok'));

		Cache::set('test_del', 1);
		$this->assertTrue(Cache::has('test_del'));
		Cache::delete('test_del');
		$this->assertFalse(Cache::has('test_del'));

		Cache::setMultiple([
			'test' => [
				'test1' => 1
			],
			'test1' => [
				'test2' => 2
			]
		]);
		$ret = Cache::getMultiple(['test', 'test1']);
		$this->assertArrayHasKey('test', $ret);
		$this->assertArrayHasKey('test1', $ret['test']);


		Cache::deleteMultiple(['test', 'test1']);
		$ret = Cache::getMultiple(['test', 'test1']);
		$this->assertFalse($ret['test']);
		$this->assertFalse($ret['test1']);

		$cache = Cache::channel("default1");
		$cache->set('test_default1', 1);
		$this->assertSame('1', $cache->get('test_default1'));
		$this->assertFalse(Cache::has('test_default1'));

		Cache::set('test_ttl', '1', 7200);
		$ttl = Redis::channel('default')->ttl('test_ttl');
		$this->assertSame(7200, $ttl);
		Cache::set('test_ttl1', '1');
		$ttl = Redis::channel('default')->ttl('test_ttl1');
		$this->assertSame(-1, $ttl);

		Cache::set('test_clear_key', 1);
		$this->assertTrue(Cache::has('test_clear_key'));

		Cache::clear();
		$this->assertFalse(Cache::has('test_clear_key'));

		$this->assertSame('default', Cache::get('test_default_1', 'default'));

		$this->assertTrue(Cache::alive());
	}
}