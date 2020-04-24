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

namespace W7\Core\Listener\User;

use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

abstract class RequestListenerAbstract extends UserListenerAbstract {
	/**
	 * @var Request
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;

	public function __construct(...$params) {
		$this->request = $params[0];
		$this->response = $params[1];
		$this->serverType = $params[2];
	}
}
