<?php

namespace W7\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultCommand extends Command {
	protected function execute(InputInterface $input, OutputInterface $output) {
		$logo = "
			__      _______ _______                   _      
			\ \    / /  ___  / ___|_      _____   ___ | | ___ 
			 \ \ /\ / /   / /\___ \ \ /\ / / _ \ / _ \| |/ _ \
			  \ V  V /   / /  ___) \ V  V / (_) | (_) | |  __/
			   \_/\_/   /_/  |____/ \_/\_/ \___/ \___/|_|\___|
			";
		$output->writeln($logo);

		if ($input->getOption('verbose')) {
			$this->version($output);
		} else {
			$input = new ArrayInput(['command' => 'list']);
			$this->getApplication()->run($input);
		}
	}

	protected function version(OutputInterface $output) {
		$frameworkVersion = \iconfig()::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;

		$output->writeln("framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion\n", true);
	}
}