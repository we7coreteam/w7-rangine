<?php
/**
 * @author donknap
 * @date 19-3-4 下午6:09
 */

namespace W7\Tcp\Listener;


use Swoole\Server;
use W7\Core\Listener\ListenerAbstract;

class ReceiveListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var Server $server
		 */
		list($server, $fd, $reactorId, $data) = $params;

	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return \Psr\Http\Message\ResponseInterface|Response
	 * @throws \ReflectionException
	 */
	private function dispatch(Server $server, Request $request, Response $response) {
		ievent(Event::ON_USER_BEFORE_REQUEST);

		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		/**
		 * @var \W7\Http\Server\Dispather $dispather
		 */
		$dispather = \iloader()->singleton(\W7\Http\Server\Dispather::class);
		$dispather->dispatch($request, $response, $server->context);

		ievent(Event::ON_USER_AFTER_REQUEST);
	}
}