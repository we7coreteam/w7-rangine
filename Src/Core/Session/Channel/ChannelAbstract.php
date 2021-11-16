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

namespace W7\Core\Session\Channel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class ChannelAbstract {
	protected array $config;
	protected static string $sessionName;
	protected ServerRequestInterface $request;

	public function __construct(array $config, ServerRequestInterface $request) {
		$this->config = $config;
		$this->request = $request;
	}

	public function getSessionName(): string {
		if (!static::$sessionName) {
			static::$sessionName = $this->config['name'] ?? session_name();
		}
		return static::$sessionName;
	}

	abstract public function getSessionId() : string;
	abstract public function replenishResponse(ResponseInterface $response, $sessionId) : ResponseInterface;
}
