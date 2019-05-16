<?php
/**
 * @author donknap
 * @date 18-8-24 下午4:33
 */
namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Debug\ExceptionHandler;

class TcpException extends RespponseException {
  protected function custome() : ResponseInterface {
    return $this->dev();
  }

	protected function dev () : ResponseInterface {
		return $this->response->json(['error' => $this->getMessage()], $this->getCode());
	}

	protected function release () : ResponseInterface {
		$message = '服务内部错误';
		$code = '500';
		return $this->response->json(['error' => $message], $code);
	}
}