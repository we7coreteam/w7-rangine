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

use W7\App;
use Psr\Http\Message\ResponseInterface;
use W7\Http\Message\Server\Response;

abstract class ResponseExceptionAbstract extends \LogicException {
	/**
	 * 该类异常是否需要写入日志
	 * @var bool
	 */
	public $isLoggable = true;
	protected $response;

	public function __construct($message = '', $code = 0, \Throwable $previous = null) {
		parent::__construct($message, (int)$code, $previous);

		$response = App::getApp()->getContext()->getResponse();
		if (empty($response)) {
			trigger_error("Invalid Http Response object.", E_USER_ERROR);
		}

		$this->response = $response;
	}

	abstract public function render() : ResponseInterface;
}
