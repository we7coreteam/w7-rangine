<?php

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;

class ReleaseException extends HttpException {
	public function render(): ResponseInterface {
		$message = '服务内部错误';
		$code = '500';
		return $this->response->json(['error' => $message], $code);
	}
}