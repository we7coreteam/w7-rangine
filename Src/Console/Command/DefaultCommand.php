<?php

namespace W7\Console\Command;

class DefaultCommand extends CommandAbstract {
	protected $cmds = [
		'h|-h|help|-help' => 'help',
		'v|-v|version|-version' => 'version'
	];

	protected function version() {
		$frameworkVersion = \iconfig()::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;

		\ioutputer()->writeln("framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion\n", true);
	}

	public function help() {
		$commands = glob(__DIR__ . '/*Command.php');
		foreach ($commands as &$command) {
			$command = str_replace(__DIR__ . DS, '', $command);
			$command = str_replace('.php', '', $command);
			if ($command === 'DefaultCommand') {
				continue;
			}

			ioutputer()->writeln('-----------------------------------------------------');
			ioutputer()->writeln(str_replace('Command', '', $command) . ':');
			ioutputer()->writeln();
			$command = "\\W7\\Console\\Command\\" . $command;
			$command = new $command();
			$command->help();
		}
	}
}