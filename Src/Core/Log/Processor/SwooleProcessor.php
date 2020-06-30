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

namespace W7\Core\Log\Processor;

use W7\Core\Facades\Context;

class SwooleProcessor {
	public function __invoke(array $record) {
		$workid = Context::getContextDataByKey('workid');
		$coid = Context::getContextDataByKey('coid');

		$record['workid'] = $workid ? $workid : '0';
		$record['coid'] = $coid ? $coid : '0';
		return $record;
	}
}
