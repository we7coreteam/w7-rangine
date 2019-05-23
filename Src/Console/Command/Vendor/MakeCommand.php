<?php

namespace W7\Console\Command\Vendor;

use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;
use W7\Core\Exception\CommandException;

class MakeCommand extends CommandAbstract {
	protected function configure() {
		$this->addOption('--name', null, InputOption::VALUE_REQUIRED, 'the vendor name');
		$this->addOption('--force', '-f', null, 'force overwrite the vendor');
	}

	protected function handle($options) {
		if (empty($options['name'])) {
			throw new CommandException('the option name not be empty');
		}
		$vendorPath = BASE_PATH. DS. 'vendor' . DS . $options['name'];
		if (empty($options['force']) && file_exists($vendorPath)) {
			$this->output->error('the vendor ' . $options['name'] . ' is existed');
			return false;
		}
		$this->makeVendorDir($vendorPath);

		$cmd = 'cd ./vendor/' . $options['name'] . ' && composer init';
		exec($cmd);
		if (!file_exists($vendorPath . DS . 'composer.json')) {
			throw new CommandException('generate vendor fail');
		}

		//生成包目录
		$this->makeVendorDir($vendorPath . DS . '/src');
		$this->makeVendorDir($vendorPath . DS . '/config');
		$this->output->info('vendor package generate success');

		//生成provider
		$this->call('vendor:makeprovider', [
			'--name' => 'W7\App\\' . $options['name'] . '\src\ServiceProvider',
			'--dir' => 'vendor',
			'--force' => $options['force'] ?? false
		]);
	}

	private function makeVendorDir($path) {
		if (!is_dir($path)) {
			mkdir($path, 0755);
		}
	}
}