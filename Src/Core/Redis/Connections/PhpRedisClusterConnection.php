<?php

namespace W7\Core\Redis\Connections;

class PhpRedisClusterConnection extends PhpRedisConnection {
	public function flushdb() {
		$arguments = func_get_args();

		$async = strtoupper((string) ($arguments[0] ?? null)) === 'ASYNC';

		foreach ($this->client->_masters() as $master) {
			$async
				? $this->command('rawCommand', [$master, 'flushdb', 'async'])
				: $this->command('flushdb', [$master]);
		}
	}
}
