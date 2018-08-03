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
	/**
	 * @var DatabaseManager $databaseManager;
	 */
	private $databaseManager;


	public function init()
	{
		ilogger()->info('db pool init');

		//新增swoole连接mysql的方式
		Connection::resolverFor('swoolemysql', function ($connection, $database, $prefix, $config) {
			return new SwooleMySqlConnection($connection, $database, $prefix, $config);
		});

		//新增swoole连接Mysql的容器
		$container = new Container();
		$container->instance('db.connector.swoolemysql', new SwooleMySqlConnector());

		//侦听sql执行完后的事件，回收$connection
		$dbDispatch = new Dispatcher($container);
		$dbDispatch->listen(QueryExecuted::class, function ($data) {
			$connection = $data->connection;
			App::$dbPool->release($connection);
		});
		$container->instance('events', $dbDispatch);

		//添加配置信息到容器
		$dbconfig = \iconfig()->getUserCommonConfig('database');

		$container->instance('config', new Fluent());
		$container['config']['database.default'] = 'default';
		$container['config']['database.connections'] = [
			'default' => $dbconfig['default'],
		];

		$factory = new ConnectionFactory($container);
		$this->databaseManager = new DatabaseManager($container, $factory);
	}

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