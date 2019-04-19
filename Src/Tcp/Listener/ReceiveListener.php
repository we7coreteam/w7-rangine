<?php
/**
 * @author donknap
 * @date 19-3-4 下午6:09
 */

namespace W7\Tcp\Listener;


use W7\App;
use Swoole\Coroutine;
use Swoole\Server;
use W7\Core\Config\Event;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Middleware\MiddlewareHandler;

class ReceiveListener extends ListenerAbstract {
    public function run(...$params) {
        /**
         * @var Server $server
         */
        list($server, $fd, $reactorId, $data) = $params;
        $this->dispatch($server, $reactorId, $fd, $data);
    }

    /**
     * @param server $server
     * @param reactorId $reactorId
     * @param fd $fd
     * @param data $data
     */
    private function dispatch(Server $server, $reactorId, $fd, $data) {
        ievent(Event::ON_USER_BEFORE_REQUEST);

        $context = App::getApp()->getContext();
        $context->setContextDataByKey('reactorid', $reactorId);
        $context->setContextDataByKey('workid', $server->worker_id);
        $context->setContextDataByKey('fd', $fd);
        $context->setContextDataByKey('coid', Coroutine::getuid());

        $psr7Request = new \W7\Http\Message\Server\Request('POST', '', [], null);
        $psr7Request = $psr7Request->withParsedBody($data);

        $middlewareHandler = new MiddlewareHandler($this->getMiddlewares());
        $middlewareHandler->handle($psr7Request);

        ievent(Event::ON_USER_AFTER_REQUEST);
    }

    private function getMiddlewares() {
        return [
            '\\W7\\Tcp\\Middleware\\ThriftMiddleware'
        ];
    }
}