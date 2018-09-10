<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:25
 */
namespace W7\Core\Database\Pool;


class SlavePool extends MasterPool {
	public function release($connection) {
		$connection = $connection->getReadPdo();
		return parent::release($connection);
	}
}