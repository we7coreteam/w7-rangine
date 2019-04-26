<?php

namespace W7\Console\Command\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command {
	protected function configure() {
		$this->addOption('--search', null, InputOption::VALUE_REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$key = $input->getOption('search');
		if ($key) {
			$options = explode(':', $key);
			$config = iconfig()->getUserConfig($options[0]);
			if (!$config) {
				$config = iconfig()->getUserAppConfig($options[0]);
			}
			array_shift($options);

			$config = $this->getData($options, $config);

			ioutputer()->writeList($this->formatData($config, $key));
		} else {
			$this->getApplication()->setDefaultCommand($this->getName());
			$input = new ArrayInput(['--help' => true]);
			$this->getApplication()->run($input);
		}
	}

	private function getData($options, $config) {
		foreach ($options as $item) {
			if (empty($config[$item])) {
				return [];
			}
			$config = $config[$item];
		}
		return $config;
	}

	private function formatData($config, $key) {
		return [
			'your ' . $key. ' config:' => $config
		];
	}
}