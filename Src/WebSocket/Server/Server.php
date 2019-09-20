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

namespace W7\WebSocket\Server;

use Swoole\WebSocket\Server as WebSocketServer;
use W7\Core\Server\ServerAbstract;
use W7\Core\Server\ServerEnum;
use W7\Core\Server\SwooleEvent;
use W7\WebSocket\Message\Message;
use W7\WebSocket\Parser\ParserInterface;

class Server extends ServerAbstract {
	public function getType() {
		return ServerEnum::TYPE_WEBSOCKET;
	}

	public function start() {
		if ($this->setting['dispatch_mode'] == 1 || $this->setting['dispatch_mode'] == 3) {
			throw new \RuntimeException('not support the dispatch mode, please reset config/server.php/common/dispatch_mode');
		}
		$this->server = $this->getServer();
		$this->setting['open_websocket_close_frame'] = false;
		$this->server->set($this->setting);

		//执行一些公共操作，注册事件等
		$this->registerService();

		ievent(SwooleEvent::ON_USER_BEFORE_START, [$this->server]);

		$this->server->start();
	}

	/**
	 * @var \Swoole\Server $server
	 * 通过侦听端口的方法创建服务
	 */
	public function listener($server) {
		$tcpServer = $server->addListener($this->connection['host'], $this->connection['port'], $this->connection['sock_type']);
		//tcp需要强制关闭其它协议支持，否则继续父服务
		$tcpServer->set([
			'open_http2_protocol' => false,
			'open_http_protocol' => false,
			'open_websocket_protocol' => true
		]);
		$event = (new SwooleEvent())->getDefaultEvent()[$this->getType()];
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			$object = \iloader()->get($class);
			$tcpServer->on($eventName, [$object, 'run']);
		}
	}

	public function getServer() {
		if (empty($this->server)) {
			$this->server = new WebSocketServer($this->connection['host'], $this->connection['port'], $this->connection['mode'], $this->connection['sock_type']);
		}
		return $this->server;
	}

	public function sendTo($fd, Message $message, $opcode = WEBSOCKET_OPCODE_TEXT) {
		if (!$this->server->isEstablished($fd)) {
			return false;
		}
		//parse 待定
		$this->server->push($fd, iloader()->get(ParserInterface::class)->encode($message), $opcode);
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
			$fdList = (array)$this->server->getClientList($startFd, $pageSize);
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
		if ($this->server->isEstablished($fd)) {
			return $this->server->disconnect($fd, $code, $reason);
		}

		return true;
	}
}
