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

namespace W7\Http\Listener;

use Illuminate\Contracts\Container\BindingResolutionException;
use W7\Core\Helper\Storage\Context;
use W7\Core\Listener\ListenerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Http\Message\Server\Response;

class AfterWorkerShutDownListener extends ListenerAbstract {
	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 */
	public function run(...$params) {
		$contexts = $this->getContext()->all();
		foreach ($contexts as $id => $context) {
			if (!empty($context[Context::RESPONSE_KEY]) && !empty($context['data']['server-type']) && $context['data']['server-type'] === ServerEnum::TYPE_HTTP) {
				/**
				 * @var Response $cResponse
				 */
				$cResponse = $context[Context::RESPONSE_KEY];
				$cResponse = $cResponse->withContent('发生致命错误，请在日志中查看错误原因，workid：' . ($context['data']['workid'] ?? '') . '，coid：' . $this->getContext()->getLastCoId() . '。');
				$cResponse->send();

				$this->getContext()->destroy($id);
			}
		}
	}
}
