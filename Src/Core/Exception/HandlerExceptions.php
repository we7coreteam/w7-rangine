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

use Illuminate\Support\Str;
use W7\App;
use W7\Console\Io\Output;
use W7\Core\Exception\Formatter\ExceptionFormatterInterface;
use W7\Core\Exception\Handler\ExceptionHandler;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Server\ServerEvent;
use W7\Http\Message\Server\Response;

class HandlerExceptions {
	use AppCommonTrait;

	protected $exceptionHandler = ExceptionHandler::class;

	public function setHandler(string $exceptionHandler) {
		$this->exceptionHandler = $exceptionHandler;
	}

	public function getHandler() : ExceptionHandler {
		$handler = $this->exceptionHandler;
		return new $handler();
	}

	public static function isIgnoreErrorTypes($errorType) {
		return !in_array($errorType, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
	}

	/**
	 * Register system error handle
	 */
	public function registerErrorHandle() {
		set_error_handler([$this, 'handleError']);
		set_exception_handler([$this, 'handleException']);

		register_shutdown_function(function () {
			$e = error_get_last();
			if (!$e || self::isIgnoreErrorTypes($e['type'])) {
				return;
			}

			$throwable = new ShutDownException($e['message'], 0, $e['type'], $e['file'], $e['line']);
			if (App::$server && App::$server->server) {
				$this->getEventDispatcher()->dispatch(ServerEvent::ON_WORKER_SHUTDOWN, [App::$server->getServer(), $throwable]);
				$this->getEventDispatcher()->dispatch(ServerEvent::ON_WORKER_STOP, [App::$server->getServer(), App::$server->getServer()->worker_id]);
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
			throw new \ErrorException($message, 0, $type, $file, $line);
		}

		return false;
	}

	public function handleException(\Throwable $throwable) {
		return $this->handle($throwable);
	}

	protected function getServerExceptionFormatter($serverType) : ExceptionFormatterInterface {
		$class = sprintf('W7\\%s\\Exception\\Formatter\\ExceptionFormatter', Str::studly($serverType));
		return new $class();
	}

	public function handle(\Throwable $throwable, $serverType = null) {
		$serverType = $serverType ?? (empty(App::$server) ? '' : App::$server->getType());
		if (!$serverType || !$this->getContext()->getResponse()) {
			if (isCli()) {
				(new Output())->error('message：' . $throwable->getMessage() . "\nfile：" . $throwable->getFile() . "\nline：" . $throwable->getLine());
			} else {
				throw $throwable;
			}
			return false;
		}

		$handler = $this->getHandler();
		try {
			$handler->report($throwable);
		} catch (\Throwable $e) {
		}

		$response = $this->getContext()->getResponse();
		if (!$response) {
			$response = new Response();
		}

		$handler->setServerType($serverType);
		$handler->setExceptionFormatter($this->getServerExceptionFormatter($serverType));
		$handler->setResponse($response);
		return $handler->handle($throwable);
	}
}
