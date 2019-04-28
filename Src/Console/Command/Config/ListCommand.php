<?php

namespace W7\Console\Command\Config;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;

class ListCommand extends CommandAbstract {
	protected function configure() {
		$this->addOption('--search', '-s', InputOption::VALUE_REQUIRED, '需要搜索的配置，例如：app.database.default');
	}

	protected function handle($options) {
		$key = $options['search'] ?? '';
		if ($key) {
			$options = explode('.', $key);
			$config = iconfig()->getUserConfig($options[0]);
			array_shift($options);

			$config = $this->getData($options, $config);

			$this->output->writeList($this->formatData($config, $key));

			return true;
		}

		throw new CommandException('the option --search not be empty');
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