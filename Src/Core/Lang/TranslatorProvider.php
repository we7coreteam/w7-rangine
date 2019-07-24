<?php

namespace W7\Core\Lang;

use Illuminate\Translation\Translator;
use W7\Core\Provider\ProviderAbstract;

class TranslatorProvider extends ProviderAbstract {
	public function register() {
		iloader()->set('translator', function () {
			$config = iconfig()->getUserAppConfig('setting');
			$lang = $config['lang'] ?? 'zh-CN';
			return new Translator($this->getFileLoader(), $lang);
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