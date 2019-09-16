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

class Message {
	private $cmd;
	private $data;
	private $code;

	public function __construct(string $cmd, $data, $code = 200) {
		$this->cmd  = $cmd;
		$this->data = $data;
		$this->code = $code;
	}

	public function setCmd(string $cmd): void {
		$this->cmd = $cmd;
	}

	public function getCmd(): string {
		return $this->cmd;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}

	public function getPackage() {
		return [
			'cmd'  => $this->cmd,
			'data' => $this->data,
			'code' => $this->code
		];
	}
}
