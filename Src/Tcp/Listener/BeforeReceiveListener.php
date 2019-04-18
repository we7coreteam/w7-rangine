<?php

namespace W7\Tcp\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Helper\JsonHelper;

class BeforeReceiveListener extends ListenerAbstract {
    public function run(...$params) {
        /**
         * body protocol
         */
        $body = JsonHelper::decode($params[0], true);
        $body['url'] = $body['url'] ?? '';
        $body['data'] = $body['data'] ?? [];
        $body['method'] = 'GET';
        $body['protocol'] = 'rpc';

        return $body;
    }
}