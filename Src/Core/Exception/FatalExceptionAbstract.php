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

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;

abstract class FatalExceptionAbstract extends ResponseExceptionAbstract {
	public function render() : ResponseInterface {
		if ((ENV & DEBUG) === DEBUG) {
			return $this->development();
		} else {
			return $this->release();
		}
	}

	abstract protected function development() : ResponseInterface;
	abstract protected function release() : ResponseInterface;
}
