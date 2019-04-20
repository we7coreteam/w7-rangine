<?php

namespace W7\Client;

use W7\Client\Protocol\ClientInterface;
use W7\Client\Protocol\Thrift\Client as ClientThrift;

class Client {
	private $baseUrl;
	private $protocol;
	private $packFormat;
	private $handle;

	private $protocolMap = [
		'thrift' => ClientThrift::class
	];

	public function __construct(array $params = [
		'base_url' => '',
		'protocol' => 'thrift'
	]) {
		$this->baseUrl = $params['base_url'] ?? '';
		$this->protocol = $params['protocol'] ?? 'thrift';
		$this->packFormat = 'json';
	}

	public function setBaseUrl($url) {
		$this->baseUrl = $url;
		$this->handle = null;

		return $this;
	}

	public function setProtocol($protocol) {
		if ($this->protocol !== $protocol) {
			$this->handle = null;
		}
		$this->protocol = $protocol;

		return $this;
	}

	public function call($url, $params = null) {
		$client = $this->getClient();
		return $client->call($url, $params);
	}

	private function getClient() : ClientInterface {
		if (!$this->handle) {
			if (empty($this->protocolMap[$this->protocol])) {
				$this->protocol = 'thrift';
			}
			$class = $this->protocolMap[$this->protocol];
			$this->handle = new $class([
				'base_url' => $this->baseUrl,
				'pack_format' => $this->packFormat
			]);
		}

		return $this->handle;
	}
}