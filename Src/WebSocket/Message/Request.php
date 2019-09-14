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

use W7\Http\Message\Server\Request as Psr7Request;
use W7\Http\Message\Stream\SwooleStream;

class Request extends Psr7Request {
	/**
	 * @var Frame
	 */
	private $frame;

	/**
	 * @param Frame $frame
	 * @return Request
	 */
	public static function loadFromWebSocketFrame(Frame $frame): self {
		$body = new SwooleStream();
		$protocol = 'HTTP/1.1';
		$request = new static('POST', $frame->getMessage()->getCmd(), [], $body, $protocol);
		$request->frame = $frame;
		return $request->withParsedBody($frame->getMessage()->getData());
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
}
