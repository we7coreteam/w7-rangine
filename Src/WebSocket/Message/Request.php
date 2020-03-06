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

use function GuzzleHttp\Psr7\parse_query;
use W7\Http\Message\Server\Request as Psr7Request;

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
		$request = new static('POST', $frame->getMessage()->getCmd());
		$request->frame = $frame;

		return $request->withQueryParams(parse_query($request->getUri()->getQuery()))->withParsedBody($frame->getMessage()->getData());
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
