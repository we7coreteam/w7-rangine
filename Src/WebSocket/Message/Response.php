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

	public function json($data = [], int $status = 200, int $encodingOptions = JSON_UNESCAPED_UNICODE): \W7\Http\Message\Server\Response {
		$response = $this;

		// Content
		if ((is_numeric($data)) || is_string($data)) {
			$content = $data;
		} elseif ($this->isArrayable($data)) {
			$content = json_encode($data, $encodingOptions);
		} else {
			$content = '{}';
		}

		return $response->withContent($content)->withStatus($status);
	}

	public function send() {
		$body = json_decode($this->getBody()->getContents(), true);
		if ($body === null) {
			$body = $this->getBody()->getContents();
		}
		App::$server->sendTo($this->getFd(), new Message($this->getFrame()->getMessage()->getCmd(), $body, $this->getStatusCode()));
	}
}
