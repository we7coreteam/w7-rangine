<?php

namespace W7\Client;

use W7\Client\Protocol\ClientAbstract;

class Client {
    private $baseUrl;
    private $protocol;
    private $handle;

    public function __construct($params = [
    	'protocol' => 'thrift'
    ]) {
		$this->baseUrl = $params['base_url'] ?? '';
	    $this->protocol = $params['protocol'] ?? '';
    }

    public function setBaseUrl($url) {
    	$this->baseUrl = $url;
	    $this->handle = null;
    }

    public function setProtocol($protocol) {
    	if ($this->protocol !== $protocol) {
    		$this->handle = null;
	    }
    	$this->protocol = $protocol;
    }

	public function call($url, $params = null)
    {
    	$client = $this->getClient();
    	return $client->call($url, $params);
    }

    private function getClient() : ClientAbstract {
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
		    $this->handle = new $class($this->baseUrl);
    	}

	    return $this->handle;
    }
}