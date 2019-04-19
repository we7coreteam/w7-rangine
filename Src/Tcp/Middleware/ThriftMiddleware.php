<?php

namespace W7\Tcp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\App;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Tcp\Server\Response;
use W7\Tcp\Services\RpcSocket;

class ThriftMiddleware extends MiddlewareAbstract {
    private $thriftProtocol = 'TBinaryProtocol';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $socket = new RpcSocket();
        $socket->buffer = $request->getParsedBody();
        $socket->server = App::$server->server;
        $socket->setHandle(App::getApp()->getContext()->getContextDataByKey('fd'));

        $protocol = '\\Thrift\\Protocol\\' . $this->thriftProtocol;
        $protocol = new $protocol($socket, false, false);

        $process = $socket->server->context['thrift_process'];
        $process->process($protocol, $protocol);

        return App::getApp()->getContext()->getResponse();
    }
}