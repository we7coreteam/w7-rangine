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
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerInterface;

abstract class ServerCommandAbstract extends CommandAbstract {
	private $servers;

	protected function configure() {
		$this->addOption('--config-app-setting-server', '-s', InputOption::VALUE_REQUIRED, 'server type');
	}

	private function registerProcessServer() {
		//如果启动的server中含有http,tcp,ws的时候,对用户自定义服务的类型个数不做限制
		//如果启动的server中不包含http,tcp,ws的时候,只能启动一个用户自定义服务
		$servers = [];
		$allServer = ServerEnum::ALL_SERVER;
		foreach ($allServer as $key => $server) {
			/**
			 * @var ServerAbstract $server
			 */
			if ($server::$canAddSubServer) {
				$servers[$key] = $server;
			}
		}

		$alone = true;
		if (array_intersect($this->servers, array_keys($servers))) {
//			在非单独启动自定义服务的情况下注册reload
			$this->registerReloadServer();
			$alone = false;
		}
		//不允许单独启动多个自定义服务
		if ($alone && count($this->servers) > 1) {
			throw new \Exception('only a single service can be started');
		}

		//注册自定义服务
		$process = [];
		$supportServers = iconfig()->getServer();
		foreach ($this->servers as $key => $item) {
			if (empty($supportServers[$item])) {
				throw new \Exception('not support this server');
			}
			if (!empty($allServer[$item])) {
				continue;
			}
			$process[] = [
				'name' => $item,
				'class' => 'W7\App\Process\\' . ucfirst($item) . 'Process',
				'number' => $supportServers[$item]['worker_num']
			];
			$supportServers['process'] = $supportServers[$item];
			unset($this->servers[$key]);
		}

		if ($process) {
			iconfig()->setUserConfig('process', $process);
			iconfig()->setUserConfig('server', $supportServers);
			$this->servers[] = 'process';
		}
	}

	private function registerReloadServer() {
		if ((ENV & DEBUG) !== DEBUG) {
			return false;
		}
		$this->servers[] = 'reload';
		$config = iconfig()->getServer();
		$config['reload'] = [
			'worker_num' => 1
		];
		iconfig()->setUserConfig('server', $config);
	}

	private function getServer() : ServerInterface {
		$this->servers = trim(iconfig()->getUserAppConfig('setting')['server']);
		$this->servers = explode('|', $this->servers);

		$this->registerProcessServer();

		foreach (ServerEnum::ALL_SERVER as $key => $handle) {
			if (in_array($key, $this->servers)) {
				unset($this->servers[array_search($key, $this->servers)]);
				return new $handle();
			}
		}

		throw new \Exception('server type error');
	}

	private function addSubServer($server) {
		$lines = [];
		foreach (ServerEnum::ALL_SERVER as $key => $handle) {
			if (in_array($key, $this->servers)) {
				$subServer = new $handle();
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
