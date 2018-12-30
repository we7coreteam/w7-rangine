<?php
/**
 * @author donknap
 * @date 18-12-30 下午5:19
 */

namespace W7\Core\Cache\Connection;


class RedisConnection extends ConnectionAbstract {
	/**
	 * @param array $config
	 * @return mixed|\Redis
	 * @throws \Exception
	 */
	public function connect($config) {
		$redis  = new \Redis();
		$result = $redis->connect($config['host'], $config['port'], $config['timeout']);
		if ($result === false) {
			$error = sprintf('Redis connection failure host=%s port=%d', $config['host'], $config['port']);
			throw new \Exception($error);
		}
		return $redis;
	}
}