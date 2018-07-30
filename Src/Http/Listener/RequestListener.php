<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:08
 */

namespace W7\Http\Listener;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use W7\App;
use W7\Core\Base\ListenerInterface;
use W7\Core\Base\Logger;
use W7\Core\Helper\Context;
use W7\Core\Helper\Middleware;

class RequestListener implements ListenerInterface
{
    /**
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \ReflectionException
     */
    public function run(Server $server, Request $request, Response $response)
    {

        /**
         * @var Context $serverContext
         */
        $serverContext = $server->context;
        /**
         * @var \W7\Http\Server\Dispather $dispather
         */
        Logger::addBasic("logid", uniqid());
        Logger::addBasic("client", getClientIp());
        $dispather = \iloader()->singleton(\W7\Http\Server\Dispather::class);
        $dispather->dispatch($request, $response, $serverContext);
    }
}
