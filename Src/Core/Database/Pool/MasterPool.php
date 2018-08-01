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
	private $dbconfig;
	private $dbDispatch;

	public function init()
	{
		ilogger()->info('init - at ' . microtime(true));
		$this->dbconfig = \iconfig()->getUserCommonConfig('database');
		/**
		 * @var Dispatcher $dispatch
		 */
		$this->dbDispatch = \iloader()->singleton(Dispatcher::class);
		$this->dbDispatch->listen(QueryExecuted::class, function ($data) {
			$connection = $data->connection;
			$this->release($connection);
		});
	}

	/**
	 * 构造函数中，需要实例化出来Laravel中关于创建数据库的一些对象
	 * 具体看 \Illuminate\Database\Capsule\Manager.php
	 */
	protected function createConnection()
	{
		$this->dbManager = $this->getDbManager();
		$connect = $this->dbManager->connection();
		$connect->createTime = microtime(true);
		$connect->connectionId = \iuuid();
		return $connect;
	}

	private function getDbManager()
	{
		/**
		 * @var Manager $manager
		 */
		$manager = new Manager();
		$manager->addConnection($this->dbconfig['master']);
		$manager->setEventDispatcher($this->dbDispatch);
		return $manager->getDatabaseManager();
	}
}