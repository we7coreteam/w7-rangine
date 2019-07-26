<?php

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * idd抛出的异常
 * Class DumpException
 * @package W7\Core\Exception
 */
class DumpException extends ResponseException {
	public $isLoggable = false;

	public function render(): ResponseInterface {
		return icontext()->getResponse()->withContent($this->getMessage());
	}
}