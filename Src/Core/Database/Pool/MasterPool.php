<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:18
 */

namespace W7\Core\Database\Pool;

use W7\Core\Pool\PoolAbstract;
use W7\Core\Database\DatabaseManager;

class MasterPool extends PoolAbstract
{
	/**
	 * @var DatabaseManager $databaseManager;
	 */
	private $databaseManager;

	/**
	 * 构造函数中，需要实例化出来Laravel中关于创建数据库的一些对象
	 * 具体看 \Illuminate\Database\Capsule\Manager.php
	 */
	protected function createConnection()
	{
		$connection = $this->databaseManager->connection();
		return $connection;
	}
}
