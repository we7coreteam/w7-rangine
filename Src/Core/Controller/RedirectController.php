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

class RedirectController extends ControllerAbstract {
	public function index(Request $request, ...$params): Response {
		[$destination, $status] = $params;
		return $this->response()->redirect($destination, $status);
	}
}
