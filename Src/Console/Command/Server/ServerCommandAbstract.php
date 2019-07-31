<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Console\Command\Server;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Server\ServerInterface;

abstract class ServerCommandAbstract extends CommandAbstract {
	private $servers;
	private $curServer;

	protected function configure() {
		$this->addOption('--config-app-setting-server', '-s', InputOption::VALUE_REQUIRED, 'server type');
	}

	private function getServer() : ServerInterface {
		$this->servers = iconfig()->getUserAppConfig('setting')['server'];

		foreach (iconfig()->getAllServer() as $key => $class) {
			if (($this->servers & $key) === $key) {
				$this->curServer = $key;
				return new $class();
			}
		}

		throw new \Exception('server type error');
	}

	private function addSubServer($server) {
		$lines = [];
		$this->servers = $this->servers ^ $this->curServer;
		foreach (iconfig()->getAllServer() as $key => $class) {
			if (($this->servers & $key) === $key) {
				$subServer = new $class();
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
