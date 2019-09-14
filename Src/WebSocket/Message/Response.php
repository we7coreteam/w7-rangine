<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\WebSocket\Message;

use W7\App;
use W7\Http\Message\Server\Response as Psr7Response;

class Response extends Psr7Response {
	/**
	 * @var Frame
	 */
	private $frame;

	public static function loadFromWebSocketFrame(Frame $frame): self {
		$response = new static();
		$response->frame = $frame;

		return $response;
	}

	/**
	 * @return int
	 */
	public function getFd(): int {
		return $this->frame->getFd();
	}

	/**
	 * @return int
	 */
	public function getOpcode(): int {
		return $this->frame->getOpcode();
	}

	/**
	 * @return Frame
	 */
	public function getFrame(): Frame {
		return $this->frame;
	}

	public function send() {
		if (!$this->data) {
			$this->data = $this->getBody()->getContents();
		}
		App::$server->sendTo($this->getFd(), new Message($this->getFrame()->getMessage()->getCmd(), $this->data, $this->getStatusCode()));
	}
}
