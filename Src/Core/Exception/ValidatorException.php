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

class ValidatorException extends ResponseExceptionAbstract {
	/**
	 * @throws \JsonException
	 */
	public function __construct($message = '', $code = 0, \Throwable $previous = null) {
		parent::__construct(json_encode(['error' => $message], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $code, $previous);
	}
}
