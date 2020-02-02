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
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;
use W7\Tcp\Message\Message;
use W7\Tcp\Parser\ParserInterface;
use W7\Tcp\Server\Dispatcher as RequestDispatcher;

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
		$context->setContextDataByKey('fd', $fd);
		$context->setContextDataByKey('reactorid', $reactorId);
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());
	
		/**
		 * @var  Message $message
		 */
		$message = iloader()->get(ParserInterface::class)->decode($data);
		$psr7Request = new Request('POST', $message->getCmd(), [], null);
		$psr7Request = $psr7Request->withParsedBody($message->getData());
		$psr7Response = new Response();

		// ievent(SwooleEvent::ON_USER_BEFORE_REQUEST, [$psr7Request, $psr7Response]);
		/**
		 * @var RequestDispatcher $dispatcher
		 */
		$dispatcher = \iloader()->get(RequestDispatcher::class);
		$psr7Response = $dispatcher->dispatch($psr7Request, $psr7Response);

		$content = $psr7Response->getBody()->getContents();
		$server->send($fd, $content);

		// ievent(SwooleEvent::ON_USER_AFTER_REQUEST);
	}
}
