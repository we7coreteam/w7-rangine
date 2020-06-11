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

use Swoole\Server;
use W7\Core\Facades\Config;
use W7\Core\Facades\Container;
use W7\Core\Facades\Event;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\ServerEvent;
use W7\Core\Session\Session;
use W7\Http\Message\Outputer\TcpResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;

class ConnectListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $fd, $reactorId) = $params;
		return $this->onConnect($server, $fd, $reactorId);
	}

	private function onConnect(Server $server, $fd, $reactorId) {
		/**
		 * @var Psr7Request $psr7Request
		 */
		$psr7Request = new Psr7Request('', '');
		$psr7Response = new Psr7Response();
		$psr7Response->setOutputer(new TcpResponseOutputer($server, $fd));

		//tcp session保证此次连接中是共享数据，Response没办法下放sessionid，不存在两次连接共用数据
		$psr7Request->session = new Session(Config::get('app.session', []));
		$psr7Request->session->start($psr7Request);

		Container::append('tcp-client', [
			$fd => [$psr7Request, $psr7Response]
		], []);

		Event::dispatch(ServerEvent::ON_USER_AFTER_OPEN, [$server, $fd, $psr7Request, ServerEnum::TYPE_TCP]);
	}
}
