<?php

namespace W7\Client\Protocol;

class ClientAbstract {
	protected $packFormat;

	public function pack($body) {
		switch ($this->packFormat) {
			case 'serialize':
				return serialize($body);
				break;
			case 'json':
			default:
				return json_encode($body);
		}
	}

	public function unpack($body) {
		switch ($this->packFormat) {
			case 'serialize':
				return unserialize($body);
				break;
			case 'json':
			default:
				return json_decode($body, true);
		}
	}
}