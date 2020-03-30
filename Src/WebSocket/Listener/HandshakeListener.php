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

namespace W7\WebSocket\Listener;

use Swoole\Http\Request;
use Swoole\Http\Response;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEvent;
use W7\Core\Session\Session;
use W7\Http\Message\Outputer\SwooleResponseOutputer;
use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Server\Response as Psr7Response;

class HandshakeListener extends ListenerAbstract {
	public function run(...$params) {
		list($request, $response) = $params;
		$this->handshake($request, $response);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return bool
	 * @throws \Exception
	 */
	private function handshake(Request $request, Response $response) {
		$secWebSocketKey = $request->header['sec-websocket-key'];
		if (0 === preg_match("/^[\+\/0-9A-Za-z]{21}[AQgw]==$/", $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
			return false;
		}

		try {
			/**
			 * @var Psr7Request $psr7Request
			 */
			$psr7Request = Psr7Request::loadFromSwooleRequest($request);
			$psr7Response = new Psr7Response();
			$psr7Response->setOutputer(new SwooleResponseOutputer($response));
		} catch (\Exception $e) {
			return false;
		}
		if (ievent(ServerEvent::ON_USER_BEFORE_HAND_SHAKE, [$psr7Request], true) === false) {
			return false;
		}

		$headers = [
			'Upgrade' => 'websocket',
			'Connection' => 'Upgrade',
			'Sec-WebSocket-Accept' => '',
			'Sec-WebSocket-Version' => '13',
		];
		$headers['Sec-WebSocket-Accept'] = base64_encode(sha1(
			($psr7Request->getHeader('sec-websocket-key')[0] ?? '') . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
			true
		));

		if (!empty($psr7Request->getHeader('sec-websocket-protocol'))) {
			$headers['Sec-WebSocket-Protocol'] = $psr7Request->getHeader('sec-websocket-protocol')[0] ?? '';
		}

		$response = $psr7Response->withHeaders($headers)->withStatus(101);

		$psr7Request->session = new Session();
		$psr7Request->session->start($psr7Request);
		$response = $psr7Request->session->replenishResponse($response);

		icontainer()->append('ws-client', [
			$request->fd => [$psr7Request, $response]
		], []);

		$response->send();
		return true;
	}
}
