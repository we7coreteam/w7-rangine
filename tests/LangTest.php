<?php

namespace W7\Tests;

use W7\Facade\Translator;

class LangTest extends TestCase {
	public function testUserTrans() {
		$result = Translator::get('test.test');
		$this->assertSame('我是测试', $result);

		$result = Translator::get('test.group.test');
		$this->assertSame('我是分组测试', $result);
	}
}