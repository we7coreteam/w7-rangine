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

namespace W7\Fpm\Server;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use W7\App;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleEvent;

class Server extends ServerAbstract {
	public static $masterServer = false;
	public $worker_id;

	public function __construct() {
		!App::$server && App::$server = $this;
		$this->server = $this;
		$this->worker_id = getmypid();
	}

	public function getType() {
		return ServerEnum::TYPE_FPM;
	}

	protected function registerServerEventListener() {
		$eventTypes = [$this->getType()];
		iloader()->get(SwooleEvent::class)->register($eventTypes);
	}

	public function start() {
		$this->registerService();
		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this]);

		$request = Request::createFromGlobals();
		$response = Response::create();

		ievent(SwooleEvent::ON_USER_AFTER_WORKER_START, [$this, 0]);
		ievent(SwooleEvent::ON_REQUEST, [$this, $request, $response]);
	}

	public function getServer() {
		return $this->server;
	}
}
