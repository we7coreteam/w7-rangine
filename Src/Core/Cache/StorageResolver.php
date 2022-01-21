<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Cache;

use W7\Core\Cache\Handler\HandlerAbstract;
use W7\Core\Helper\Traiter\AppCommonTrait;

class StorageResolver {
	use AppCommonTrait;

	protected $storageConfig = [];

	public function __construct($storageConfig = []) {
		$this->storageConfig = $storageConfig;
	}

	public function storage($name) {
		$connection = $this->storageConfig[$name]['driver'];
		/**
		 * @var HandlerAbstract $connection
		 */
		return $connection::connect($this->storageConfig[$name]);
	}
}
