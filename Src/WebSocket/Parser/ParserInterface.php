<?php

namespace W7\WebSocket\Parser;

use W7\WebSocket\Message\Message;

interface ParserInterface {
	public function encode(Message $message) : string;
	public function decode(string $message) : Message;
}