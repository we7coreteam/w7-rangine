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

namespace W7\Core\Provider\Listener;

use W7\Core\Container\Event\AttributeNotExistsEvent;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Provider\ProviderManager;

class AttributeNotExistsListener extends ListenerAbstract {
	public function run(...$params) {
		/**
		 * @var AttributeNotExistsEvent $attributeNotExistsEvent
		 */
		$attributeNotExistsEvent = $params[0];

		/**
		 * @var ProviderManager $providerManager
		 */
		$providerManager = icontainer()->singleton(ProviderManager::class);
		$provider = $providerManager->getDependDeferredProvider($attributeNotExistsEvent->name);
		if ($provider && !$providerManager->hasRegister($provider)) {
			$provider = $providerManager->registerProvider($provider);
			$provider && $providerManager->bootProvider($provider);
		}
	}
}
