<?php

namespace W7\Tests;

use W7\Core\Log\Handler\DailyHandler;
use W7\Facade\Config;
use W7\Facade\Logger;

class LoggerTest extends TestCase {
	public function setUp(): void {
		parent::setUp();
		Config::set('handler.log.daily', DailyHandler::class);
	}

	protected function clearLog() {
		//清空当前日志
		$files = glob(RUNTIME_PATH . '/logs/*.log');
		if ($files) {
			foreach ($files as $file) {
				unlink($file);
			}
		}
	}

	public function testWrite() {
		$this->clearLog();

		Logger::debug('test debug');

		$files = glob(RUNTIME_PATH . '/logs/w7-*.log');
		$content = file_get_contents($files[0]);

		$this->assertNotFalse(strpos($content, 'DEBUG: test debug'));

		$this->clearLog();
	}

	public function testDebugInInfo() {
		$this->clearLog();
		Logger::channel("debug_in_info")->debug('test debug');
		$files = glob(RUNTIME_PATH . '/logs/w7-*.log');
		$this->assertEmpty($files);
	}
}