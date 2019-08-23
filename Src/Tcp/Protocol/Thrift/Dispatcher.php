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

namespace W7\Tcp\Protocol\Thrift;

use Swoole\Server;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\TMultiplexedProcessor;
use W7\Tcp\Protocol\DispatcherInterface;
use W7\Tcp\Protocol\Thrift\Core\DispatcherHandle;
use W7\Tcp\Protocol\Thrift\Core\DispatcherProcessor;
use W7\Tcp\Protocol\Thrift\Core\RpcSocket;

class Dispatcher implements DispatcherInterface {
	/**
	 * @var TMultiplexedProcessor
	 */
	private $process;

	public function __construct() {
		$this->registerService();
	}

	/**
	 * 注册路由到对应控制器的dispatcher
	 *用户可自定义service，进行数据的处理和返回
	 */
	private function registerService() {
		$this->process = new TMultiplexedProcessor();
		$services = [
			'Dispatcher' => [
				'handle' => DispatcherHandle::class,
				'process' => DispatcherProcessor::class
			]
		];

		foreach ($services as $key => $value) {
			$serviceHandler = new $value['handle']();
			$serviceProcess = new $value['process']($serviceHandler);
			$this->process->registerProcessor($key, $serviceProcess);
		}
	}

	/**
	 * 解析thrift数据，并路由到Protocol/Thrift/Core/DispatcherHandle
	 * @param Server $server
	 * @param $fd
	 * @param $data
	 */
	public function dispatch(Server $server, $fd, $data) {
		$socket = new RpcSocket();
		$socket->buffer = $data;
		$socket->server = $server;
		$socket->setHandle($fd);

		try {
			$protocol = new TBinaryProtocol($socket, false, false);
			$this->process->process($protocol, $protocol);
		} catch (\Throwable $e) {
			$server->close($fd);
		}
	}
}
