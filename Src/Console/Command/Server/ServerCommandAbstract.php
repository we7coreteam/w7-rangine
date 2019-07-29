<?php

namespace W7\Console\Command\Server;

use W7\App;
use W7\Console\Command\CommandAbstract;
use W7\Crontab\Server\CrontabServer;
use W7\Core\Exception\CommandException;
use W7\Core\Process\Pool\IndependentPool;
use W7\Process\Server\ProcessServer;
use W7\Reload\Server\ReloadServer;
use W7\Core\Server\ServerInterface;
use W7\Http\Server\Server as HttpServer;
use W7\Tcp\Server\Server as TcpServer;

abstract class ServerCommandAbstract extends CommandAbstract {
	//当前的server
	protected $netServer;
	//顺序不能变
	protected $netServerMap = [
		HTTP => 'Http',
		TCP => 'Tcp',
	];
	protected $ordinaryServerMap = [
		PROCESS => 'Process',
		CRONTAB => 'Crontab'
	];

	protected function configure() {
		$this->addArgument('type', null, 'server type');
	}

	private function getHttpServer() {
		$this->netServer = HTTP;
		return new HttpServer();
	}

	private function getTcpServer() {
		$this->netServer = TCP;
		return new TcpServer();
	}

	private function getProcessServer() {
		return (new ProcessServer())->registerPool(IndependentPool::class);
	}

	private function getCrontabServer() {
		return (new CrontabServer())->registerPool(IndependentPool::class);
	}

	private function getReloadServer() {
		return new ReloadServer();
	}

	private function getServer() : ServerInterface {
		$type = $this->input->getArgument('type');
		if (!defined('SERVER') && !$type) {
			throw new CommandException('argument type error');
		}
		if (!defined('SERVER') && $type) {
			try{
				$type = strtoupper($type);
				$server = eval('return ' . $type . ';');
				define('SERVER', $server);
			} catch (\Throwable $e) {
				throw new CommandException('argument type error');
			}
		}

		$allServer = $this->netServerMap + $this->ordinaryServerMap;
		foreach ($allServer as $key => $type) {
			if ((SERVER & $key) === $key) {
				return $this->{'get' . $type . 'Server'}();
			}
		}

		throw new \Exception('server type error');
	}

	private function addSubServer($server) {
		$lines = [];
		if (!$this->netServer) {
			return $lines;
		}

		$servers = SERVER ^ $this->netServer;
		foreach ($this->netServerMap as $key => $type) {
			if (($servers & $key) === $key) {
				$subServer = $this->{'get' . $type . 'Server'}();
				$subServer->listener($server->getServer());

				$statusInfo = '';
				foreach ($subServer->getStatus() as $key => $value) {
					$statusInfo .= " $key: $value, ";
				}
				$lines[] = "* {$subServer->getType()}  | " . rtrim($statusInfo, ', ');
			}
		}

		return $lines;
	}

	protected function start() {
		$server = $this->getServer();
		$status = $server->getStatus();

		if ($server->isRun()) {
			$this->output->writeln("The server have been running!(PID: {$status['masterPid']})", true);
			return $this->restart();
		}

		$statusInfo = '';
		foreach ($status as $key => $value) {
			$statusInfo .= " $key: $value, ";
		}

		// 信息面板
		$lines = [
			'			 Server Information					  ',
			'********************************************************************',
			"* {$server->getType()} | " . rtrim($statusInfo, ', '),
		];

		$lines = array_merge($lines, $this->addSubServer($server));

		if ((SERVER & HTTP === HTTP) || (SERVER & TCP === TCP)) {
			App::getApp()::$server = $server;
		}

		$lines[] = '********************************************************************';
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