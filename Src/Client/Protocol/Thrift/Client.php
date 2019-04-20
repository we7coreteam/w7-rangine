<?php

namespace W7\Client\Protocol\Thrift;

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TSocket;
use W7\Client\Protocol\ClientAbstract;
use W7\Client\Protocol\ClientInterface;
use W7\Client\Protocol\Thrift\Core\DispatcherClient;

class Client extends ClientAbstract implements ClientInterface {
	private $host;
	private $port;

	public function __construct(array $params) {
		$host = $params['base_url'];
		$pos = strrpos($host, ':');
		if ($pos !== false) {
			$this->host = substr($host, 0, $pos);
			$this->port = substr($host, $pos + 1);
		} else {
			$this->host = $host;
		}

		$this->packFormat = $params['pack_format'];
	}

	public function call($url, $params = null) {
		$socket = new TSocket($this->host, $this->port);
		$transport = new TFramedTransport($socket);
		$protocol = new TBinaryProtocol($transport);
		$service = new TMultiplexedProtocol($protocol, 'Dispatcher');
		$transport->open();

		$body = [
			'url' => $url
		];
		if ($params) {
			$body['data'] = $params;
		}
		$body = $this->pack($body);

		$client = new DispatcherClient($service);
		$ret = $client->run($body);
		$transport->close();

		return $this->unpack($ret);
	}
}