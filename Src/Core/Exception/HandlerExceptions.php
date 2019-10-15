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

class HandlerExceptions {
	/**
	 * @var HandlerAbstract
	 */
	private $handler;

	private $canThrowException = true;

	private $errorLevel;

	/**
	 * Register system error handle
	 *
	 * @throws InvalidArgumentException
	 */
	public function registerErrorHandle() {
		$this->errorLevel = error_reporting();
		set_error_handler([$this, 'handleError']);
		set_exception_handler([$this, 'handleException']);
		register_shutdown_function(function () {
			if (!$e = error_get_last()) {
				return;
			}

			$this->canThrowException = false;
			$this->handleError($e['type'], $e['message'], $e['file'], $e['line']);
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
		//这里不用error_reporting直接获取的原因是，当使用@触发异常时，取到的值是0
		if ($type === ($type & $this->errorLevel)) {
			$throwable = new \ErrorException($message, 0, $type, $file, $line);
			if ($this->canThrowException) {
				throw $throwable;
			} else {
				$this->canThrowException = true;
				$this->handleException($throwable);
				return true;
			}
		}

		return false;
	}

	/**
	 * @param \Throwable $throwable
	 */
	public function handleException(\Throwable $throwable) {
		$this->handle($throwable);
	}

	public function handle(\Throwable $throwable) {
		if (!($throwable instanceof ResponseExceptionAbstract)) {
			$class = 'W7\\' . ucfirst(App::$server->type) . '\Exception\FatalException';
			$throwable = new $class($throwable->getMessage(), $throwable->getCode(), $throwable);
		}

		return $this->getHandler()->handle($throwable);
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
}
