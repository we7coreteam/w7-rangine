<?php

namespace W7\Console\Command\Server;

use W7\App;
use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;
use W7\Tcp\Console\TcpCommand;

abstract class ServerCommandAbstract extends CommandAbstract {
	protected function configure() {
		$this->addOption('--operate', '-o', InputOption::VALUE_REQUIRED, 'start|stop|restart');
	}

	protected function handle($options) {
		if (!empty($options['operate']) && method_exists($this, $options['operate'])) {
			$operate = $options['operate'];
			$this->$operate($options);
			return true;
		}

		throw new CommandException('the option --operate not be empty');
	}

	abstract protected function createServer();

	protected function start($option) {
		$server = $this->createServer();
		$status = $server->getStatus();


		if ($server->isRun()) {
			$this->output->writeln("The server have been running!(PID: {$status['masterPid']})", true);
			return $this->restart();
		}

		$statusInfo = '';
		foreach ($status as $key => $value) {
			$statusInfo .= " $key: $value, ";
		}

		$tcpLines = 'tcp  |  disable ( --enable-tcp )';
		//附加TCP服务
		if (!empty($option['enable-tcp'])) {
			$tcpServerConsole = new TcpCommand();
			$tcpServer = $tcpServerConsole->createServer();
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
			'			 Server Information					  ',
			'********************************************************************',
			"* {$server->type} | " . rtrim($statusInfo, ', '),
			"* {$tcpLines}",
			'********************************************************************',
		];

		// 启动服务器
		$this->output->writeln(implode("\n", $lines));
		$server->start();
	}

	protected function stop() {
		$server = $this->createServer();
		// 是否已启动
		if (!$server->isRun()) {
			$this->output->writeln('The server is not running!', true, true);
		}
		$this->output->writeln(sprintf('Server %s is stopping ...', $server->type));
		$result = $server->stop();
		if (!$result) {
			$this->output->writeln(sprintf('Server %s stop fail', $server->type), true, true);
		}
		$this->output->writeln(sprintf('Server %s stop success!', $server->type));
	}

	protected function restart() {
		$server = $this->createServer();
		if ($server->isRun()) {
			$this->stop();
		}
		$this->start();
	}
}