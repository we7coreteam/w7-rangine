<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Server;

use W7\App;
use Swoole\Process;
use W7\Core\Config\Event;
use W7\Core\Crontab\CrontabServer;
use W7\Core\Exception\CommandException;
use W7\Core\Process\Pool\DependentPool;
use W7\Core\Process\ProcessServer;

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
		$this->enableCoroutine();
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

	protected function enableCoroutine() {
		$this->setting['enable_coroutine'] = true;
		$this->setting['task_enable_coroutine'] = true;
		$this->setting['task_ipc_mode'] = 1;
		$this->setting['message_queue_key'] = '';
	}

	public function stop() {
		$status = $this->getStatus();
		$timeout = 20;
		$startTime = time();
		$result = true;

		while (Process::kill($status['masterPid'], 0)) {
			$result = Process::kill($status['masterPid'], SIGTERM);
			if ($result) {
				break;
			}
			if (!$result) {
				if (time() - $startTime >= $timeout) {
					$result = false;
					break;
				}
				usleep(10000);
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
		return true;
	}

	protected function registerProcesser() {
		$processName = \iconfig()->getProcess();
		foreach ($processName as $name) {
			\iprocess($name, App::$server->server);
		}

		//启动用户配置的进程
		$process = iconfig()->getUserAppConfig('process');
		if (!empty($process)) {
			foreach ($process as $name => $row) {
				if (empty($row['enable'])) {
					continue;
				}

				if (!class_exists($row['class'])) {
					$row['class'] = sprintf("\\W7\\App\\Process\\%s", Str::studly($row['class']));
				}

				if (!class_exists($row['class'])) {
					continue;
				}

				$row['number'] = intval($row['number']);
				if (!isset($row['number']) || empty($row['number']) || $row['number'] <= 1) {
					\iprocess($row['class'], App::$server->server);
				} else {
					//多个进程时，通过进程池管理

					for ($i = 1; $i <= $row['number']; $i++) {
						\iprocess($row['class'], App::$server->server);
					}
				}
			}
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
