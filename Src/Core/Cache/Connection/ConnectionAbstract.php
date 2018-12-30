<?php
/**
 * @author donknap
 * @date 18-12-30 下午5:21
 */

namespace W7\Core\Cache\Connection;



abstract class ConnectionAbstract implements ConnectionInterface {
	public function release() {
		$poolName = $this->poolName;

		if (empty($poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $poolName);
		if (empty($poolType)) {
			$poolType = 'swoolemysql';
		}
	}
}