<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Server;

use Illuminate\Support\Str;
use W7\App;
use W7\Core\Config\Event;
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
		return true;
	}

	protected function registerSwooleEventListener() {
		$event = [$this->getType(), 'task', 'manage'];
		
		foreach ($event as $name) {
			$event = \iconfig()->getEvent()[$name];
			if (!empty($event)) {
				$this->registerEvent($event);
			}
		}

		//开启协程
		//if (isCo()) {
			\Swoole\Runtime::enableCoroutine(true);
		//}
	}

	protected function registerProcess() {
		if ((SERVER & CRONTAB) === CRONTAB) {
			(new CrontabServer())->registerPool(DependentPool::class)->start();
		}
		if ((SERVER & PROCESS) === PROCESS) {
			(new ProcessServer())->registerPool(DependentPool::class)->start();
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
			$object = \iloader()->get($class);
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
