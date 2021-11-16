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

namespace W7\Core\Controller;

use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

class FaviconController extends ControllerAbstract {
	public function index(Request $request): Response {
		return $this->responseRaw('');
	}
}
