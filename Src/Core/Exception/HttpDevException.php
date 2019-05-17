<?php

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Debug\ExceptionHandler;

class HttpDevException extends HttpException {
	public function render(): ResponseInterface {
		$debug = ExceptionHandler::register(true);
		return $this->response->withContent($debug->getHtml($this));
	}
}