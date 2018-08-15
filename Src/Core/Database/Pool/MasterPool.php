<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:18
 */

namespace W7\Core\Database\Pool;

use W7\Core\Pool\PoolAbstract;

class MasterPool extends PoolAbstract {
	public function release($connection) {
		$connection = $connection->getPdo();
		if ($connection instanceof \Closure) {
			$connection = $connection->getReadPdo();
		}
		ilogger()->info('pool release connection ');
		return parent::release($connection);
	}
}
