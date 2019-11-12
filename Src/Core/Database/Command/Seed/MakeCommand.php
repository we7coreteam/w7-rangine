<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Database\Command\Seed;

use W7\Console\Command\GeneratorCommandAbstract;

class MakeCommand extends GeneratorCommandAbstract {
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'create a new seeder class';

	protected function before() {
		$this->name = ucfirst($this->name);
	}

	protected function after() {
		exec('composer dump-autoload');
	}

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub() {
		return __DIR__ . '/stubs/seeder.stub';
	}

	protected function replaceStub() {
		$this->replace('{{ DummyClass }}', $this->name);
	}

	protected function savePath() {
		return 'database/seeds';
	}
}
