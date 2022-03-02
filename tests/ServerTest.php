<?php

namespace W7\Tests;

use W7\Core\Server\ServerEnum;
use W7\Facade\Config;
use W7\Http\Server\Server;

class ServerTest extends TestCase {
	public function testDefaultConfig() {
		$httpServer = new Server();

		$this->assertSame(2, $httpServer->setting['worker_num']);
		$this->assertSame(SWOOLE_PROCESS, $httpServer->setting['mode']);
		$this->assertSame(SWOOLE_TCP, $httpServer->setting['sock_type']);
	}

	public function testOverDefaultConfig() {
		Config::set('server.http.mode', SWOOLE_BASE);

		$httpServer = new Server();

		$this->assertSame(2, $httpServer->setting['worker_num']);
		$this->assertSame(SWOOLE_BASE, $httpServer->setting['mode']);
		$this->assertSame(SWOOLE_TCP, $httpServer->setting['sock_type']);
	}

	public function testErrorConfig() {
		Config::set('server.http.host', null);

		try{
			new Server();
		} catch (\Throwable $e) {
			$this->assertSame('server host error', $e->getMessage());
		}
	}
}