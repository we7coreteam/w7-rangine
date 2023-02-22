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

use W7\Core\Cache\CacheFactory;
use W7\Core\Cache\Handler\HandlerAbstract;
use W7\Core\Cache\Handler\RedisHandler;
use W7\Facade\Cache;
use W7\Facade\Config;
use W7\Facade\Redis;

class TestCache1 {
	public function ok() {
	}
}

class UserCacheHandler extends HandlerAbstract {
	protected $storage;

	public static function connect($config) : HandlerAbstract {
		return new static();
	}

	public function set($key, $value, $ttl = null) {
		if ($ttl) {
			$this->storage[$key] = [
				'value' => $value,
				'expire_time' => time() + $ttl
			];
		} else {
			$this->storage[$key] = [
				'value' => $value
			];
		}
	}

	public function get($key, $default = null) {
		return $this->storage[$key]['value'] ?? null;
	}

	public function has($key) {
		if (isset($this->storage[$key])) {
			$info = $this->storage[$key];
			return !(isset($info['expire_time']) && $info['expire_time'] < time());
		}

		return false;
	}

	public function setMultiple($values, $ttl = null) {
		return false;
	}

	public function getMultiple($keys, $default = null) {
		return false;
	}

	public function delete($key) {
		if (isset($this->storage[$key])) {
			unset($this->storage[$key]);
		}
		return true;
	}

	public function deleteMultiple($keys) {
		foreach ($keys as $key) {
			$this->delete($key);
		}
	}

	public function clear() {
		$this->storage = [];
		return true;
	}

	public function alive() {
		return true;
	}

	public function __call($name, $arguments) {
		return $this->storage->$name(...$arguments);
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

		Cache::setMultiple([
			'test-1' => [
				'test1' => 1
			],
			'test1-1' => [
				'test2' => 2
			]
		], 10);
		$ret = Cache::getMultiple(['test-1', 'test1-1']);
		$this->assertArrayHasKey('test-1', $ret);
		$this->assertArrayHasKey('test1', $ret['test-1']);

		sleep(11);
		$ret = Cache::getMultiple(['test-1', 'test1-1']);
		$this->assertNull($ret['test-1']);
		$this->assertNull($ret['test1-1']);

		Cache::deleteMultiple(['test', 'test1']);
		$ret = Cache::getMultiple(['test', 'test1']);
		$this->assertNull($ret['test']);
		$this->assertNull($ret['test1']);

		$cache = Cache::channel('default1');
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

	public function testUserHandler() {
		$cacheConfig = [
			'user' => [
				'driver' => UserCacheHandler::class
			]
		];

		$cacheFactory = new CacheFactory($cacheConfig);
		$cache = $cacheFactory->channel('user');
		$cache->set('test', 'test1');
		$ret =$cache->get('test');
		$this->assertSame('test1', $ret);

		$cache->set('test', [
			'test1' => 1
		]);
		$ret = $cache->get('test');
		$this->assertArrayHasKey('test1', $ret);

		$obj = new TestCache1();
		$cache->set('obj', $obj);
		$ret = $cache->get('obj');
		$this->assertTrue(method_exists($ret, 'ok'));

		$cache->set('obj', serialize($obj));
		$ret = $cache->get('obj');
		$this->assertTrue(method_exists(unserialize($ret), 'ok'));

		$cache->set('test_del', 1);
		$this->assertTrue($cache->has('test_del'));
		$cache->delete('test_del');
		$this->assertFalse($cache->has('test_del'));

		$cache->set('test_clear_key', 1);
		$this->assertTrue($cache->has('test_clear_key'));

		$cache->clear();
		$this->assertFalse($cache->has('test_clear_key'));

		$this->assertSame('default', $cache->get('test_default_1', 'default'));

		$this->assertTrue($cache->alive());
	}
}
