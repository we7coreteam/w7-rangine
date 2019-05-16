<?php
/**
 * @author donknap
 * @date 18-8-29 上午10:31
 */

namespace W7\Core\Exception;

use Throwable;
use W7\App;
use Psr\Http\Message\ResponseInterface;

abstract class ResponseException extends \LogicException {
	protected $response;
	protected $type;

	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->response = App::getApp()->getContext()->getResponse();
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function render() : ResponseInterface {
		return $this->{$this->type}();
	}

	abstract protected function custome() : ResponseInterface;
	abstract protected function dev() : ResponseInterface;
	abstract protected function release() : ResponseInterface;
}