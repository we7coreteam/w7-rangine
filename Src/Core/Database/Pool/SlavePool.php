<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:25
 */
namespace W7\Core\Database\Pool;

use W7\Core\Pool\PoolAbstract;

class SlavePool extends PoolAbstract {
	public function release($connection) {
		$connection = $connection->getReadPdo();
		return parent::release($connection);
	}
}