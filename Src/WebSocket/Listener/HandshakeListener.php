<?php

namespace W7\WebSocket\Listener;


use Swoole\Http\Request;
use Swoole\Http\Response;
use W7\App;
use W7\Core\Config\Event;
use W7\Core\Listener\ListenerAbstract;


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
    	var_dump($request);
	    // websocket握手连接算法验证
	    $secWebSocketKey = $request->header['sec-websocket-key'];
	    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
	    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
		    $response->end();
		    return false;
	    }

	    if (!ievent(Event::ON_USER_HAND_SHAKE, [$request, $response])) {
		    $response->end();
		    return false;
	    }

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

	    $response->status(101);
	    $response->end();

	    App::$server->getServer()->defer(function () use ($request) {
		    (new OpenListener())->run(App::$server->getServer(), $request);
		    //ievent目前不能直接触发类似Event::ON_OPEN的事件
//	    	ievent(Event::ON_USER_OPEN, [App::$server->getServer(), $request]);
	    });
    }
}
