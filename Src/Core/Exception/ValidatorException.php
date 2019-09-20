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

class ValidatorException extends ResponseExceptionAbstract {
	public function render(): ResponseInterface {
		return $this->response->withStatus($this->getCode())->withData(['error' => $this->getMessage()]);
	}
}
