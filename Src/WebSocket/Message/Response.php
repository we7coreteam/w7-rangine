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
use W7\WebSocket\Parser\ParserInterface;

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

	//这里的处理逻辑不是很合理，后续做修改
	public function withData($data) {
		if ($data instanceof Message) {
			$this->data = $data;
			return $this;
		} else {
			return parent::withData($data);
		}
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
		if (!($this->data instanceof Message)) {
			$this->data = new Message($this->getFrame()->getMessage()->getCmd(), $this->data, $this->getStatusCode());
		}
		$this->sendTo($this->getFd(), $this->data, $this->getOpcode());
	}

	public function sendTo($fd, Message $message, $opcode = WEBSOCKET_OPCODE_TEXT) {
		if (!App::$server->getServer()->isEstablished($fd)) {
			return false;
		}
		//parse 待定
		App::$server->getServer()->push($fd, iloader()->get(ParserInterface::class)->encode($message), $opcode);
	}

	public function sendToSome(array $fds, Message $message, $opcode = WEBSOCKET_OPCODE_TEXT) {
		foreach ($fds as $fd) {
			$this->sendTo($fd, $message, $opcode);
		}
	}

	public function sendToAll(Message $message, $opcode = WEBSOCKET_OPCODE_TEXT) {
		$this->pageEach(function ($fd) use ($message, $opcode) {
			$this->sendTo($fd, $message, $opcode);
		});
	}

	/**
	 * Pagination traverse all valid WS connection
	 *
	 * @param callable $handler
	 * @param int      $pageSize
	 *
	 * @return int
	 */
	public function pageEach(callable $handler, $pageSize = 50): int {
		$count = $startFd = 0;

		while (true) {
			$fdList = (array)App::$server->getServer()->getClientList($startFd, $pageSize);
			if (($num = count($fdList)) === 0) {
				break;
			}

			$count += $num;

			/** @var $fdList array */
			foreach ($fdList as $fd) {
				$handler($fd);
			}

			// It's last page.
			if ($num < $pageSize) {
				break;
			}

			// Get start fd for next page.
			$startFd = end($fdList);
		}

		return $count;
	}

	public function disconnect($fd, $code = 0, $reason = ''): bool {
		if (App::$server->getServer()->isEstablished($fd)) {
			return App::$server->getServer()->disconnect($fd, $code, $reason);
		}

		return true;
	}
}
