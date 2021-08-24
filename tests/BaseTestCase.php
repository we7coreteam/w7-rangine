<?php

namespace W7\Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		define('BASE_PATH', __DIR__);
		define('RUNTIME_PATH', __DIR__ . '/test');
	}
}