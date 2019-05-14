<?php

namespace W7\Core\Exception;

use Psr\Http\Message\ResponseInterface;

abstract class CustomExceptionAbstract extends \Exception {
	abstract public function render() : ResponseInterface;
}