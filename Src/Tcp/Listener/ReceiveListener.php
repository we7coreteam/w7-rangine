<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Tcp\Listener;

use W7\App;
use Swoole\Coroutine;
use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\SwooleEvent;
use W7\Tcp\Protocol\Dispatcher;

class ReceiveListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		* @var Server $server
		*/
		list($server, $fd, $reactorId, $data) = $params;

		$this->dispatch($server, $reactorId, $fd, $data);
	}

	/**
	 * 根据用户选择的protocol，把data传到对应protocol的dispatcher
	 * @param Server $server
	 * @param $reactorId
	 * @param $fd
	 * @param $data
	 * @throws \Exception
	 */
	private function dispatch(Server $server, $reactorId, $fd, $data) {
		$context = App::getApp()->getContext();
		$context->setContextDataByKey('reactorid', $reactorId);
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$serverConf = iconfig()->getServer();
		$serverConf = $serverConf[App::$server->getType()];
		$protocol = $serverConf['protocol'] ?? '';
		Dispatcher::dispatch($protocol, $server, $fd, $data);

		ievent(SwooleEvent::ON_USER_AFTER_REQUEST);
	}
}
