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

namespace  W7\WebSocket\Parser;

use W7\WebSocket\Message\Message;

class JsonParser implements ParserInterface {
	public function encode(Message $message): string {
		return json_encode($message->getPackage());
	}

	public function decode(string $data): Message {
		$cmd = '';
		$map = json_decode($data, true);

		// Find message route command
		if (isset($map['cmd'])) {
			$cmd = (string)$map['cmd'];
			unset($map['cmd']);
		}

		if (isset($map['data'])) {
			$data = $map['data'];
		} else {
			$data = $map;
		}

		return new Message($cmd, $data);
	}
}
