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

namespace W7\Core\Exception\Handler;

use W7\Core\Exception\ResponseExceptionAbstract;
use W7\Http\Message\Server\Response;

class ExceptionHandler extends HandlerAbstract {
	/**
	 * @param \Throwable $e
	 * @return Response
	 */
	protected function handleRelease(\Throwable $e) : Response {
		$this->log($e);

		// ResponseExceptionAbstract 为特殊的异常，此异常不管何时都将反馈给客户端
		if ($e instanceof ResponseExceptionAbstract) {
			return $this->getResponse()->withStatus($e->getCode() ?? '500')->withHeader('Content-Type', 'text/html')->withContent(\json_encode(['error' => $e->getMessage()]));
		} else {
			return $this->getResponse()->withStatus(500)->withHeader('Content-Type', 'text/html')->withContent(\json_encode(['error' => '系统内部错误']));
		}
	}

	/**
	 * 处理异常时将按照服务各自定义的FatalException异常来再次包装错误信息
	 * @param \Throwable $e
	 * @return Response
	 */
	protected function handleDevelopment(\Throwable $e) : Response {
		$this->log($e);

		$class = $this->getServerFatalExceptionClass();
		$error = new $class($e->getMessage(), $e->getCode(), $e);
		return $this->getResponse()->withStatus(500)->withHeader('Content-Type', 'text/html')->withContent($error->getMessage());
	}
}
