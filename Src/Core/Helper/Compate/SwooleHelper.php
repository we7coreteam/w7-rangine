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

namespace W7\Core\Helper\Compate;

use Closure;
use Exception;
use Swoole\Coroutine;
use Swoole\Timer;
use Throwable;
use W7\App;
use W7\Contract\Logger\LoggerFactoryInterface;
use W7\Core\Helper\Storage\Context;

class SwooleHelper {
	public static function checkLoadSwooleExtension($exitIfNotLoad = true):bool {
		static $hasLoadSwooleExtension = true;
		if ($hasLoadSwooleExtension && isCli() && extension_loaded('swoole') && version_compare(SWOOLE_VERSION, '4.4.0', '>=')) {
			$hasLoadSwooleExtension = true;
			return true;
		}

		$hasLoadSwooleExtension = false;
		if ($exitIfNotLoad) {
			throw new Exception('please check if the Swoole extension is installed and if the version is greater than 4.4.0');
		}

		return false;
	}

	/**
	 * @throws \ReflectionException
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public static function createCoroutine(Closure $callback) {
		$context = App::getApp()->getContainer()->get(Context::class);
		$coId = $context->getCoroutineId();

		return Coroutine::create(function () use ($callback, $coId, $context) {
			$context->fork($coId);

			try {
				$callback();
			} catch (Throwable $throwable) {
				App::getApp()->getContainer()->get(LoggerFactoryInterface::class)->debug('igo error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}
		});
	}

	public static function sleep($seconds): void {
		Coroutine\System::sleep($seconds);
	}

	/**
	 * @throws Exception
	 */
	public static function timeTick($ms, Closure $callback): int {
		self::checkLoadSwooleExtension();
		return Timer::tick($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				App::getApp()->getContainer()->get(LoggerFactoryInterface::class)->debug('timer-tick error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}
		});
	}

	/**
	 * @throws Exception
	 */
	public static function timeAfter($ms, Closure $callback): int {
		self::checkLoadSwooleExtension();
		return Timer::after($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				App::getApp()->getContainer()->get(LoggerFactoryInterface::class)->debug('time-after error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}
		});
	}
}
