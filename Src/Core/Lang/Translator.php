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

namespace W7\Core\Lang;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator as TranslatorAbstract;

class Translator extends TranslatorAbstract {
	public function __construct() {
		parent::__construct($this->getFileLoader(), $this->getLocale());
	}

	private function getFileLoader() {
		$paths = [
			BASE_PATH . '/vendor/caouecs/laravel-lang/src/',
			BASE_PATH . '/config/lang/'
		];

		$loader = new FileLoader(new Filesystem(), '', $paths);
		if (\is_callable([$loader, 'addJsonPath'])) {
			$loader->addJsonPath(BASE_PATH . '/vendor/caouecs/laravel-lang/json/');
			$loader->addJsonPath(BASE_PATH . '/config/lang/json/');
		}
		return $loader;
	}

	public function getLocale() {
		$config = iconfig()->getUserAppConfig('setting');
		return $config['lang'] ?? 'zh-CN';
	}
}
