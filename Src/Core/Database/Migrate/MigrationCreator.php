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

namespace W7\Core\Database\Migrate;

use Illuminate\Database\Migrations\MigrationCreator as MigrationCreatorAbstract;

class MigrationCreator extends MigrationCreatorAbstract {
	/**
	 * Get the path to the stubs.
	 *
	 * @return string
	 */
	public function stubPath() {
		return __DIR__.'/stubs';
	}
}
