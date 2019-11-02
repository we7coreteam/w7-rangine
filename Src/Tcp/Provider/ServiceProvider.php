<?php

namespace W7\ThriftRpc;

use W7\Core\Log\LogManager;
use W7\Core\Provider\ProviderAbstract;

class ServiceProvider extends ProviderAbstract{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->registerLog();
	}

	private function registerLog() {
		if (!empty($this->config->getUserConfig('log')['channel']['json-rpc'])) {
			return false;
		}
		/**
		 * @var LogManager $logManager
		 */
		$logManager = iloader()->get(LogManager::class);
		$logManager->addChannel('json-rpc', 'stream', [
			'path' => RUNTIME_PATH . '/logs/json-rpc.log',
			'level' => ienv('LOG_CHANNEL_JSON_RPC_LEVEL', 'debug'),
		]);
	}
}
