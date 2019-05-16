<?php
/**
 * @author donknap
 * @date 18-8-24 下午4:33
 */
namespace W7\Core\Exception;

use Throwable;
use W7\App;
use Psr\Http\Message\ResponseInterface;

class HttpException extends \LogicException {
	protected $response;

	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->response = App::getApp()->getContext()->getResponse();
	}

	public function render() : ResponseInterface {
		return $this->response->json(['error' => $this->getMessage()], $this->getCode());
	}
}