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
	public function getHandlerClassByType($type, $handlerName) {
		$config = iconfig()->getUserConfig('handler')[$type] ?? [];
		$handlerClass = $config[$handlerName] ?? '';
		if (!$handlerClass || $handlerClass && !class_exists($handlerClass)) {
			throw new \RuntimeException($type . ' handler ' . $handlerName . ' is not support');
		}

		return $handlerClass;
	}
}
