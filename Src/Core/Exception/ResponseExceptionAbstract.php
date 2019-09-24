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

use W7\App;
use Psr\Http\Message\ResponseInterface;

abstract class ResponseExceptionAbstract extends ExceptionAbstract {
	protected $response;

	public function __construct($message = '', $code = 0, \Throwable $previous = null) {
		parent::__construct($message, (int)$code, $previous);
		$this->response = App::getApp()->getContext()->getResponse();
	}

	abstract public function render() : ResponseInterface;
}
