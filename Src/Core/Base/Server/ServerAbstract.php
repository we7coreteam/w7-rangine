<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Base\Server;

use W7\App;
use W7\Core\Config\Event;
use W7\Core\Exception\CommandException;
use W7\Core\Helper\Context;

abstract class ServerAbstract implements ServerInterface
{

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
	public function __construct()
	{
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
	public function getPname()
	{
		return $this->setting['pname'];
	}


	public function getStatus()
	{
		$this->setting['masterPid'] = $this->server->master_pid;
		$this->setting['managerPid'] = $this->server->manager_pid;

		return [
			'host' => $this->connection['host'],
			'port' => $this->connection['port'],
			'type' => $this->connection['sock_type'],
			'mode' => $this->connection['mode'],
			'workerNum' => $this->setting['worker_num'],
			'masterPid' => $this->setting['masterPid'],
			'managerPid' => $this->setting['managerPid'],
		];
	}

	public function getServer()
	{
		return $this->server;
	}

	public function isRun()
	{
		$status = $this->getStatus();
		if (!empty($status['masterPid'])) {
			return true;
		} else {
			return false;
		}
	}

	public function stop()
	{
		$timeout = 20;
		$startTime = time();
		$result = true;

		if (\swoole_process::kill($this->setting['masterPid'], 0)) {
			\swoole_process::kill($this->setting['masterPid'], SIGTERM);
			while (1) {
				$masterIslive = \swoole_process::kill($this->setting['masterPid'], SIGTERM);
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


	public function registerService()
	{
		$this->registerSwooleEventListener();
		$this->registerProcesser();
		$this->registerServerContext();
		$this->registerDb();
		return true;
	}

	protected function registerProcesser()
	{
		$processName = \iconfig()->getProcess();
		foreach ($processName as $name) {
			$process = iprocess($name, App::$server);
			if (!$process) {
				continue;
			}
			$this->server->addProcess($process);
		}
	}

	protected function registerSwooleEventListener()
	{
		$event = [$this->type, 'task', 'manage'];
		
		foreach ($event as $name) {
			$event = \iconfig()->getEvent()[$name];
			if (!empty($event)) {
				$this->registerEvent($event);
			}
		}
	}

	/**
	 *
	 */
	protected function registerServerContext()
	{
		/**
		 * @var Context $contextObj
		 */
		$contextObj = iloader()->singleton(\W7\Core\Helper\Context::class);
		$this->server->context = $contextObj->getContextData();
	}

	private function registerDb()
	{
		App::$dbPool = \iloader()->singleton(\W7\Core\Database\Pool\MasterPool::class);
	}

	private function registerEvent($event)
	{
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
