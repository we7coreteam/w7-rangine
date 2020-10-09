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

namespace W7\Core\Database\Connection;

use Illuminate\Database\MySqlConnection;

class PdoMysqlConnection extends MySqlConnection {
	/**
	 * 获取当前活动的查询连接
	 */
	public function getActiveConnection() {
		if ($this->pdo instanceof \PDO) {
			return $this->pdo;
		} else {
			return $this->readPdo;
		}
	}
}
