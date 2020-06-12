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

namespace W7\Fpm\Session\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Middleware\MiddlewareAbstract;
use W7\Core\Session\Session;

class SessionMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$request->session = Container::clone(Session::class);
		$request->session->start($request);

		//第二个参数表示shudown后，保存session数据并执行close，释放session锁，不释放会导致同一个sessionid的请求处于等待状态(session_start被调用的时候，该文件是被锁住的)
		session_set_save_handler($request->session->getHandler(), true);
		//执行session_start才能触发php默认的gc
		//启动”session_start” 会自动执行,open,read函数，然后页面执行完，会执行shutdown函数，最后会把session写入进去，然后执行close关闭文件
		session_start();

		Context::setResponse($request->session->replenishResponse(Context::getResponse()));

		return $handler->handle($request);
	}
}
