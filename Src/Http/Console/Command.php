<?php
/**
 * @author donknap
 * @date 18-7-19 下午3:56
 */

namespace W7\Http\Console;

use W7\Core\Command\CommandInterface;
use W7\Http\Server\Server;

class Command implements CommandInterface
{
	/**
	 * @var \W7\Core\Server\ServerAbstract $server
	 */
	private $server;

	public function start()
	{
		$this->server = $server = $this->createServer();
		$status = $server->getStatus();

		if ($server->isRun()) {
			\ioutputer()->writeln("The server have been running!(PID: {$status['masterPid']})", true);
			return $this->restart();
		}

		// 信息面板
		$lines = [
			'						 Server Information					  ',
			'********************************************************************',
			"* HTTP | host: {$status['host']}, port: {$status['port']}, type: {$status['type']}, worker: {$status['workerNum']}, mode: {$status['mode']}",
			'********************************************************************',
		];

		// 启动服务器
		\ioutputer()->writeln(implode("\n", $lines));
		$server->start();
	}

	public function stop()
	{
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

	public function restart()
	{
		$this->server = $server = $this->createServer();
		if ($this->server->isRun()) {
			$this->stop();
		}
		$this->start();
	}

	public function createServer()
	{
		$server = new Server();
		return $server;
	}
}
