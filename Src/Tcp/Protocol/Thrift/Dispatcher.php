<?php

namespace W7\Tcp\Protocol\Thrift;

use Swoole\Server;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\TMultiplexedProcessor;
use W7\Tcp\Protocol\IDispatcher;
use W7\Tcp\Protocol\Thrift\Core\DispatcherHandle;
use W7\Tcp\Protocol\Thrift\Core\DispatcherProcessor;
use W7\Tcp\Protocol\Thrift\Core\RpcSocket;

class Dispatcher implements IDispatcher
{
    private $process;

    public function __construct()
    {
        $this->registerService();
    }

    private function registerService() {
        $this->process = new TMultiplexedProcessor();
        $services = [
            'Dispatcher' => [
                'handle' => DispatcherHandle::class,
                'process' => DispatcherProcessor::class
            ]
        ];
        /**
         * add user services in here
         * not support
         */
        foreach ($services as $key => $value) {
            $serviceHandler = new $value['handle']();
            $serviceProcess = new $value['process']($serviceHandler);
            $this->process->registerProcessor($key, $serviceProcess);
        }
    }

    public function dispatch(Server $server, $fd, $data)
    {
        $socket = new RpcSocket();
        $socket->buffer = $data;
        $socket->server = $server;
        $socket->setHandle($fd);

        $protocol = new TBinaryProtocol($socket, false, false);
        $this->process->process($protocol, $protocol);
    }
}