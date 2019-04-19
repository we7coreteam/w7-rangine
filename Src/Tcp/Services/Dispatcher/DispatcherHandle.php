<?php

namespace W7\Tcp\Services\Dispatcher;

use W7\Http\Message\Helper\JsonHelper;

class DispatcherHandle implements \W7\Tcp\Services\Dispatcher\DispatcherIf {
    public function run($params) {
        $params = JsonHelper::decode($params, true);
        $dispather = \iloader()->singleton(\W7\Tcp\Server\Dispather::class);
        $response = $dispather->dispatch('POST', $params['url'] ?? '', $params['data'] ?? []);

        return $response->getContent();
    }
}