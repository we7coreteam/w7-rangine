<?php
/**
 * @author donknap
 * @date 18-7-30 下午6:18
 */

namespace W7\Core\Database\Pool;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use W7\Core\Base\Pool\PoolAbstract;
use W7\Core\Database\Connection\SwooleMySqlConnection;
use W7\Core\Database\Connector\SwooleMySqlConnector;

class MasterPool extends PoolAbstract
{
	protected $connectionName = 'master';
	/**
	 * @var Manager
	 */
	private $dbManager;
	private $dbconfig;
	private $dbDispatch;
	private $container;

	public function init()
	{
		$this->dbconfig = \iconfig()->getUserCommonConfig('database');

		//新增swoole连接mysql的方式
		Connection::resolverFor('swoolemysql', function ($connection, $database, $prefix, $config) {
			return new SwooleMySqlConnection($connection, $database, $prefix, $config);
		});

		//新增swoole连接Mysql的容器
		/**
		 * @var Manager $manager
		 */
		$this->container = new Container();
		$this->container->instance('db.connector.swoolemysql', iloader()->singleton(SwooleMySqlConnector::class));

		//侦听sql执行完后的事件，回收$connection
		/**
		 * @var Dispatcher $dispatch
		 */
		$this->dbDispatch = new Dispatcher($this->container);
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
		$manager = new Manager($this->container);
		$manager->addConnection($this->dbconfig['master']);
		$manager->setEventDispatcher($this->dbDispatch);
		return $manager->getDatabaseManager();
	}
}