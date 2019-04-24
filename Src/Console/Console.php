<?php
/**
 * 控制台管理
 * 根据config中注册的服务配置，路由到相应的服务组件处理
 * @author donknap
 * @date 18-7-19 上午10:04
 */

namespace W7\Console;

use W7\Console\Command\CommandInterface;
use W7\Console\Io\Input;
use W7\Core\Exception\CommandException;
use W7\Core\Helper\StringHelper;

class Console {
	public function run() {
		$this->checkEnv();
		\ioutputer()->writeLogo();

		$input = iloader()->singleton(Input::class);
		$command = $input->getCommand();

		if ($input->isVersionCommand()) {
			$this->showVersion();
			return true;
		}

		if (!$command['command']) {
			$this->showDefaultCommand();
			return true;
		}

		$commandInstance = null;
		try{
			$commandInstance = $this->getCommandInstance($command['command']);
			$commandInstance->run($input);
		}catch (\Throwable $e) {
			\ioutputer()->writeln($e->getMessage());
			$this->showDefaultCommand();
		}

		return true;
	}

	private function getCommandInstance($command) : CommandInterface {
		$className = sprintf("\\W7\\Console\\Command\\%s", StringHelper::studly($command) . 'Command');
		if (!class_exists($className)) {
			throw new CommandException('The ' . $command . ' command not found');
		}
		return new $className();
	}

	private function showVersion() {
		$frameworkVersion = \iconfig()::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;

		\ioutputer()->writeln("framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion\n", true);
	}

	private function showDefaultCommand() {
		$commandList = [
			'Usage:' => [
				"php bin/gerent.php {command} [arguments] [options]",
				"php bin/server.php {command} [arguments] [options]"
			],
			'bin/gerent.php' => [
				'Commands' => [
					'config'
				],
				'Arguments:' => [
					'route' => 'get app route config',
					'server' => 'get app server config',
					'cache' => 'get app cache config',
				]
			],
			'bin/server.php' => [
				'Commands' => [
					'http',
					'tcp'
				],
				'Arguments:' => [
					'start' => 'Start the service.',
					'stop' => 'Stop the service.',
					'restart' => 'Restart the service'
				],
				'Options:' => [
					'--env' => 'Set the startup environment configuration file',
					'--enable-tcp' => 'Start Tcp service when non-Tcp service'
				]
			]
		];
		\ioutputer()->writeList($commandList, 'comment', 'info');
		return true;
	}

	private function checkEnv() {
		if (PHP_SAPI !== 'cli') {
			throw new \RuntimeException('Must be running in php cli mode');
		}
		if (!version_compare(PHP_VERSION, '7.0')) {
			throw new \RuntimeException('Php version must be greater than 7.0');
		}

		if (!\extension_loaded('swoole')) {
			throw new \RuntimeException('Missing Swoole extension, please install the latest version');
		}

		if (!class_exists('Swoole\Coroutine')) {
			throw new \RuntimeException("Swoole Coroutine is not enabled, please append the '--enable-coroutine' parameter when compiling");
		}
	}
}
