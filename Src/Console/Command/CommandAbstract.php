<?php

namespace W7\Console\Command;

use W7\Console\Io\Input;

abstract class CommandAbstract implements CommandInterface {
	public function run (Input $input) {
		$command = $input->getCommand();
		if (!$command['action'] || $input->isHelpCommand()) {
			$this->help();
			return true;
		}

		try{
			$this->dispatch($command['action'], $command['option']);
		} catch (\Throwable $e) {
			\ioutputer()->writeln($e->getMessage());
			$this->help();
		}
	}

	public function dispatch($action, $options) {

	}

	public function help() {

	}

	public function version() {
		$frameworkVersion = \iconfig()::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;

		\ioutputer()->writeln("framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion\n", true);
	}


}