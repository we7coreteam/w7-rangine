<?php

namespace W7\Tests;

use W7\Facade\Context;

class ContextTest extends TestCase {
	public function testInNoCo() {
		Context::setContextDataByKey('test', 1);
		$this->assertSame(1, Context::getContextDataByKey('test'));

		Context::setContextDataByKey('test', null);
		$this->assertNull(Context::getContextDataByKey('test'));
	}

	public function testInCo() {
		go(function () {
			Context::setContextDataByKey('test', 1);
			$this->assertSame(1, Context::getContextDataByKey('test'));
			igo(function () {
				$data = Context::getContextDataByKey('test');
				$this->assertSame(1, $data);
				Context::setContextDataByKey('test', 3);
			});
			$this->assertSame(1, Context::getContextDataByKey('test'));
		});
		go(function () {
			Context::setContextDataByKey('test', 2);
			$this->assertSame(2, Context::getContextDataByKey('test'));
		});
	}

	public function testFork() {
		go(function () {
			Context::setContextDataByKey('test', 1);
			$this->assertSame(1, Context::getContextDataByKey('test'));
			$coid = Context::getCoroutineId();
			go(function () use ($coid) {
				Context::fork($coid);
				$data = Context::getContextDataByKey('test');
				$this->assertSame(1, $data);
				Context::setContextDataByKey('test', 3);
			});
			$this->assertSame(1, Context::getContextDataByKey('test'));
		});
	}
}