<?php

namespace W7\Console\Command\Vendor;

use W7\Console\Command\GeneratorCommandAbstract;

class MakeCommand extends GeneratorCommandAbstract {
	protected $type = 'Provider';

	protected function getStub() {
		return dirname(__DIR__, 1).'/Stubs/provider.stub';
	}

	protected function getDefaultNamespace($rootNamespace) {
		return $rootNamespace.'\Provider';
	}
}