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
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleServerAbstract;

abstract class ServerCommandAbstract extends CommandAbstract {
	private array $masterServers = [];
	private array $aloneServers = [];
	private array $followServers = [];

	protected function configure(): void {
		$this->addOption('--config-app-setting-server', '-s', InputOption::VALUE_REQUIRED, 'server type');
	}

	/**
	 * @throws CommandException
	 */
	protected function handle($options): void {
		$this->clearStartServer();
		$this->parseServer();
	}

	/**
	 * @throws CommandException
	 */
	private function parseServer(): void {
		$servers = trim($this->getConfig()->get('app.setting.server'));
		if (!$servers) {
			throw new CommandException('please set the server to start');
		}
		$servers = explode('|', $servers);

		$aloneServers = [];
		$followServers = [];
		$masterServers = [];

		if (count(array_intersect(array_keys(ServerEnum::$ALL_SERVER), $servers)) !== count($servers)) {
			$servers[] = ServerEnum::TYPE_PROCESS;
		}

		foreach (ServerEnum::$ALL_SERVER as $key => $server) {
			if (!in_array($key, $servers, true)) {
				continue;
			}

			unset($servers[array_search($key, $servers, true)]);

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

	private function addSubServer(SwooleServerAbstract $server): array {
		$lines = [];
		foreach ($this->followServers as $key => $handle) {
			/**
			 * @var SwooleServerAbstract $subServer
			 */
			$subServer = $this->getContainer()->get($handle);
			if ($subServer->listener($server->getServer()) === false) {
				continue;
			}
			$this->saveStartServer($subServer->getType());

			$statusInfo = '';
			foreach ($subServer->getStatus() as $statusKey => $value) {
				$statusInfo .= " $statusKey: $value, ";
			}
			$lines[] = "* {$subServer->getType()}  | " . rtrim($statusInfo, ', ');
		}

		return $lines;
	}

	protected function start(): void {
		/**
		 * @var SwooleServerAbstract $server
		 */
		$server = $this->getMasterServer();
		$this->saveStartServer($server->getType());
		$status = $server->getStatus();

		if ($server->isRun()) {
			$this->output->warning("The server have been running!(PID: {$status['masterPid']})", true);
			$this->restart();
			return ;
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

	protected function stop(): bool {
		$this->clearStartServer();
		$server = $this->getMasterServer();
		// 是否已启动
		if (!$server->isRun()) {
			$this->output->warning('The server is not running!', true, true);
			return true;
		}
		$this->output->info(sprintf('Server %s is stopping ...', $server->getType()));
		$result = $server->stop();
		if (!$result) {
			$this->output->warning(sprintf('Server %s stop fail', $server->getType()), true, true);
			return false;
		}
		$this->output->success(sprintf('Server %s stop success!', $server->getType()));
		return true;
	}

	protected function restart(): void {
		$stop = $this->stop();
		$stop && $this->start();
	}

	private function saveStartServer($type): void {
		$serverConfig = $this->getConfig()->get('app.setting.started_servers', []);
		$serverConfig[] = $type;
		$this->getConfig()->set('app.setting.started_servers', $serverConfig);
	}

	private function clearStartServer(): void {
		$this->getConfig()->set('app.setting.started_servers', []);
	}
}
