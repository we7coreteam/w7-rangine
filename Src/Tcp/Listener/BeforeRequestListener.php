<?php

namespace W7\Tcp\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Http\Message\Helper\JsonHelper;

class BeforeRequestListener extends ListenerAbstract {
    public function run(...$params) {
        if (!is_array($params[0]) && !is_object($params[0])) {
            $params[0] = JsonHelper::decode($params[0], true);
        }

        $body['url'] = $params[0]['url'] ?? '';
        $body['data'] = $params[0]['data'] ?? [];
        $body['method'] = 'GET';
        $body['protocol'] = 'rpc';

        return [$body];
    }
}