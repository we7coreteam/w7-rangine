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

class ExceptionHandle {
	private $exceptionMap = [
		'http' => HttpException::class,
		'http_dev' => HttpDevException::class,
		'http_release' => HttpReleaseException::class,
		'tcp' => TcpException::class,
		'tcp_dev' => TcpException::class,
		'tcp_release' => HttpReleaseException::class
	];
	private static $userExceptionMap = [];
	private $type;
	private $env;

	public function __construct($type) {
		$this->type = $type;
		$this->env = 'release';
		if ((ENV & DEBUG) === DEBUG) {
			$this->env = 'dev';
		}
	}

	public static function registerUserExceptionMap($map) {
		static::$userExceptionMap = $map;
	}

	private function getRealException($name) {
		if (!empty(static::$userExceptionMap[$name])) {
			return static::$userExceptionMap[$name];
		}

		return $name;
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

	public function handle(\Throwable $throwable) {
		$previous = $throwable;
		if (!($throwable instanceof ResponseExceptionAbstract)) {
			$exception = $this->exceptionMap[$this->type . '_' . $this->env];
			$exception = $this->getRealException($exception);
			$throwable = new $exception($throwable->getMessage(), $throwable->getCode(), $throwable);
		} elseif (!empty(static::$userExceptionMap[get_class($throwable)])) {
			$userException =  $this->getRealException(get_class($throwable));
			$throwable = new $userException($throwable->getMessage(), $throwable->getCode(), $throwable->getPrevious());
		}

		if ($throwable->isLoggable) {
			$this->log($previous);
		}
		return $throwable->render();
	}

	public function registerException($type, $class) {
		$this->exceptionMap[$type] = $class;
	}
}
