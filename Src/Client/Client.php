<?php

namespace W7\Client;

use W7\Client\Protocol\IClient;

class Client {
    private $baseUrl;
    private $protocol;
    private $packFormat;
    private $handle;

    public function __construct(array $params = [
    	'base_url' => '',
    	'protocol' => 'thrift',
	    'pack_format' => 'json'
    ]) {
		$this->baseUrl = $params['base_url'] ?? '';
	    $this->protocol = $params['protocol'] ?? 'thrift';
	    $this->packFormat = $params['pack_format'] ?? 'json';
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

	public function call($url, $params = null)
    {
    	$client = $this->getClient();
    	return $client->call($url, $params);
    }

//    private function pack($url, $params = null) {
//	    $body = [
//		    'url' => $url
//	    ];
//	    if ($params) {
//		    $body['data'] = $params;
//	    }
//
//	    switch ($this->packFormat) {
//		    case 'serialize':
//			    return serialize($body);
//			    break;
//		    case 'json':
//		    default:
//			    return json_encode($body);
//	    }
//    }

//	private function unpack($data) {
//		switch ($this->packFormat) {
//			case 'serialize':
//				return unserialize($data);
//				break;
//			case 'json':
//			default:
//				return json_decode($data, true);
//		}
//	}

    private function getClient() : IClient {
    	if (!$this->handle) {
		    $class = '';
		    switch ($this->protocol) {
//			    case 'json':
//				    $class = '\\W7\\Client\\Protocol\\Json\\Client';
//				    break;
//			    case 'grpc':
//				    $class = '\\W7\\Client\\Protocol\\Grpc\\Client';
//				    break;
			    case 'thrift':
			    default:
				    $class = '\\W7\\Client\\Protocol\\Thrift\\Client';
		    }
		    $this->handle = new $class([
		    	'base_url' => $this->baseUrl,
			    'pack_format' => $this->packFormat
		    ]);
    	}

	    return $this->handle;
    }
}