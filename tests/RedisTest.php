<?php

namespace W7\Tests;

use W7\Core\Cache\Handler\RedisHandler;
use W7\Facade\Cache;
use W7\Facade\Config;
use W7\Facade\Redis;

class TestCache {
	public function ok() {

	}
}

class RedisTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
		Config::set('handler.cache.redis', RedisHandler::class);
	}

	public function testPool() {
		go(function () {
			$client = null;
			go(function () use (&$client) {
				$client = Redis::channel("default")->client();
				$client2 = Redis::channel("default")->client();

				$this->assertSame($client, $client2);
			});

			$client1 = Redis::channel("default")->client();

			$this->assertSame($client, $client1);
		});
	}

	public function testCache() {
		Redis::set('test', 'test1');
		$ret = Redis::get('test');
		$this->assertSame('test1', $ret);

		Redis::set('test', json_encode([
			'test1' => 1
		]));
		$ret = Redis::get('test');
		$ret = json_decode($ret, true);
		$this->assertArrayHasKey('test1', $ret);

		$obj = new TestCache();
		Redis::set('obj', serialize($obj));
		$ret = Redis::get('obj');
		$ret = unserialize($ret);
		$this->assertTrue(method_exists($ret, 'ok'));

		Redis::set('obj', serialize($obj));
		$ret = Redis::get('obj');
		$this->assertTrue(method_exists(unserialize($ret), 'ok'));
	}

	public function testHmsetAndHmget() {
		$key = uniqid('', true);
		$result = Redis::hMset($key, ['key' => 'value', 'key2' => 'value2', 'key3' => 'value3']);
		$this->assertEquals(true, $result);

		$data = [
			'value2',
			'value',
		];
		$values = Redis::hMGet($key, ['key2', 'key']);
		$this->assertEquals($data, $values);

		$data = [
			false,
			false,
		];
		$values = Redis::hMGet($key, ['NotExistKey', 'NotExistKey2']);
		$this->assertEquals($data, $values);

		$result = Redis::hMGet($key . time(), ['key']);
		$this->assertFalse($result[0]);
	}

	public function testHmsetAndHmgetByCo() {
		go(function () {
			$this->testHmsetAndHmget();
		});
	}

	public function testHGetAll() {
		$key = uniqid('', true);
		$result = Redis::hMset($key, ['key' => 'value', 'key2' => 'value2', 'key3' => 'value3']);
		$this->assertEquals(true, $result);

		$result = Redis::hGetAll($key);
		$this->assertEquals(['key' => 'value', 'key2' => 'value2', 'key3' => 'value3'], $result);

		Redis::set($key, 'xxxxx');
		$result = Redis::hGetAll($key);
		$this->assertFalse($result);

		Redis::del($key);
		$result = Redis::hGetAll($key);
		$this->assertEquals([], $result);

		Redis::sAdd($key, 'xxxxx');
		$result = Redis::hGetAll($key);
		$this->assertFalse($result);
	}

	public function testHGetAllByCo() {
		go(function () {
			$this->testHGetAll();
		});
	}

	public function testHIncrBy() {
		$key = uniqid('', true);
		$result = Redis::hIncrBy($key, 'incr', 2);
		$this->assertEquals(2, $result);
		$result = Redis::hIncrBy($key, 'incr', 2);
		$this->assertEquals(4, $result);
		$result = Redis::hGet($key, 'incr');
		$this->assertEquals(4, $result);
	}

	public function testHIncrByCo() {
		go(function () {
			$this->testHIncrBy();
		});
	}

	public function testHIncrBySetNx() {
		$key = uniqid('', true);
		$result = Redis::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);

		$result = Redis::hSetNx($key, 'one', 1);
		$this->assertSame(0, $result);
	}

	public function testHSetNxByCo() {
		go(function () {
			$this->testHIncrBySetNx();
		});
	}

	public function testHDel() {
		$key = uniqid('', true);
		$result = Redis::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);
		$result = Redis::hSetNx($key, 'two', 2);
		$this->assertSame(1, $result);
		$result = Redis::hSetNx($key, 'three', 3);
		$this->assertSame(1, $result);

		$result = Redis::hDel($key, 'one', 'two');
		$this->assertEquals(2, $result);
		$result = Redis::hGetAll($key);
		$this->assertEquals(['three' => 3], $result);
	}

	public function testHDelByCo() {
		go(function () {
			$this->testHDel();
		});
	}

	public function testHLen() {
		$key = uniqid('', true);
		$result = Redis::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);
		$result = Redis::hSetNx($key, 'two', 2);
		$this->assertSame(1, $result);
		$result = Redis::hSetNx($key, 'three', 3);
		$this->assertSame(1, $result);

		$result = Redis::hLen($key);
		$this->assertEquals(3, $result);
	}

	public function testHLenByCo() {
		go(function () {
			$this->testHLen();
		});
	}

	public function testHExists() {
		$key = uniqid('', true);
		$result = Redis::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);

		$result = Redis::hExists($key, 'one');
		$this->assertTrue($result);
		$result = Redis::hExists($key, 'two');
		$this->assertFalse($result);
	}

	public function testHExistsByCo() {
		go(function () {
			$this->testHExists();
		});
	}

	public function testHValsAndHKeys() {
		$key = uniqid('', true);
		$result = Redis::hMset($key, ['one' => 1, 'two' => 'hello', 'three' => 'world']);
		$this->assertTrue($result);

		$result = Redis::hKeys($key);
		$this->assertEquals(['one', 'two', 'three'], $result);

		$result = Redis::hVals($key);
		$this->assertEquals([1, 'hello', 'world'], $result);
	}

	public function testHValsAndHKeysByCo() {
		go(function () {
			$this->testHValsAndHKeys();
		});
	}

	public function testSAddAndSMembers() {
		$key    = uniqid('', true);
		$value1 = uniqid('', true);
		$value2 = uniqid('', true);
		$value3 = uniqid('', true);
		$value4 = uniqid('', true);

		Redis::sAdd($key, $value1, $value2, $value3);
		Redis::sAdd($key, $value4);

		$values = [$value1, $value2, $value3, $value4];

		$members = Redis::sMembers($key);
		sort($members);
		sort($values);

		$this->assertEquals($members, $values);
	}

	public function testSaddAndSMembersByCo() {
		go(function () {
			$this->testSAddAndSMembers();
		});
	}

	public function testSRemoveAndScontainsAndScard() {
		$key    = uniqid('', true);
		$value1 = uniqid('', true);
		$value2 = uniqid('', true);
		Redis::sAdd($key, $value1, $value2);
		$result = Redis::sMembers($key);
		$this->assertCount(2, $result);

		$result = Redis::sIsMember($key, $value1);
		$this->assertTrue($result);

		$result = Redis::sCard($key);
		$this->assertEquals(2, $result);

		$result = Redis::sRem($key, $value1);
		$this->assertEquals(1, $result);

		$members = Redis::sMembers($key);
		$this->assertCount(1, $members);
	}

	public function testSremoveByCo() {
		go(function () {
			$this->testSRemoveAndScontainsAndScard();
		});
	}

	public function testZAdd() {
		$key = uniqid('', true);
		$ret = Redis::zAdd($key, 1.1, 'key');
		$this->assertEquals(1, $ret);

		$ret2 = Redis::zAdd($key, 1.3, 'key2');
		$this->assertEquals(1, $ret2);

		$ret3 = Redis::zAdd($key, 3.2, 'key3');
		$this->assertEquals(1, $ret3);

		$ret4 = Redis::zAdd($key, 1.2, 'key4');
		$this->assertEquals(1, $ret4);

		$ret5 = Redis::zAdd($key, 5.2, 'key5');
		$this->assertEquals(1, $ret5);

		$keys = Redis::zRange($key, 0, -1);
		$this->assertCount(5, $keys);

		$data = [
			'key4',
			'key2',
			'key3',
		];
		$rangeKeys = Redis::zRangeByScore($key, 1.2, 3.2);
		$this->assertEquals($data, $rangeKeys);

		$data2 = [
			'key4' => 1.2,
			'key2' => 1.3,
			'key3' => 3.2,
		];
		$rangeKeys = Redis::zRange($key, 1.2, 3.2, 'WITHSCORES');
		$this->assertEquals(array_keys($data2), $rangeKeys);

		$rangeKeys = Redis::zRange($key, 1.2, 3.2, false);
		$this->assertEquals($data, $rangeKeys);

		$rangeKeys = Redis::zRange($key, 1.2, 3.2, true);
		$this->assertEquals($data2, $rangeKeys);

		$rangeKeys = Redis::zRange($key, 1.2, 3.2, 0);
		$this->assertEquals($data, $rangeKeys);

		$rangeKeys = Redis::zRange($key, 1.2, 3.2, 'xxx');
		$this->assertEquals(array_keys($data2), $rangeKeys);

		$rangeKeys = Redis::zRangeByScore($key, 1, 2, [
			'limit' => ['offset' => 1, 'count' => 1]
		]);
		$this->assertEquals(['key4'], $rangeKeys);

		$rangeKeys = Redis::zRangeByScore($key, 1, 2, [
			'withscores' => true,
			'limit' => ['offset' => 1, 'count' => 1]
		]);
		$this->assertEquals(['key4' => 1.2], $rangeKeys);

		$rangeKeys = Redis::zRangeByScore($key, 1.2, 3.2, [
			'withscores' => true
		]);
		$this->assertEquals($data2, $rangeKeys);

		$rangeKeys = Redis::zRevRangeByScore($key, 2, 1, [
			'limit' => ['offset' => 0, 'count' => 1]
		]);
		$this->assertEquals(['key2'], $rangeKeys);

		$rangeKeys = Redis::zRevRangeByScore($key, 2, 1, [
			'limit' => ['offset' => 0, 'count' => 1],
			'withscores' => true
		]);
		$this->assertEquals(['key2' => 1.3], $rangeKeys);

		$rangeKeys = Redis::zRevRangeByScore($key, 3.2, 1.2, [
			'withscores' => true
		]);
		$this->assertEquals(['key3' => 3.2, 'key2' => 1.3, 'key4' => 1.2], $rangeKeys);
	}

	public function testZaddByCo() {
		go(function () {
			$this->testZAdd();
		});
	}

	public function testlPush() {
		go(function () {
			$key = uniqid('', true);
			$result = Redis::lPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Redis::lPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testlPushx() {
		go(function () {
			$key = uniqid('', true);
			$result = Redis::lPushx($key, 'A');
			$this->assertEquals(0, $result);
			$result = Redis::lPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Redis::lPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testrPush() {
		go(function () {
			$key = uniqid('', true);
			$result = Redis::rPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Redis::rPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testrPushx() {
		go(function () {
			$key = uniqid('', true);
			$result = Redis::rPushx($key, 'A');
			$this->assertEquals(0, $result);
			$result = Redis::rPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Redis::rPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testlLen() {
		go(function () {
			$key = uniqid('', true);
			$result = Redis::lPush($key, 'A');
			$lenResult = Redis::lLen($key);
			$this->assertEquals($result, $lenResult);
		});
	}

	public function testlPop() {
		go(function () {
			$key = uniqid('', true);
			Redis::lPush($key, 'A');
			Redis::lPush($key, 'B');
			Redis::lPush($key, 'C');

			$result = Redis::lPop($key);
			$this->assertEquals('C', $result);
		});
	}

	public function testrPop() {
		go(function () {
			$key = uniqid('', true);
			Redis::rPush($key, 'A');
			Redis::rPush($key, 'B');
			Redis::rPush($key, 'C');

			$result = Redis::rPop($key);
			$this->assertEquals('C', $result);
		});
	}

	public function testlRange() {
		go(function () {
			$expected = [
				'A',
				'B',
				'C'
			];
			$key = uniqid('', true);
			foreach ($expected as $value) {
				Redis::rPush($key, $value);
			}

			$result = Redis::lRange($key, 0, -1);
			foreach ($result as $index => $value) {
				$this->assertEquals($value, $expected[ $index ]);
			}
		});
	}

	public function testlIndex() {
		go(function () {
			$key = uniqid('', true);
			Redis::rPush($key, 'A');
			Redis::rPush($key, 'B');
			Redis::rPush($key, 'C');

			$result = Redis::lIndex($key, 0);
			$this->assertEquals('A', $result);

			$result = Redis::lIndex($key, -1);
			$this->assertEquals('C', $result);

			$result = Redis::lIndex($key, 10);
			$this->assertFalse($result);
		});
	}

	public function testlInsert() {
		go(function () {
			$key = uniqid('', true);

			$expected = [
				'A',
				'B',
				'C'
			];

			$result = Redis::lInsert($key, 'after', 'A', 'X');
			$this->assertEquals(0, $result);

			foreach ($expected as $value) {
				Redis::lPush($key, $value);
			}

			$result = Redis::lInsert($key, 'before', 'C', 'X');
			array_push($expected, 'X');
			$expected = array_reverse($expected);

			$this->assertEquals($result, Redis::lLen($key));
			$result = Redis::lRange($key, 0, -1);

			foreach ($result as $index => $value) {
				$this->assertEquals($value, $expected[ $index ]);
			}

		});
	}

	public function testlRem() {
		go(function () {
			$key = uniqid('', true);
			$expected = [
				'A',
				'B',
				'C',
				'A',
				'A',
				'C'
			];

			foreach ($expected as $value) {
				Redis::lPush($key, $value);
			}

			$counts = array_count_values($expected);
			$result = Redis::lRem($key, 1, 'A');
			$this->assertEquals($result, 1);

			$result = Redis::lRem($key, 1, 'C');

			$this->assertEquals($result, 1);

			$this->assertEquals(4, Redis::lLen($key));
		});
	}

	public function testlSet() {
		go(function () {
			$key = uniqid('', true);
			$expected = [
				'A',
				'B',
				'C',
			];

			foreach ($expected as $value) {
				Redis::lPush($key, $value);
			}
			Redis::lSet($key, 0, 'A2');

			$this->assertEquals('A2', Redis::lIndex($key, 0));
		});
	}

	/**
	 * @bug [swoole-bug]
	 */
	public function testlTrim() {
		go(function () {
			$key = uniqid('', true);
			$expected = [
				'A',
				'B',
				'C',
			];

			foreach ($expected as $value) {
				Redis::lPush($key, $value);
			}

			/* array('C', 'B', 'A') */
			Redis::lTrim($key, 0, 1);
			$expected = [
				'C',
				'B'
			];

			/* expected:array('C', 'B'),but it will return array('C', 'B', 'A')  */
			foreach (Redis::lRange($key, 0, -1) as $index => $value) {
				$this->assertEquals($value, $expected[ $index ]);
			}
		});
	}

	public function testblPop() {
		go(function () {
			$key = uniqid('', true);
			$expected = [
				'A',
				'B',
				'C',
			];

			foreach ($expected as $value) {
				Redis::lPush($key, $value);
			}


			$expected = 'C';

			go(function () use ($key, $expected) {
				$result = Redis::blPop($key, 6);
				$this->assertEquals($result[1], $expected);
			});

			go(function () use ($key, $expected) {
				\co::sleep(3.0);
				Redis::lPush($key, $expected);
			});
		});
	}

	public function testScan() {
		$redis = Redis::channel('default')->client();

		$redis->set('a1', '1');
		$redis->set('a2', '2');
		$redis->set('a3', '3');
		$redis->set('a4', '4');
		$redis->set('b14', '4');
		$redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
		$iterator = null;
		$num = 0;
		while ($keys = $redis->scan($iterator, 'a*',2)){
			$num += count($keys);
		}

		$redis->del('a1', 'a2', 'a3', 'a4', 'b14');

		$this->assertSame(4, $num);
	}
}