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

namespace W7\Console\Command\Server;

class StopCommand extends ServerCommandAbstract {
	protected string $description = 'stop server';

	protected function handle($options): void {
		parent::handle($options);
		$this->stop();
	}
}
