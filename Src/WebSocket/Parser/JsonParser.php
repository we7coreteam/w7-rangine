<?php

namespace  W7\WebSocket\Parser;

use W7\WebSocket\Message\Message;

class JsonParser implements ParserInterface {
    public function encode(Message $message): string {
        return json_encode($message->getPackage());
    }

    public function decode(string $data): Message {
        $cmd = '';
        $ext = [];
        $map = json_decode($data, true);

        // Find message route command
        if (isset($map['cmd'])) {
            $cmd = (string)$map['cmd'];
            unset($map['cmd']);
        }

        if (isset($map['data'])) {
            $data = $map['data'];
            $ext  = $map['ext'] ?? [];
        } else {
            $data = $map;
        }

        return new Message($cmd, $data, $ext);
    }
}
