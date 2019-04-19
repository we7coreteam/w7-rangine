<?php

namespace W7\Tcp\Protocol;

use Swoole\Server;

interface IDispatcher
{
    public function dispatch (Server $server, $fd, $data);
}