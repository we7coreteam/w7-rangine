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

use Throwable;

abstract class ExceptionAbstract extends \LogicException {
	/**
	 * 该类异常是否需要写入日志
	 * @var bool
	 */
	public $isLoggable = true;

	public function __construct($message = '', $code = 0, Throwable $previous = null) {
		parent::__construct($message, (int)$code, $previous);
	}

	abstract public function render();
}
