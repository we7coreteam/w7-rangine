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

abstract class ServerCommandAbstract extends CommandAbstract {
	private $masterServers = [];
	private $aloneServers = [];
	private $followServers = [];
	private $processServers = [];

	protected function configure() {
		$this->addOption('--config-app-setting-server', '-s', InputOption::VALUE_REQUIRED, 'server type');
	}

	protected function handle($options) {
		$this->parseServer();
		$this->registerServer();
	}

	private function parseServer() {
		$servers = trim(iconfig()->getUserAppConfig('setting')['server']);
		$servers = explode('|', $servers);

		$aloneServers = [];
		$followServers = [];
		$masterServers = [];

		if(count(array_intersect(array_keys(ServerEnum::ALL_SERVER), $servers)) !== count($servers)) {
			$servers[] = ServerEnum::TYPE_PROCESS;
		}
		foreach (ServerEnum::ALL_SERVER as $key => $server) {
			if (!in_array($key, $servers)) {
				continue;
			}

			unset($servers[array_search($key, $servers)]);

			if ($masterServers && $server::$followServer) {
				$followServers[$key] = $server;
			} elseif ($masterServers && $server::$aloneServer) {
				$aloneServers[$key] = $server;
			} elseif ($server::$mainServer) {
				$masterServers[$key] = $server;
			} elseif ($server::$aloneServer) {
				$aloneServers[$key] = $server;
			} elseif ($server::$followServer) {
				$followServers[$key] = $server;
			}
		}

		if ($servers) {
			$this->processServers = $servers;
		}

		if (count($masterServers) > 1) {
			throw new CommandException('server ' . implode(' , ', array_keys($masterServers)) . ' only one can be started');
		}
		if ($masterServers && $aloneServers) {
			throw new CommandException('server ' . implode(' , ', array_keys($aloneServers)) . ' cannot follow start');
		}
		if (!$masterServers && count($aloneServers) > 1) {
			throw new CommandException('server ' . implode(' , ', array_keys($aloneServers)) . ' only one can be started');
		}
		if (!$masterServers && $followServers) {
			throw new CommandException('server ' . implode(' , ', array_keys($followServers)) . ' must start with the master server');
		}

		$this->masterServers = $masterServers;
		$this->aloneServers = $aloneServers;
		$this->followServers = $followServers;
	}

	private function registerServer() {
		$this->registerProcessServer();
		$this->registerReloadServer();
	}

	private function registerProcessServer() {
		$process = [];
		foreach ($this->processServers as $key => $item) {
			$process[] = [
				'name' => $item,
				'class' => 'W7\App\Process\\' . ucfirst($item) . 'Process',
				'number' => $item['number'] ?? 1
			];
			if (!class_exists('W7\App\Process\\' . ucfirst($item) . 'Process')) {
				throw new CommandException('process server ' . $item . ' not support as app/Process');
			}
		}

		if ($process) {
			$processConfig = iconfig()->getUserConfig('process');
			$processConfig['process'] = $process;
			iconfig()->setUserConfig('process', $processConfig);
		}
	}

	private function registerReloadServer() {
		if ((ENV & DEBUG) !== DEBUG || !$this->masterServers) {
			return false;
		}

		$this->followServers[ServerEnum::TYPE_RELOAD] = ServerEnum::ALL_SERVER[ServerEnum::TYPE_RELOAD];
	}

	private function getMasterServer() {
		if ($this->masterServers) {
			$server = array_values($this->masterServers)[0];
		} else {
			$server = array_values($this->aloneServers)[0];
		}

		return new $server();
	}

	private function addSubServer($server) {
		$lines = [];
		foreach ($this->followServers as $key => $handle) {
			$subServer = new $handle();
			$subServer->listener($server->getServer());

			$statusInfo = '';
			foreach ($subServer->getStatus() as $key => $value) {
				$statusInfo .= " $key: $value, ";
			}
			$lines[] = "* {$subServer->getType()}  | " . rtrim($statusInfo, ', ');
		}

		return $lines;
	}

	protected function start() {
		$server = $this->getMasterServer();
		$status = $server->getStatus();

		if ($server->isRun()) {
			$this->output->warning("The server have been running!(PID: {$status['masterPid']})", true);
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
	}

	protected function restart() {
		$this->stop();
		$this->start();
	}
}
