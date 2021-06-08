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
use Throwable;
use W7\App;
use W7\Contract\Logger\LoggerFactoryInterface;

class FpmHelper {
	public static function createCoroutine(Closure $callback) {
		$generatorFunc = function () use ($callback) {
			try {
				yield $callback();
			} catch (Throwable $e) {
				App::getApp()->getContainer()->get(LoggerFactoryInterface::class)->debug($e->getMessage(), ['exception' => $e]);
			}
		};
		App::getApp()->getContainer()->get(\W7\Core\Helper\Compate\FpmCoroutine::class)->add($generatorFunc());
		return true;
	}

	public static function sleep($seconds) {
		sleep($seconds);
	}
}
