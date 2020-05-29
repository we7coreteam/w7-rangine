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

namespace W7\Core\Helper\Traiter;

trait HandlerTrait {
	public static $classCache = [];

	public function getHandlerClassByTypeAndName($type, $handlerName) {
		if (!empty(self::$classCache[$type][$handlerName])) {
			return self::$classCache[$type][$handlerName];
		}

		$config = iconfig()->get("handler.$type", []);
		$handlerClass = $config[$handlerName] ?? '';
		if (!$handlerClass || !class_exists($handlerClass)) {
			throw new \RuntimeException($type . ' handler ' . $handlerName . ' is not support');
		}
		self::$classCache[$type][$handlerName] = $handlerClass;

		return $handlerClass;
	}
}
