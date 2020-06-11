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

namespace W7\Core\Facades;

use W7\Core\Log\LogManager;

/**
 * Class Logger
 * @package W7\Core\Facades
 *
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static \Psr\Log\LoggerInterface channel(string $channel = null)
 *
 * @see \W7\Core\Log\Logger
 */
class Logger extends FacadeAbstract {
	protected static function getFacadeAccessor() {
		return LogManager::class;
	}

	public static function getFacadeRoot() {
		/**
		 * @var LogManager $root
		 */
		$root = parent::getFacadeRoot();
		return $root->getDefaultChannel();
	}
}
