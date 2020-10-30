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

use Swoole\Coroutine;
use W7\App;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Facades\Logger;
use Swoole\Timer;

if (!function_exists('isCli')) {
	function isCli() {
		return PHP_SAPI == 'cli';
	}
}

if (!function_exists('getClientIp')) {
	function getClientIp() {
		$request = Context::getRequest();
		$serverParams = $request->getServerParams();
		$xForwardedFor = !empty($serverParams['HTTP_X_FORWARDED_FOR']) ? $serverParams['HTTP_X_FORWARDED_FOR'] : ($request->getHeader('X-Forwarded-For')[0] ?? '');

		if (!empty($xForwardedFor)) {
			$arr = explode(',', $xForwardedFor);
			$pos = array_search('unknown', $arr);
			if (false !== $pos) {
				unset($arr[$pos]);
			}
			$ip = trim($arr[0]);
		} elseif (!empty($serverParams['HTTP_CLIENT_IP'])) {
			$ip = $serverParams['HTTP_CLIENT_IP'];
		} elseif ($request->hasHeader('X-Real-IP')) {
			$ip = $request->getHeader('X-Real-IP')[0];
		} elseif (!empty($serverParams['REMOTE_ADDR'])) {
			$ip = $serverParams['REMOTE_ADDR'];
		} else {
			$ip = $request->getSwooleRequest()->server['remote_addr'];
		}

		return $ip;
	}
}

if (!function_exists('ienv')) {
	function ienv($key, $default = null) {
		$value = getenv($key);

		if ($value === false) {
			return value($default);
		}

		switch (strtolower($value)) {
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}

		//约定如要env中要写常量名称的话必须要大写
		if (preg_match('/([A-Z]+[\|\^\&])+/', $value)) {
			//常量解析
			$exec = 'return ' . $value . ';';
			try {
				$result = @eval($exec);
				if ($result !== false && $result !== "\0\0\0\0\0\0") {
					$value = $result;
				}
			} catch (Throwable $e) {
				//
			}
		}
		if (defined($value)) {
			$value = constant($value);
		}

		if (is_string($value) && ($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
			return substr($value, 1, -1);
		}

		return $value;
	}
}

if (!function_exists('isCo')) {
	/**
	 * 是否是在协成
	 * @return bool
	 */
	function isCo():bool {
		return Context::getCoroutineId() > 0;
	}
}

if (!function_exists('isWorkerStatus')) {
	function isWorkerStatus() {
		if (App::$server === null) {
			return false;
		}

		$server = App::$server->getServer();
		if (empty($server->manager_pid) || $server->manager_pid == 0) {
			return false;
		}
		if ($server && \property_exists($server, 'taskworker') && ($server->taskworker === false)) {
			return true;
		}

		return false;
	}
}

if (!function_exists('isetProcessTitle')) {
	function isetProcessTitle($title) {
		if (\stripos(PHP_OS, 'Darwin') !== false) {
			return true;
		}
		if (\function_exists('cli_set_process_title')) {
			return cli_set_process_title($title);
		}

		if (\function_exists('swoole_set_process_name')) {
			return swoole_set_process_name($title);
		}
		return true;
	}
}

if (!function_exists('igo')) {
	function igo(Closure $callback) {
		if (!isCo()) {
			$generatorFunc = function () use ($callback) {
				try {
					yield $callback();
				} catch (Throwable $e) {
					Logger::debug($e->getMessage(), ['exception' => $e]);
				}
			};
			Container::singleton(\W7\Core\Helper\Compate\CgiCoroutine::class)->add($generatorFunc());
			return true;
		}

		$coId =Context::getCoroutineId();
		$result = null;
		Coroutine::create(function () use ($callback, $coId, &$result) {
			Context::fork($coId);
			try {
				$result = $callback();
			} catch (Throwable $throwable) {
				Logger::debug('igo error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				Context::destroy();
			});
		});
		return $result;
	}
}

if (!function_exists('isleep')) {
	function isleep($seconds) {
		if (!isCo()) {
			sleep($seconds);
			return true;
		}
		\Swoole\Coroutine\System::sleep($seconds);
	}
}

if (!function_exists('itimeTick')) {
	function itimeTick($ms, \Closure $callback) {
		return Timer::tick($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				Logger::debug('timer-tick error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				Context::destroy();
			});
		});
	}
}

if (!function_exists('itimeAfter')) {
	function itimeAfter($ms, \Closure $callback) {
		return Timer::after($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				Logger::debug('time-after error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				Context::destroy();
			});
		});
	}
}
