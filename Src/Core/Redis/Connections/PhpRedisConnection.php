<?php

namespace W7\Core\Redis\Connections;

use Illuminate\Support\Str;

class PhpRedisConnection extends \Illuminate\Redis\Connections\PhpRedisConnection {
	public function command($method, array $parameters = []) {
		try {
			return parent::command($method, $parameters);
		} catch (\RedisException $e) {
			if (Str::contains($e->getMessage(), 'went away') && $this->connector) {
				return $this->command($method, $parameters);
			}

			throw $e;
		}
	}
}
