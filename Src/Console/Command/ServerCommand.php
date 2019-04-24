<?php

namespace W7\Console\Command;

use W7\Core\Exception\CommandException;
use W7\Console\Command\CommandInterface;
use W7\Core\Command\CommandInterface as ServerCommandInterface;
use W7\Core\Helper\StringHelper;

class ServerCommand implements CommandInterface {
	public function run($action, $options = []) {
		return $this->dispatch($action, $options);
	}

	private function dispatch($action, $options) {
		$supportServer = $this->supportServer();
		if (!in_array($action, $supportServer)) {
			throw new CommandException(sprintf('Not support server of %s', $action));
		}

		$serverConsole = $this->getServerConsole($action);
		$command = array_shift($options);
		if (!method_exists($serverConsole, $command)) {
			throw new CommandException(sprintf('Not support arguments of  %s', $command));
		}

		return call_user_func_array(array($serverConsole, $command), [$options]);
	}

	private function getServerConsole($name) : ServerCommandInterface {
		$className = sprintf("\\W7\\%s\\Console\\Command", StringHelper::studly($name));
		if (!class_exists($className)) {
			throw new CommandException('The ' . $name . ' server command not found');
		}
		return new $className();
	}

	/**
	 * 获取当前支持哪些服务，主是看config/server.php中是否定义服务配置
	 * @return array
	 * @throws \Exception
	 */
	private function supportServer() {
		$result = [];
		$setting = \iconfig()->getServer();

		if (empty($setting)) {
			throw new \Exception('Service information is not defined in the config file');
		}
		foreach ($setting as $serverName => $config) {
			if ($serverName == 'common' || empty($config['host']) || empty($config['port'])) {
				continue;
			}
			$result[] = $serverName;
		}
		return $result;
	}

	public function help() {
		return [
			'Commands:' => [
				'http',
				'tcp'
			],
			'Arguments:' => [
				'start' => 'Start the service.',
				'stop' => 'Stop the service.',
				'restart' => 'Restart the service',
			],
			'Options:' => [
				'-h, --help' => 'Display help information',
				'-v, --version' => 'Display version information',
				'--env' => 'Set the startup environment configuration file',
				'--enable-tcp' => 'Start Tcp service when non-Tcp service'
			]
		];
	}
}