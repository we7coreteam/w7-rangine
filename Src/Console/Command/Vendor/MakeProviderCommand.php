<?php

namespace W7\Console\Command\Vendor;

use W7\Console\Command\GeneratorCommandAbstract;

class MakeProviderCommand extends GeneratorCommandAbstract {
	protected $type = 'Provider';

	protected function configure() {
		parent::configure();
		$this->setDescription('generate provider');
	}

	protected function getStub() {
		return dirname(__DIR__, 1).'/Stubs/provider.stub';
	}

	protected function getDefaultNamespace($rootNamespace) {
		return $rootNamespace.'\Provider';
	}
}