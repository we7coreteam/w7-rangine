<?php
/**
 * @author donknap
 * @date 18-7-24 下午7:45
 */

namespace W7\Core\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareInterface {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

}