<?php
/**
 * @author donknap
 * @date 19-3-4 下午6:09
 */

namespace W7\Tcp\Listener;

use W7\App;
use Swoole\Server;
use Swoole\Coroutine;
use W7\Core\Listener\ListenerAbstract;

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
        $data = ievent(Event::ON_USER_BEFORE_RECEIVE, [$data]);

        $context = App::getApp()->getContext();
        $context->setContextDataByKey('reactorid', $reactorId);
        $context->setContextDataByKey('workid', $server->worker_id);
        $context->setContextDataByKey('coid', Coroutine::getuid());

        /**
         * @var \W7\Tcp\Server\Dispather $dispather
         */
        $dispather = \iloader()->singleton(\W7\Tcp\Server\Dispather::class);
        $dispather->dispatch($fd, $data, $server);

        ievent(Event::ON_USER_AFTER_RECEIVE);
    }
}