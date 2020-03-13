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

namespace W7\Fpm\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Fpm\Compat\Globals\Cookie;
use W7\Fpm\Compat\Globals\File;
use W7\Fpm\Compat\Globals\Get;
use W7\Fpm\Compat\Globals\Post;
use W7\Fpm\Compat\Globals\Server;
use W7\Fpm\Compat\Globals\Session;

class AfterWorkerStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->registerProxy();
	}

	private function registerProxy() {
		return false;
		//有兼容问题
		$_COOKIE = new Cookie();
		$_FILES = new File();
		$_GET = new Get();
		$_POST = new Post();
		$_SERVER = new Server();
		$_SESSION = new Session();
	}
}
