<?php

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use Whoops\Exception\Inspector;

class HttpDevException extends HttpException {
	public function render(): ResponseInterface {
		ob_start();
		$render = new \Whoops\Handler\PrettyPageHandler();
		$render->handleUnconditionally(true);
		$render->setException($this);
		$render->setInspector(new Inspector($this));
		$render->setRun(new \Whoops\Run);
		$render->handle();
		$content = ob_get_clean();

		return $this->response->withContent($content);
	}
}