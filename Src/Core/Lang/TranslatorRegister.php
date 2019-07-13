<?php

namespace W7\Core\Lang;

use Illuminate\Translation\Translator;
use W7\Core\Service\ServiceAbstract;

class TranslatorRegister extends ServiceAbstract {
	public function register() {
		iloader()->set('translator', function () {
			return new Translator($this->getFileLoader(), 'zh-CN');
		});
	}

	private function getFileLoader() {
		$paths = [
			BASE_PATH . '/vendor/caouecs/laravel-lang/src/',
			BASE_PATH . '/config/lang/'
		];

		$loader = new FileLoader(iloader()->get('filesystem'), '', $paths);
		if (\is_callable([$loader, 'addJsonPath'])) {
			$loader->addJsonPath(BASE_PATH . '/vendor/caouecs/laravel-lang/json/');
			$loader->addJsonPath(BASE_PATH . '/config/lang/json/');
		}
		return $loader;
	}
}