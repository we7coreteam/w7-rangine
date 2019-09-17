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

namespace W7\Core\View;

use W7\Core\Process\ReloadProcess;
use W7\Core\Provider\ProviderAbstract;

class ViewProvider extends ProviderAbstract {
	public function register() {
		ReloadProcess::addType(iloader()->singleton(View::class)->getSuffix());
		//该目录必须存在,provider是在注册了open base dir后才执行的, 所以这里不能对目录进行检测和重建
		ReloadProcess::addDir(BASE_PATH . '/view');
		//用户自定义目录
		$userTemplatePath = iconfig()->getUserAppConfig('view')['template_path'] ?? [];
		foreach ($userTemplatePath as $path) {
			$path = (array)$path;
			foreach ($path as $item) {
				ReloadProcess::addDir($item);
			}
		}
	}
}
