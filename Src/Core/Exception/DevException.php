<?php

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Debug\ExceptionHandler;
use W7\App;

class DevException extends HttpException {
	public function render() : ResponseInterface {
		return $this->{'render' . ucfirst(App::$server->type)}();
	}

	private function renderHttp() {
		$debug = ExceptionHandler::register(true);
		return $this->response->withContent($debug->getHtml($this));
	}

	private function renderTcp() {
		return $this->response->json(['error' => $this->getMessage()], $this->getCode());
	}
}