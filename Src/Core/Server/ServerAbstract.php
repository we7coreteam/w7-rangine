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
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Fluent;
use W7\Core\Database\Connection\SwooleMySqlConnection;
use W7\App;
use W7\Core\Config\Event;
use W7\Core\Database\ConnectorManager;
use W7\Core\Database\DatabaseManager;
use W7\Core\Exception\CommandException;

abstract class ServerAbstract implements ServerInterface {

	/**
	 * @var \Swoole\Http\Server
	 */
	public $server;

	/**
	 * 服务类型
	 * @var
	 */
	public $type;

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
		App::$server = $this;
		$setting = \iconfig()->getServer();
		if (empty($setting[$this->type]) || empty($setting[$this->type]['host'])) {
			throw new CommandException(sprintf('缺少服务配置 %s', $this->type));
		}
		$this->setting = array_merge([], $setting['common']);
		$this->connection = $setting[$this->type];
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

	public function stop() {
		$status = $this->getStatus();
		$timeout = 20;
		$startTime = time();
		$result = true;

		if (\swoole_process::kill($status['masterPid'], 0)) {
			\swoole_process::kill($status['masterPid'], SIGTERM);
			while (1) {
				$masterIslive = \swoole_process::kill($status['masterPid'], SIGTERM);
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
		$this->registerProcesser();
		$this->registerServerContext();
		$this->registerDb();
		return true;
	}

	protected function registerProcesser() {
		$processName = \iconfig()->getProcess();
		foreach ($processName as $name) {
			\iprocess($name, App::$server);
		}
	}

	protected function registerSwooleEventListener() {
		$event = [$this->type, 'task', 'manage'];
		
		foreach ($event as $name) {
			$event = \iconfig()->getEvent()[$name];
			if (!empty($event)) {
				$this->registerEvent($event);
			}
		}
	}

	protected function registerServerContext() {
		$contextObj = App::getApp()->getContext();
		$this->server->context = $contextObj->getContextData();
	}

	private function registerDb() {
		ilogger()->info('db pool init');

		//新增swoole连接mysql的方式
		Connection::resolverFor('swoolemysql', function ($connection, $database, $prefix, $config) {
			return new SwooleMySqlConnection($connection, $database, $prefix, $config);
		});

		//新增swoole连接Mysql的容器
		$container = new Container();
		//$container->instance('db.connector.swoolemysql', new SwooleMySqlConnector());
		//$container->instance('db.connector.mysql', new PdoMySqlConnector());
		$container->instance('db.connector.swoolemysql', new ConnectorManager());
		$container->instance('db.connector.mysql', new ConnectorManager());

		//侦听sql执行完后的事件，回收$connection
		$dbDispatch = new Dispatcher($container);
		$dbDispatch->listen(QueryExecuted::class, function ($data) use ($container) {
			$connection = $data->connection;
			//$pool = $container->make('db.connector.swoolemysql')->pool;
			//if (is_null($pool)) {
			//	return false;
			//}
			//$pool->release($connection);
		});

		$container->instance('events', $dbDispatch);

		//添加配置信息到容器
		$dbconfig = \iconfig()->getUserAppConfig('database');

		$container->instance('config', new Fluent());
		$container['config']['database.default'] = 'default';
		$container['config']['database.connections'] = $dbconfig;

		$factory = new ConnectionFactory($container);
		$dbManager = new DatabaseManager($container, $factory);

		Model::setConnectionResolver($dbManager);
		return true;
	}

	private function registerEvent($event) {
		if (empty($event)) {
			return true;
		}
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			$object = \iloader()->singleton($class);
			if ($eventName == Event::ON_REQUEST) {
				$server = \W7\App::$server->server;
				$this->server->on(Event::ON_REQUEST, function ($request, $response) use ($server, $object) {
					$object->run($server, $request, $response);
				});
			} else {
				$this->server->on($eventName, [$object, 'run']);
			}
		}
	}
}
