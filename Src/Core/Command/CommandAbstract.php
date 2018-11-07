<?php
/**
 * @author donknap
 * @date 18-11-6 下午2:36
 */

namespace W7\Core\Command;


abstract class CommandAbstract implements CommandInterface {

	protected $server;

	public function start() {
		$this->server = $server = $this->createServer();
		$status = $server->getStatus();

		if ($server->isRun()) {
			\ioutputer()->writeln("The server have been running!(PID: {$status['masterPid']})", true);
			return $this->restart();
		}

		$statusInfo = '';
		foreach ($status as $key => $value) {
			$statusInfo .= " $key: $value, ";
		}

		// 信息面板
		$lines = [
			'						 Server Information					  ',
			'********************************************************************',
			"* {$server->type} | " . rtrim($statusInfo, ', '),
			'********************************************************************',
		];

		// 启动服务器
		\ioutputer()->writeln(implode("\n", $lines));
		$server->start();
	}

	public function stop() {
		$this->server = $server = $this->createServer();
		// 是否已启动
		if (!$this->server->isRun()) {
			\ioutputer()->writeln('The server is not running!', true, true);
		}
		\ioutputer()->writeln(sprintf('Server %s is stopping ...', $this->server->type));
		$result = $this->server->stop();
		if (!$result) {
			\ioutputer()->writeln(sprintf('Server %s stop fail', $this->server->type), true, true);
		}
		ioutputer()->writeln(sprintf('Server %s stop success!', $this->server->type));
	}

	public function restart() {
		$this->server = $server = $this->createServer();
		if ($this->server->isRun()) {
			$this->stop();
		}
		$this->start();
	}
}