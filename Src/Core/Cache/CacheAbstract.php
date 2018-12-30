<?php
/**
 * @author donknap
 * @date 18-12-30 下午5:38
 */

namespace W7\Core\Cache;


use Psr\SimpleCache\CacheInterface;

abstract class CacheAbstract implements CacheInterface {

	/**
	 * @var ConnectorManager
	 */
	protected $manager;

	protected $connection;

	/**
	 * 选择一个缓存通道
	 * @param $name
	 * @return $this
	 */
	public function channel($name) {
		if (empty($name)) {
			throw new \RuntimeException('Invalid cache channel name');
		}
		return new static($name);
	}

	public function __construct($name = 'default') {
		$this->manager = iloader()->singleton(ConnectorManager::class);
		$this->connection = $this->manager->connect($name);
		return $this;
	}
}