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

namespace W7\Core\Provider;

use Illuminate\Pagination\Paginator;
use W7\Core\Facades\Context;

class PaginationServiceProvider extends ProviderAbstract {
	public function register() {
		Paginator::currentPathResolver(function () {
			return rtrim(preg_replace('/\?.*/', '', Context::getRequest()->getUri()->getPath()), '/');
		});

		Paginator::currentPageResolver(function ($pageName = 'page') {
			$page = Context::getRequest()->input($pageName, 1);
			if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int)$page >= 1) {
				return (int)$page;
			}
			return 1;
		});
	}
}
