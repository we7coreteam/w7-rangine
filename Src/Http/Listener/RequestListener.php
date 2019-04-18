<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:08
 */

namespace W7\Http\Listener;

use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use W7\App;
use W7\Core\Config\Event;
use W7\Core\Listener\ListenerAbstract;

class RequestListener extends ListenerAbstract {
	public function run(...$params) {
		list($server, $request, $response) = $params;
		return $this->dispatch($server, $request, $response);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return \Psr\Http\Message\ResponseInterface|Response
	 * @throws \ReflectionException
	 */
	private function dispatch(Server $server, Request $request, Response $response) {
		ievent(Event::ON_BEFORE_REQUEST);

		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		$dispather = \iloader()->singleton(\W7\Http\Server\Dispather::class);
		$response = $dispather->dispatch($request, $response, $server->context);

        $data = ievent(Event::ON_AFTER_REQUEST, [$response->getContent()]);

        $response->send($data);
	}
}
