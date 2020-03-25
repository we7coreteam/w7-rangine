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

namespace W7\Core\Exception;

use InvalidArgumentException;
use W7\App;
use W7\Core\Exception\Handler\ExceptionHandler;
use W7\Core\Exception\Handler\HandlerAbstract;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Outputer\DefaultResponseOutputer;
use W7\Http\Message\Server\Response;
use W7\Http\Message\Server\Response as Psr7Response;

class HandlerExceptions {
	/**
	 * @var HandlerAbstract
	 */
	private $handler;

	/**
	 * Register system error handle
	 *
	 * @throws InvalidArgumentException
	 */
	public function registerErrorHandle() {
		set_error_handler([$this, 'handleError']);
		set_exception_handler([$this, 'handleException']);

		register_shutdown_function(function () {
			$e = error_get_last();
			if (!$e || !in_array($e['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])) {
				return;
			}

			$throwable = new ShutDownException($e['message'], 0, $e['type'], $e['file'], $e['line']);
			if (App::$server && App::$server->server) {
				ievent(ServerEvent::ON_WORKER_SHUTDOWN, [App::$server->getServer(), $throwable]);
				ievent(ServerEvent::ON_WORKER_STOP, [App::$server->getServer(), App::$server->getServer()->worker_id]);
			} else {
				throw $throwable;
			}
		});
	}

	/**
	 * @param int $type
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 * @return bool
	 * @throws \ErrorException
	 */
	public function handleError(int $type, string $message, string $file, int $line) {
		if (error_reporting() & $type) {
			$throwable = new \ErrorException($message, 0, $type, $file, $line);
			throw $throwable;
		}

		return false;
	}

	public function handleException(\Throwable $throwable) {
		return $this->handle($throwable);
	}

	/**
	 * @return HandlerAbstract
	 */
	public function getHandler(): HandlerAbstract {
		if (!$this->handler) {
			$this->handler = new ExceptionHandler();
		}
		return $this->handler;
	}

	/**
	 * @param HandlerAbstract $handler
	 */
	public function setHandler(HandlerAbstract $handler) {
		$this->handler = $handler;
	}

	public function handle(\Throwable $throwable, $serverType = null) {
		$response = App::getApp()->getContext()->getResponse();
		if (empty($response) || !($response instanceof Response)) {
			$response = new Psr7Response();
			$response->setOutputer(new DefaultResponseOutputer());
		}
		$handler = $this->getHandler();
		$handler->setServerType($serverType ?? App::$server->getType());
		$handler->setResponse($response);

		try {
			$handler->report($throwable);
		} catch (\Throwable $e) {
			null;
		}

		return $handler->handle($throwable);
	}
}
