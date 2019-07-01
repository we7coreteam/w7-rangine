<?php

namespace W7\Console\Command\Server;

use W7\App;
use W7\Console\Command\CommandAbstract;
use W7\Core\Crontab\CrontabServer;
use W7\Core\Process\Pool\IndependentPool;
use W7\Core\Process\ProcessServer;
use W7\Core\Server\ServerInterface;
use W7\Http\Server\Server as HttpServer;
use W7\Tcp\Server\Server as TcpServer;

abstract class ServerCommandAbstract extends CommandAbstract {
	protected function getServer() : ServerInterface {
		if ((SERVER & HTTP) === HTTP) {
			return new HttpServer();
		}
		if ((SERVER & TCP) === TCP) {
			return new TcpServer();
		}
		if ((SERVER & PROCESS) == PROCESS) {
			return (new ProcessServer())->registerPool(IndependentPool::class);
		}
		if ((SERVER & CRONTAB) === CRONTAB) {
			return (new CrontabServer())->registerPool(IndependentPool::class);
		}

		return new HttpServer();
	}

	protected function start() {
		$server = $this->getServer();
		$status = $server->getStatus();

		if ($server->isRun()) {
			$this->output->writeln("The server have been running!(PID: {$status['masterPid']})", true);
			return false;
		}

		$statusInfo = '';
		foreach ($status as $key => $value) {
			$statusInfo .= " $key: $value, ";
		}

		if ((SERVER & HTTP === HTTP) || (SERVER & TCP === TCP)) {
			App::getApp()::$server = $server;
		}

		// 信息面板
		$lines = [
			'			 Server Information					  ',
			'********************************************************************',
			"* {$server->getType()} | " . rtrim($statusInfo, ', '),
			'********************************************************************',
		];

		// 启动服务器
		$this->output->writeln(implode("\n", $lines));
		$server->start();
	}

	protected function stop() {
		$server = $this->getServer();
		// 是否已启动
		if (!$server->isRun()) {
			$this->output->writeln('The server is not running!', true, true);
		}
		$this->output->writeln(sprintf('Server %s is stopping ...', $server->getType()));
		$result = $server->stop();
		if (!$result) {
			$this->output->writeln(sprintf('Server %s stop fail', $server->getType()), true, true);
		} else {
			$this->output->writeln(sprintf('Server %s stop success!', $server->getType()));
		}

	}

	protected function restart() {
		$server = $this->getServer();
		if ($server->isRun()) {
			$this->stop();
		}
		$this->start();
	}
}