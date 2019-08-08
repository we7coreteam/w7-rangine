<?php

namespace W7\Core\Cache\Connection;

use W7\Core\Cache\Handler\FileHandler;

class FileConnection extends ConnectionAbstract {
	public function connect(array $config) {
		if (empty($config['path'])) {
			throw new \Exception("cache config path can't be empty");
		}
		return new FileHandler($config['path']);
	}
}