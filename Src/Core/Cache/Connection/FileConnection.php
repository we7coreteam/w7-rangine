<?php

namespace W7\Core\Cache\Connection;

use W7\Core\Cache\Handler\FileHandler;

class FileConnection extends ConnectionAbstract {
	public function connect(array $config) {
		if (empty($config['path'])) {
			$config['path'] = RUNTIME_PATH . '/cache';
		}
		return new FileHandler($config['path']);
	}
}