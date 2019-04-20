<?php

namespace W7\Tcp\Protocol\Thrift\Core;

use W7\App;
use W7\Http\Message\Helper\JsonHelper;
use W7\Http\Message\Server\Request;
use W7\Tcp\Server\Dispather;

class DispatcherHandle implements DispatcherIf {
    public function run($params) {
        $params = JsonHelper::decode($params, true);
        $params['url'] = $params['url'] ?? '';
        $params['data'] = $params['data'] ?? [];

        $psr7Request = new Request('POST', $params['url'], [], null);
        $psr7Request = $psr7Request->withParsedBody($params['data']);
        App::getApp()->getContext()->setRequest($psr7Request);

        $dispather = \iloader()->singleton(Dispather::class);
        $psr7Response = $dispather->dispatch($psr7Request);

        return $psr7Response->getBody()->getContents();
    }
}