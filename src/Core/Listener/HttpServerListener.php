<?php
/**
 * @author donknap
 * @date 18-7-21 上午11:08
 */

namespace W7\Core\Listener;

use Swoole\Http\Request;
use Swoole\Http\Response;

class HttpServerListener {
	public function onRequest(Request $request, Response $response) {
		print_r($request);
	}
}