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
use W7\Core\Listener\ListenerInterface;

class RequestListener implements ListenerInterface
{
	/**
	 * @param Request $request
	 * @param Response $response
	 * @return \Psr\Http\Message\ResponseInterface|Response
	 * @throws \ReflectionException
	 */
	public function run(Server $server, Request $request, Response $response) {
		$context = App::getApp()->getContext();
		$context->setContextDataByKey('workid', $server->worker_id);
		$context->setContextDataByKey('coid', Coroutine::getuid());

		/**
		 * @var \W7\Http\Server\Dispather $dispather
		 */
		$dispather = \iloader()->singleton(\W7\Http\Server\Dispather::class);
		$dispather->dispatch($request, $response, $server->context);
	}
}
