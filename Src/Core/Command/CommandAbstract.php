<?php
/**
 * @author donknap
 * @date 18-11-6 下午2:36
 */

namespace W7\Core\Command;


use W7\App;

abstract class CommandAbstract implements CommandInterface {

	protected $server;

	/**
	 * Console 的参数
	 * @var
	 */
	private $option;

	public function start($option = []) {
		$this->server = $server = $this->createServer();
		$status = $server->getStatus();

		if (!empty($option)) {
			$this->option = $option;
		}
		if ($server->isRun()) {
			\ioutputer()->writeln("The server have been running!(PID: {$status['masterPid']})", true);
			return $this->restart();
		}

		$statusInfo = '';
		foreach ($status as $key => $value) {
			$statusInfo .= " $key: $value, ";
		}

		$tcpLines = 'tcp  |  disable ( --enable-tcp )';
		//附加TCP服务
		if (!empty($this->option['enable-tcp'])) {
			$tcpServer = $this->tcpServerConsole->createServer();
			$tcpServer->listener($server->getServer());

			$tcpStatusInfo = '';
			foreach ($tcpServer->getStatus() as $key => $value) {
				$tcpStatusInfo .= " $key: $value, ";
			}
			$tcpLines = "{$tcpServer->type}  | " . rtrim($tcpStatusInfo, ', ');
		}

		App::getApp()::$server = $server;

		// 信息面板
		$lines = [
			'						 Server Information					  ',
			'********************************************************************',
			"* {$server->type} | " . rtrim($statusInfo, ', '),
			"* {$tcpLines}",
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