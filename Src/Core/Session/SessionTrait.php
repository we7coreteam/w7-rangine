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

namespace W7\Core\Session;

use W7\Core\Helper\Traiter\AppCommonTrait;

trait SessionTrait {
	use AppCommonTrait;

	protected function sessionIsAutoStart() {
		$sessionAutoStart = $this->getConfig()->get('app.session.auto_start');
		return is_null($sessionAutoStart) || !empty($sessionAutoStart);
	}
}
