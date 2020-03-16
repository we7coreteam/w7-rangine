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
use W7\App;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEvent;
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
		try {
			// websocket握手连接算法验证
			$secWebSocketKey = $request->header['sec-websocket-key'];
			$patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
			if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
				return false;
			}

			$psr7Request = Psr7Request::loadFromSwooleRequest($request);
			$psr7Response = Psr7Response::loadFromSwooleResponse($response);
			App::getApp()->getContext()->setRequest($psr7Request);
			App::getApp()->getContext()->setResponse($psr7Response);

			if (ievent(ServerEvent::ON_USER_BEFORE_HAND_SHAKE, [$psr7Request], true) === false) {
				return false;
			}
			ievent(ServerEvent::ON_OPEN, [App::$server->getServer(), $psr7Request]);

			$key = base64_encode(sha1(
				$request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
				true
			));
			$headers = [
				'Upgrade' => 'websocket',
				'Connection' => 'Upgrade',
				'Sec-WebSocket-Accept' => $key,
				'Sec-WebSocket-Version' => '13',
			];
			if (isset($request->header['sec-websocket-protocol'])) {
				$headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
			}

			foreach ($headers as $key => $val) {
				$response->header($key, $val);
			}
			foreach (App::getApp()->getContext()->getResponse()->getHeaders() as $key => $val) {
				$response->header($key, implode(';', $val));
			}

			$response->status(101);
		} catch (\Throwable $e) {
			ilogger()->debug('websocket handshake fail with error ' . $e->getMessage());
		} finally {
			$response->end();
		}
	}
}
