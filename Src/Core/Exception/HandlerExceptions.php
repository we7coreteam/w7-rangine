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
use Psr\Http\Message\ResponseInterface;
use W7\App;
use W7\Core\Exception\Handler\ExceptionHandler;
use W7\Core\Exception\Handler\HandlerAbstract;

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
	public function registerErrorHandle(): void {
		set_error_handler([$this, 'handleError'], error_reporting());
		set_exception_handler([$this, 'handleException']);
		register_shutdown_function(function () {
			if (!$e = error_get_last()) {
				return;
			}

			$this->handleError($e['type'], $e['message'], $e['file'], $e['line']);
		});
	}

	/**
	 * @param int $num
	 * @param string $str
	 * @param string $file
	 * @param int $line
	 * @throws \ErrorException
	 */
	public function handleError(int $type, string $message, string $file, int $line): void {
		throw new \ErrorException($message, 0, $type, $file, $line);
	}

	public function log(\Throwable $throwable) {
		$errorMessage = sprintf(
			'Uncaught Exception %s: "%s" at %s line %s',
			get_class($throwable),
			$throwable->getMessage(),
			$throwable->getFile(),
			$throwable->getLine()
		);

		$context = [];
		if ((ENV & BACKTRACE) === BACKTRACE) {
			$context = array('exception' => $throwable);
		}

		ilogger()->error($errorMessage, $context);
	}

	/**
	 * @param \Throwable $throwable
	 */
	public function handleException(\Throwable $throwable): void {
		$this->handle($throwable);
	}

	public function handle(\Throwable $throwable) : ResponseInterface {
		$previous = $throwable;
		if (!($throwable instanceof ResponseExceptionAbstract)) {
			$class = 'W7\Core\Exception\\' . ucfirst(App::$server->getType()) . 'FatalException';
			$throwable = new $class($throwable->getMessage(), $throwable->getCode(), $throwable);
		}

		if ($throwable->isLoggable) {
			$this->log($previous);
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
	public function setHandler(HandlerAbstract $handler): void {
		$this->handler = $handler;
	}
}
