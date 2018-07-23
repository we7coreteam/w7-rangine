<?php
/**
 * @author donknap
 * @date 18-7-19 下午3:56
 */

namespace W7\Http\Console;

use W7\Core\Base\CommandInterface;
use W7\Http\Server\Server;

class Command implements CommandInterface {
	public function start() {
		$server = $this->getServer();
		$status = $server->getStatus();

		if ($server->isRun()) {
			\ioutputer()->writeln("The server have been running!(PID: {$status['masterPid']})", true, true);
		}

		// 信息面板
		$lines = [
			'                         Server Information                      ',
			'********************************************************************',
			"* HTTP | host: {$status['host']}, port: {$status['port']}, type: {$status['type']}, worker: {$status['workerNum']}, mode: {$status['mode']}",
			'********************************************************************',
		];

		// 启动服务器
		\ioutputer()->writeln(implode("\n", $lines));
		$server->start();
	}

	public function reload() {
		// TODO: Implement reload() method.
	}

	public function stop() {
		// TODO: Implement stop() method.
	}

	private function getServer() {
		$server = new Server();
		return $server;
	}
}