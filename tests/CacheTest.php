<?php


namespace W7\Tests;

use W7\Core\Cache\Handler\RedisHandler;
use W7\Facade\Cache;
use W7\Facade\Config;

class TestCache {
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

		$obj = new TestCache();
		Cache::set('obj', $obj);
		$ret = Cache::get('obj');
		$this->assertTrue(method_exists($ret, 'ok'));

		Cache::set('obj', serialize($obj));
		$ret = Cache::get('obj');
		$this->assertTrue(method_exists(unserialize($ret), 'ok'));

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
	}

	public function hmsetAndHmget() {
		$key = uniqid('', true);
		$result = Cache::hMset($key, ['key' => 'value', 'key2' => 'value2', 'key3' => 'value3']);
		$this->assertEquals(true, $result);

		$data = [
			'value2',
			'value',
		];
		$values = Cache::hMGet($key, ['key2', 'key']);
		$this->assertEquals($data, $values);

		$data = [
			false,
			false,
		];
		$values = Cache::hMGet($key, ['NotExistKey', 'NotExistKey2']);
		$this->assertEquals($data, $values);

		$result = Cache::hMGet($key . time(), ['key']);
		$this->assertFalse($result[0]);
	}

	public function testHmsetAndHmgetByCo() {
		go(function () {
			$this->hmsetAndHmget();
		});
	}

	public function hGetAll() {
		$key = uniqid('', true);
		$result = Cache::hMset($key, ['key' => 'value', 'key2' => 'value2', 'key3' => 'value3']);
		$this->assertEquals(true, $result);

		$result = Cache::hGetAll($key);
		$this->assertEquals(['key' => 'value', 'key2' => 'value2', 'key3' => 'value3'], $result);

		Cache::set($key, 'xxxxx');
		$result = Cache::hGetAll($key);
		$this->assertFalse($result);

		Cache::delete($key);
		$result = Cache::hGetAll($key);
		$this->assertEquals([], $result);

		Cache::sAdd($key, 'xxxxx');
		$result = Cache::hGetAll($key);
		$this->assertFalse($result);
	}

	public function testHGetAllByCo() {
		go(function () {
			$this->hGetAll();
		});
	}

	public function hIncrBy() {
		$key = uniqid('', true);
		$result = Cache::hIncrBy($key, 'incr', 2);
		$this->assertEquals(2, $result);
		$result = Cache::hIncrBy($key, 'incr', 2);
		$this->assertEquals(4, $result);
		$result = Cache::hGet($key, 'incr');
		$this->assertEquals(4, $result);
	}

	public function testHIncrByCo() {
		go(function () {
			$this->hIncrBy();
		});
	}

	public function hSetNx() {
		$key = uniqid('', true);
		$result = Cache::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);

		$result = Cache::hSetNx($key, 'one', 1);
		$this->assertSame(0, $result);
	}

	public function testHSetNxByCo() {
		go(function () {
			$this->hSetNx();
		});
	}

	public function hDel() {
		$key = uniqid('', true);
		$result = Cache::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);
		$result = Cache::hSetNx($key, 'two', 2);
		$this->assertSame(1, $result);
		$result = Cache::hSetNx($key, 'three', 3);
		$this->assertSame(1, $result);

		$result = Cache::hDel($key, 'one', 'two');
		$this->assertEquals(2, $result);
		$result = Cache::hGetAll($key);
		$this->assertEquals(['three' => 3], $result);
	}

	public function testHDelByCo() {
		go(function () {
			$this->hDel();
		});
	}

	public function hLen() {
		$key = uniqid('', true);
		$result = Cache::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);
		$result = Cache::hSetNx($key, 'two', 2);
		$this->assertSame(1, $result);
		$result = Cache::hSetNx($key, 'three', 3);
		$this->assertSame(1, $result);

		$result = Cache::hLen($key);
		$this->assertEquals(3, $result);
	}

	public function testHLenByCo() {
		go(function () {
			$this->hLen();
		});
	}

	public function hExists() {
		$key = uniqid('', true);
		$result = Cache::hSetNx($key, 'one', 1);
		$this->assertSame(1, $result);

		$result = Cache::hExists($key, 'one');
		$this->assertTrue($result);
		$result = Cache::hExists($key, 'two');
		$this->assertFalse($result);
	}

	public function testHExistsByCo() {
		go(function () {
			$this->hExists();
		});
	}

	public function hValsAndHKeys() {
		$key = uniqid('', true);
		$result = Cache::hMset($key, ['one' => 1, 'two' => 'hello', 'three' => 'world']);
		$this->assertTrue($result);

		$result = Cache::hKeys($key);
		$this->assertEquals(['one', 'two', 'three'], $result);

		$result = Cache::hVals($key);
		$this->assertEquals([1, 'hello', 'world'], $result);
	}

	public function testHValsAndHKeysByCo() {
		go(function () {
			$this->hValsAndHKeys();
		});
	}

	public function sAddAndSMembers() {
		$key    = uniqid('', true);
		$value1 = uniqid('', true);
		$value2 = uniqid('', true);
		$value3 = uniqid('', true);
		$value4 = uniqid('', true);

		Cache::sAdd($key, $value1, $value2, $value3);
		Cache::sAdd($key, $value4);

		$values = [$value1, $value2, $value3, $value4];

		$members = Cache::sMembers($key);
		sort($members);
		sort($values);

		$this->assertEquals($members, $values);
	}

	public function testSaddAndSMembersByCo() {
		go(function () {
			$this->sAddAndSMembers();
		});
	}

	public function sRemoveAndScontainsAndScard() {
		$key    = uniqid('', true);
		$value1 = uniqid('', true);
		$value2 = uniqid('', true);
		Cache::sAdd($key, $value1, $value2);
		$result = Cache::sMembers($key);
		$this->assertCount(2, $result);

		$result = Cache::sIsMember($key, $value1);
		$this->assertTrue($result);

		$result = Cache::sCard($key);
		$this->assertEquals(2, $result);

		$result = Cache::sRem($key, $value1);
		$this->assertEquals(1, $result);

		$members = Cache::sMembers($key);
		$this->assertCount(1, $members);
	}

	public function testSremoveByCo() {
		go(function () {
			$this->sRemoveAndScontainsAndScard();
		});
	}

	public function zAdd() {
		$key = uniqid('', true);
		$ret = Cache::zAdd($key, 1.1, 'key');
		$this->assertEquals(1, $ret);

		$ret2 = Cache::zAdd($key, 1.3, 'key2');
		$this->assertEquals(1, $ret2);

		$ret3 = Cache::zAdd($key, 3.2, 'key3');
		$this->assertEquals(1, $ret3);

		$ret4 = Cache::zAdd($key, 1.2, 'key4');
		$this->assertEquals(1, $ret4);

		$ret5 = Cache::zAdd($key, 5.2, 'key5');
		$this->assertEquals(1, $ret5);

		$keys = Cache::zRange($key, 0, -1);
		$this->assertCount(5, $keys);

		$data = [
			'key4',
			'key2',
			'key3',
		];
		$rangeKeys = Cache::zRangeByScore($key, 1.2, 3.2);
		$this->assertEquals($data, $rangeKeys);

		$data2 = [
			'key4' => 1.2,
			'key2' => 1.3,
			'key3' => 3.2,
		];
		$rangeKeys = Cache::zRange($key, 1.2, 3.2, 'WITHSCORES');
		$this->assertEquals(array_keys($data2), $rangeKeys);

		$rangeKeys = Cache::zRange($key, 1.2, 3.2, false);
		$this->assertEquals($data, $rangeKeys);

		$rangeKeys = Cache::zRange($key, 1.2, 3.2, true);
		$this->assertEquals($data2, $rangeKeys);

		$rangeKeys = Cache::zRange($key, 1.2, 3.2, 0);
		$this->assertEquals($data, $rangeKeys);

		$rangeKeys = Cache::zRange($key, 1.2, 3.2, 'xxx');
		$this->assertEquals(array_keys($data2), $rangeKeys);

		$rangeKeys = Cache::zRangeByScore($key, 1, 2, [
			'limit' => ['offset' => 1, 'count' => 1]
		]);
		$this->assertEquals(['key4'], $rangeKeys);

		$rangeKeys = Cache::zRangeByScore($key, 1, 2, [
			'withscores' => true,
			'limit' => ['offset' => 1, 'count' => 1]
		]);
		$this->assertEquals(['key4' => 1.2], $rangeKeys);

		$rangeKeys = Cache::zRangeByScore($key, 1.2, 3.2, [
			'withscores' => true
		]);
		$this->assertEquals($data2, $rangeKeys);

		$rangeKeys = Cache::zRevRangeByScore($key, 2, 1, [
			'limit' => ['offset' => 0, 'count' => 1]
		]);
		$this->assertEquals(['key2'], $rangeKeys);

		$rangeKeys = Cache::zRevRangeByScore($key, 2, 1, [
			'limit' => ['offset' => 0, 'count' => 1],
			'withscores' => true
		]);
		$this->assertEquals(['key2' => 1.3], $rangeKeys);

		$rangeKeys = Cache::zRevRangeByScore($key, 3.2, 1.2, [
			'withscores' => true
		]);
		$this->assertEquals(['key3' => 3.2, 'key2' => 1.3, 'key4' => 1.2], $rangeKeys);
	}

	public function testZaddByCo() {
		go(function () {
			$this->zAdd();
		});
	}

	public function testlPush() {
		go(function () {
			$key = uniqid('', true);
			$result = Cache::lPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Cache::lPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testlPushx() {
		go(function () {
			$key = uniqid('', true);
			$result = Cache::lPushx($key, 'A');
			$this->assertEquals(0, $result);
			$result = Cache::lPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Cache::lPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testrPush() {
		go(function () {
			$key = uniqid('', true);
			$result = Cache::rPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Cache::rPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testrPushx() {
		go(function () {
			$key = uniqid('', true);
			$result = Cache::rPushx($key, 'A');
			$this->assertEquals(0, $result);
			$result = Cache::rPush($key, 'A');
			$this->assertEquals(1, $result);
			$result = Cache::rPush($key, 'B');
			$this->assertEquals(2, $result);
		});
	}

	public function testlLen() {
		go(function () {
			$key = uniqid('', true);
			$result = Cache::lPush($key, 'A');
			$lenResult = Cache::lLen($key);
			$this->assertEquals($result, $lenResult);
		});
	}

	public function testlPop() {
		go(function () {
			$key = uniqid('', true);
			Cache::lPush($key, 'A');
			Cache::lPush($key, 'B');
			Cache::lPush($key, 'C');

			$result = Cache::lPop($key);
			$this->assertEquals('C', $result);
		});
	}

	public function testrPop() {
		go(function () {
			$key = uniqid('', true);
			Cache::rPush($key, 'A');
			Cache::rPush($key, 'B');
			Cache::rPush($key, 'C');

			$result = Cache::rPop($key);
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
				Cache::rPush($key, $value);
			}

			$result = Cache::lRange($key, 0, -1);
			foreach ($result as $index => $value) {
				$this->assertEquals($value, $expected[ $index ]);
			}
		});
	}

	public function testlIndex() {
		go(function () {
			$key = uniqid('', true);
			Cache::rPush($key, 'A');
			Cache::rPush($key, 'B');
			Cache::rPush($key, 'C');

			$result = Cache::lIndex($key, 0);
			$this->assertEquals('A', $result);

			$result = Cache::lIndex($key, -1);
			$this->assertEquals('C', $result);

			$result = Cache::lIndex($key, 10);
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

			$result = Cache::lInsert($key, 'after', 'A', 'X');
			$this->assertEquals(0, $result);

			foreach ($expected as $value) {
				Cache::lPush($key, $value);
			}

			$result = Cache::lInsert($key, 'before', 'C', 'X');
			array_push($expected, 'X');
			$expected = array_reverse($expected);

			$this->assertEquals($result, Cache::lLen($key));
			$result = Cache::lRange($key, 0, -1);

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
				Cache::lPush($key, $value);
			}

			$counts = array_count_values($expected);
			$result = Cache::lRem($key, 'A', 1);

			$this->assertEquals($result, $counts['A']);

			$result = Cache::lRem($key, 'C', 1);

			$this->assertEquals($result, $counts['C'] - 1);

			$this->assertEquals(2, Cache::lLen($key));
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
				Cache::lPush($key, $value);
			}
			Cache::lSet($key, 0, 'A2');

			$this->assertEquals('A2', Cache::lIndex($key, 0));
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
				Cache::lPush($key, $value);
			}

			/* array('C', 'B', 'A') */
			Cache::lTrim($key, 0, 1);
			$expected = [
				'C',
				'B'
			];

			/* expected:array('C', 'B'),but it will return array('C', 'B', 'A')  */
			foreach (Cache::lRange($key, 0, -1) as $index => $value) {
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
				Cache::lPush($key, $value);
			}


			$expected = 'C';

			go(function () use ($key, $expected) {
				$result = Cache::blPop($key, 6);
				$this->assertEquals($result[1], $expected);
			});

			go(function () use ($key, $expected) {
				\co::sleep(3.0);
				Cache::lPush($key, $expected);
			});
		});
	}
}