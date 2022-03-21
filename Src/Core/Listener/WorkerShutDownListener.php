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

namespace W7\Core\Listener;

use W7\App;
use W7\Core\Server\ServerEvent;

class WorkerShutDownListener extends ListenerAbstract {
	public function run(...$params) {
		$this->log($params[1]);
		$startedServers = $this->getConfig()->get('app.setting.started_servers', [App::$server->getType()]);
		foreach ($startedServers as $startedServer) {
			$listener = sprintf('\\W7\\%s\\Listener\\%sListener', ucfirst($startedServer), ucfirst(ServerEvent::ON_USER_AFTER_WORKER_SHUTDOWN));
			class_exists($listener) && $this->getEventDispatcher()->listen(ServerEvent::ON_USER_AFTER_WORKER_SHUTDOWN, $listener);
		}

		$this->getEventDispatcher()->dispatch(ServerEvent::ON_USER_AFTER_WORKER_SHUTDOWN, $params);
	}

	protected function log(\Throwable $throwable) {
		$this->getContext()->setContextDataByKey('workid', App::$server->getServer()->worker_id);
		$this->getContext()->setContextDataByKey('coid', $this->getContext()->getLastCoId());
		$errorMessage = sprintf(
			'Uncaught Exception %s: "%s" at %s line %s',
			get_class($throwable),
			$throwable->getMessage(),
			$throwable->getFile(),
			$throwable->getLine()
		);

		$context = [];
		if ((ENV & BACKTRACE) === BACKTRACE) {
			$context = array('exception' => $throwable);
		}

		$this->getLogger()->debug($errorMessage, $context);
	}
}
