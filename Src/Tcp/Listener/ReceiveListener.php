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
use W7\Tcp\Protocol\IDispatcher;

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
        $context->setContextDataByKey('coid', Coroutine::getuid());

        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch($server, $fd, $data);

        ievent(Event::ON_USER_AFTER_REQUEST);
    }

    private function getDispatcher() : IDispatcher {
        $serverConf = iconfig()->getServer();
        $protocol = $serverConf['protocol'] ?? 'thrift';

        $class = '';
        switch ($protocol) {
	        case 'json':
		        $class = '\\W7\\Tcp\\Protocol\\Json\\Dispatcher';
		        break;
            case 'grpc':
                $class = '\\W7\\Tcp\\Protocol\\Grpc\\Dispatcher';
                break;
            case 'thrift':
            default:
                $class = '\\W7\\Tcp\\Protocol\\Thrift\\Dispatcher';
        }

        return \iloader()->singleton($class);
    }
}