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
use W7\Core\Exception\CommandException;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleServerAbstract;

abstract class ServerCommandAbstract extends CommandAbstract {
	private $masterServers = [];
	private $aloneServers = [];
	private $followServers = [];

	protected function configure() {
		$this->addOption('--config-app-setting-server', '-s', InputOption::VALUE_REQUIRED, 'server type');
	}

	protected function handle($options) {
		$this->clearStartServer();
		$this->parseServer();
	}

	private function parseServer() {
		$servers = trim($this->getConfig()->get('app.setting.server'));
		if (!$servers) {
			throw new CommandException('please set the server to start');
		}
		$servers = explode('|', $servers);

		$aloneServers = [];
		$followServers = [];
		$masterServers = [];

		if (count(array_intersect(array_keys(ServerEnum::$ALL_SERVER), $servers)) !== count($servers)) {
			$processServer = ServerEnum::$ALL_SERVER[ServerEnum::TYPE_PROCESS];
			unset(ServerEnum::$ALL_SERVER[ServerEnum::TYPE_PROCESS]);
			ServerEnum::$ALL_SERVER[ServerEnum::TYPE_PROCESS] = $processServer;
			$servers[] = ServerEnum::TYPE_PROCESS;
		}

		/**
		 * @var ServerAbstract $server
		 */
		foreach (ServerEnum::$ALL_SERVER as $key => $server) {
			if (!in_array($key, $servers)) {
				continue;
			}

			unset($servers[array_search($key, $servers)]);

			if (!$masterServers && $server::$masterServer) {
				$masterServers[$key] = $server;
			} elseif ($masterServers || $server::$onlyFollowMasterServer) {
				$followServers[$key] = $server;
			} else {
				$aloneServers[$key] = $server;
			}
		}

		if ($masterServers) {
			foreach (ServerEnum::$ALL_SERVER as $key => $server) {
				if ($server::$onlyFollowMasterServer) {
					$followServers[$key] = $server;
				}
			}
		}

		if (!$masterServers && $followServers) {
			throw new CommandException('server ' . implode(' , ', array_keys($followServers)) . ' must start with the master server');
		}
		if (!$masterServers && count($aloneServers) > 1) {
			foreach ($aloneServers as $name => $server) {
				if ($server::$aloneServer) {
					throw new CommandException('server ' . $name . ' can only be started independently');
				}
			}
		}

		$this->masterServers = $masterServers;
		$this->aloneServers = $aloneServers;
		$this->followServers = $followServers;
	}

	private function getMasterServer() {
		if ($this->masterServers) {
			$server = array_values($this->masterServers)[0];
		} else {
			$server = array_values($this->aloneServers)[0];
		}

		return $this->getContainer()->get($server);
	}

	private function addSubServer(SwooleServerAbstract $server) {
		$lines = [];
		foreach ($this->followServers as $handle) {
			/**
			 * @var SwooleServerAbstract $subServer
			 */
			$subServer = $this->getContainer()->get($handle);
			if ($subServer->listener($server->getServer()) === false) {
				continue;
			}
			$this->saveStartServer($subServer->getType());

			$statusInfo = '';
			foreach ($subServer->getStatus() as $ikey => $value) {
				$statusInfo .= " $ikey: $value, ";
			}
			$lines[] = "* {$subServer->getType()}  | " . rtrim($statusInfo, ', ');
		}

		return $lines;
	}

	protected function start() {
		/**
		 * @var SwooleServerAbstract $server
		 */
		$server = $this->getMasterServer();
		$this->saveStartServer($server->getType());
		$status = $server->getStatus();

		if ($server->isRun()) {
			$this->output->warning("The server have been running!(PID: {$status['masterPid']})");
			$this->restart();
			return;
		}

		$statusInfo = '';
		foreach ($status as $key => $value) {
			$statusInfo .= " $key: $value, ";
		}

		$lines = [
			'			 Server Information					  ',
			'********************************************************************',
			"* {$server->getType()} | " . rtrim($statusInfo, ', '),
		];

		$lines = array_merge($lines, $this->addSubServer($server));

		$lines[] = '********************************************************************';

		$this->output->writeln(implode("\n", $lines));
		$server->start();
	}

	protected function stop() {
		$this->clearStartServer();
		$server = $this->getMasterServer();
		// 是否已启动
		if (!$server->isRun()) {
			$this->output->warning('The server is not running!');
			return true;
		}
		$this->output->info(sprintf('Server %s is stopping ...', $server->getType()));
		$result = $server->stop();
		if (!$result) {
			$this->output->warning(sprintf('Server %s stop fail', $server->getType()));
			return false;
		}
		$this->output->success(sprintf('Server %s stop success!', $server->getType()));
		return true;
	}

	protected function restart() {
		$stop = $this->stop();
		$stop && $this->start();
	}

	private function saveStartServer($type) {
		$serverConfig = $this->getConfig()->get('app.setting.started_servers', []);
		$serverConfig[] = $type;
		$this->getConfig()->set('app.setting.started_servers', $serverConfig);
	}

	private function clearStartServer() {
		$this->getConfig()->set('app.setting.started_servers', []);
	}
}
