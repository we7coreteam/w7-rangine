<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Server;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Fluent;
use Swoole\Process;
use W7\Core\Crontab\CrontabServer;
use W7\Core\Database\Connection\PdoMysqlConnection;
use W7\Core\Database\Connection\SwooleMySqlConnection;
use W7\App;
use W7\Core\Database\ConnectorManager;
use W7\Core\Database\DatabaseManager;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Exception\CommandException;
use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\ProcessServer;
use W7\Laravel\CacheModel\Caches\Cache;

abstract class ServerAbstract implements ServerInterface {
	const TYPE_HTTP = 'http';
	const TYPE_RPC = 'rpc';
	const TYPE_TCP = 'tcp';
	const TYPE_WEBSOCKET = 'websocket';

	/**
	 * @var \Swoole\Http\Server
	 */
	public $server;

	/**
	 * 配置
	 * @var
	 */
	public $setting;
	/**
	 * @var 连接配置
	 */
	public $connection;

	/**
	 * ServerAbstract constructor.
	 * @throws CommandException
	 */
	public function __construct() {
		date_default_timezone_set('Asia/Shanghai');
		App::$server = $this;
		$setting = \iconfig()->getServer();
		if (empty($setting[$this->getType()]) || empty($setting[$this->getType()]['host'])) {
			throw new CommandException(sprintf('缺少服务配置 %s', $this->getType()));
		}
		$this->setting = array_merge([], $setting['common']);
		$this->enableCoroutine();
		$this->connection = $setting[$this->getType()];
	}

	/**
	 * Get pname
	 *
	 * @return string
	 */
	public function getPname() {
		return $this->setting['pname'];
	}

	public function getStatus() {
		$pidFile = $this->setting['pid_file'];
		if (file_exists($pidFile)) {
			$pids = explode(',', file_get_contents($pidFile));
		}
		return [
			'host' => $this->connection['host'],
			'port' => $this->connection['port'],
			'type' => $this->connection['sock_type'],
			'mode' => $this->connection['mode'],
			'workerNum' => $this->setting['worker_num'],
			'masterPid' => !empty($pids[0]) ? $pids[0] : 0,
			'managerPid' => !empty($pids[1]) ? $pids[1] : 0,
		];
	}

	public function getServer() {
		return $this->server;
	}

	public function isRun() {
		$status = $this->getStatus();
		if (!empty($status['masterPid'])) {
			return true;
		} else {
			return false;
		}
	}

	protected function enableCoroutine() {
		$this->setting['enable_coroutine'] = true;
		$this->setting['task_enable_coroutine'] = false;
	}

	public function stop() {
		$status = $this->getStatus();
		$timeout = 20;
		$startTime = time();
		$result = true;

		if (Process::kill($status['masterPid'], 0)) {
			Process::kill($status['masterPid'], SIGTERM);
			while (1) {
				$masterIslive = Process::kill($status['masterPid'], SIGTERM);
				if ($masterIslive) {
					if (time() - $startTime >= $timeout) {
						$result = false;
						break;
					}
					usleep(10000);
					continue;
				}
				break;
			}
		}


		if (!file_exists($this->setting['pid_file'])) {
			return true;
		} else {
			unlink($this->setting['pid_file']);
		}
		return $result;
	}

	public function registerService() {
		$this->registerSwooleEventListener();
		$this->registerProcess();
		$this->registerServerContext();
//		$this->registerDb();
		$this->registerCacheModel();
	}

	protected function registerSwooleEventListener() {
		$eventTypes = [$this->getType(), 'task', 'manage'];

		$swooleEvents = (new SwooleEvent())->getDefaultEvent();
		foreach ($eventTypes as $name) {
			$event = $swooleEvents[$name];
			if (!empty($event)) {
				$this->registerEvent($event);
			}
		}
	}

	protected function registerEvent($event) {
		if (empty($event)) {
			return true;
		}
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			if ($eventName == SwooleEvent::ON_REQUEST) {
				$server = \W7\App::$server->server;
				$this->server->on(SwooleEvent::ON_REQUEST, function ($request, $response) use ($server) {
					iloader()->singleton(EventDispatcher::class)->dispatch(SwooleEvent::ON_REQUEST, [$server, $request, $response]);
				});
			} else {
				$this->server->on($eventName, function() use ($eventName) {
					iloader()->singleton(EventDispatcher::class)->dispatch($eventName, func_get_args());
				});
			}
		}
	}

	protected function registerProcess() {
		if ((SERVER & CRONTAB) === CRONTAB) {
			(new CrontabServer())->registerPool(DependentPool::class)->start();
		}
		if ((SERVER & PROCESS) === PROCESS) {
			(new ProcessServer())->registerPool(DependentPool::class)->start();
		}
	}

	protected function registerServerContext() {
		$contextObj = App::getApp()->getContext();
		$this->server->context = $contextObj->getContextData();
	}

	/**
	 * model -> newQuery -> DatabaseMananger -> function connection ->
	 *      Factory -> createConnector 拿到一个Pdo连接 （ConnectorManager -> 从连接池里拿一个Pdo连接） -> createConnection 放置Pdo连接，生成连接操作对象 (PdoMysqlConnection)
	 *
	 * @return bool
	 */
	private function registerDb() {
		//新增swoole连接mysql的方式
		Connection::resolverFor('swoolemysql', function ($connection, $database, $prefix, $config) {
			return new SwooleMySqlConnection($connection, $database, $prefix, $config);
		});
		Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
			return new PdoMysqlConnection($connection, $database, $prefix, $config);
		});

		//新增swoole连接Mysql的容器
		$container = new Container();
		//$container->instance('db.connector.swoolemysql', new SwooleMySqlConnector());
		//$container->instance('db.connector.mysql', new PdoMySqlConnector());
		$container->instance('db.connector.swoolemysql', new ConnectorManager());
		$container->instance('db.connector.mysql', new ConnectorManager());

		//侦听sql执行完后的事件，回收$connection
		$dbDispatch = iloader()->singleton(EventDispatcher::class)->getDispatcher();
		$reflect = new \ReflectionClass($dbDispatch);
		$property = $reflect->getProperty('container');
		$property->setAccessible('public');
		$property->setValue($reflect, $container);
		$property->setAccessible('protected');

		$dbDispatch->listen(QueryExecuted::class, function ($data) use ($container) {
			/**
			 *检测是否是事物里面的query
			 */
			if (App::getApp()->getContext()->getContextDataByKey('db-transaction')) {
				return false;
			}
			return $this->releaseDb($data, $container);
		});
		$dbDispatch->listen(TransactionBeginning::class, function ($data) {
			$connection = $data->connection;
			App::getApp()->getContext()->setContextDataByKey('db-transaction', $connection);
		});
		$dbDispatch->listen(TransactionCommitted::class, function ($data) use ($container) {
			if (idb()->transactionLevel() === 0) {
				App::getApp()->getContext()->setContextDataByKey('db-transaction', null);
				return $this->releaseDb($data, $container);
			}
		});
		$dbDispatch->listen(TransactionRolledBack::class, function ($data) use ($container) {
			if (idb()->transactionLevel() === 0) {
				App::getApp()->getContext()->setContextDataByKey('db-transaction', null);
				return $this->releaseDb($data, $container);
			}
		});

		$container->instance('events', $dbDispatch);

		//添加配置信息到容器
		$dbconfig = \iconfig()->getUserAppConfig('database');

		$container->instance('config', new Fluent());
		$container['config']['database.default'] = 'default';
		$container['config']['database.connections'] = $dbconfig;
		$factory = new ConnectionFactory($container);
		$dbManager = new DatabaseManager($container, $factory);

		Model::setEventDispatcher($dbDispatch);
		Model::setConnectionResolver($dbManager);
		return true;
	}

	private function releaseDb($data, $container) {
		return true;
		$connection = $data->connection;
		ilogger()->channel('database')->debug(($data->sql ?? '') . ', params: ' . implode(',', (array) (empty($data->bindings) ? [] : $data->bindings )));

		$poolName = $connection->getPoolName();
		if (empty($poolName)) {
			return true;
		}
		list($poolType, $poolName) = explode(':', $poolName);
		if (empty($poolType)) {
			$poolType = 'swoolemysql';
		}

		$activePdo = $connection->getActiveConnection();
		if (empty($activePdo)) {
			return false;
		}
		$connectorManager = $container->make('db.connector.' . $poolType);
		$pool = $connectorManager->getCreatedPool($poolName);
		if (empty($pool)) {
			return true;
		}
		$pool->releaseConnection($activePdo);
		return true;
	}

	protected function registerCacheModel() {
		$config = iconfig()->getUserAppConfig('cache');
		if (!empty($config['default']) && !empty($config['default']['model']) && !empty($config['default']['host']) && !empty($config['default']['port'])) {
			Cache::setCacheResolver(icache());
		}
	}
}
