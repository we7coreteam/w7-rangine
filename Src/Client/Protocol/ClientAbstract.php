<?php

namespace W7\Client\Protocol;

abstract class ClientAbstract
{
	public function __construct($host) {}
	public function call ($url, $data = null) {}
}