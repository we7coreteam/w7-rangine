<?php
/**
 * @author donknap
 * @date 18-7-24 下午5:31
 */

namespace W7\Http\Server;

use W7\Core\Base\DispatcherAbstract;
use W7\Core\Helper\Context;

class Dispather extends DispatcherAbstract {

	public $lastMiddleware = \W7\Http\Middleware\RequestMiddleware::class;

	public function dispatch(...$params) {
		list($request, $response) = $params;

		$psr7Request = \w7\Http\Message\Server\Request::loadFromSwooleRequest($request);
		$psr7Response = new \w7\Http\Message\Server\Response($response);

		Context::setRequest($psr7Request);
		Context::setResponse($psr7Response);

		$routes = iconfig()->getUserConfig("route");

		//根据router配置，获取到全部中间件数据，最后附加Http组件的中间件，用于处理调用Controller

	}

	/**
	 * 通过route信息，调用具体的Controller
	 */
	public function handler() {

	}
}