<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:18
 */

namespace W7\Core\Database\Pool;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use W7\Core\Base\Pool\PoolAbstract;

class MasterPool extends PoolAbstract
{
	/**
	 * @var Manager
	 */
	private $dbManager;
	private $container;

	/**
	 * 构造函数中，需要实例化出来Laravel中关于创建数据库的一些对象
	 * 具体看 \Illuminate\Database\Capsule\Manager.php
	 */
	protected function createConnection()
	{
		$this->dbManager = $this->getDbManager();
		$connect = $this->dbManager->connection();
		$connect->createTime = time();
		$connect->connectionId = uniqid();
		return $connect;
	}

	private function getDbManager()
	{
		if (!empty($this->dbManager))
		{
			//return $this->dbManager;
		}
		$dbconfig = \iconfig()->getUserCommonConfig('database');
		/**
		 * @var Manager $manager
		 */
		$manager = iloader()->singleton(Manager::class);
		$manager->setAsGlobal();
		$manager->addConnection($dbconfig['master']);

		/**
		 * @var Dispatcher $dispatch
		 */
		$dispatch = \iloader()->singleton(Dispatcher::class);
		$dispatch->listen(QueryExecuted::class, function ($data) {
			$connection = $data->connection;
			$this->release($connection);
		});
		$manager->setEventDispatcher($dispatch);
		return $manager->getDatabaseManager();
	}
}