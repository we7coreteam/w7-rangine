<?php

namespace W7\Client\Protocol;

class ClientAbstract {
	protected $packFormat;

	public function pack( $body) {
		switch ($this->packFormat) {
			case 'serialize':
				return serialize($body);
			case 'json':
				return json_encode($body);
			default:
				return $body;
		}
	}

	public function unpack($body) {
		switch ($this->packFormat) {
			case 'serialize':
				return unserialize($body);
			case 'json':
				return json_decode($body, true);
			default:
				return $body;
		}
	}
}