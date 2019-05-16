<?php
/**
 * @author donknap
 * @date 18-8-24 下午4:33
 */
namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Debug\ExceptionHandler;

class HttpException extends TcpException {
  protected function custome() : ResponseInterface {
    return parent::dev();
	}
	
	protected function dev () : ResponseInterface {
		$debug = ExceptionHandler::register(true);
		return $this->response->withContent($debug->getHtml($this));
	}
}