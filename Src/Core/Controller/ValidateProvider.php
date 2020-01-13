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

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use W7\Core\Provider\ProviderAbstract;

class ValidateProvider extends ProviderAbstract {
	public function register() {
		$this->registerLoader();
		$this->registerTranslator();
		$this->registerFactory();
	}

	public function registerLoader() {
		iloader()->set('validate.loader', function () {
			return new ArrayLoader();
		});
	}

	public function registerTranslator() {
		iloader()->set('validate.translator', function () {
			$config = iconfig()->getUserAppConfig('setting');
			return new Translator(iloader()->get('validate.loader'), $config['lang'] ?? 'zh-CN');
		});
	}

	public function registerFactory() {
		iloader()->set(Factory::class, function () {
			/**
			 * @var Translator $translator
			 */
			$translator = iloader()->get('validate.translator');
			$validate = new Factory($translator);
			$validate->setPresenceVerifier(new DatabasePresenceVerifier(idb()));
			return $validate;
		});
	}
}
