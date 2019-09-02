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

class TcpFatalException extends FatalExceptionAbstract {
	protected function development(): ResponseInterface {
		return $this->response->withData(['error' => $this->getMessage()], $this->getCode());
	}

	protected function release(): ResponseInterface {
		return $this->response->withData(['error' => '系统内部错误'], 500);
	}
}
