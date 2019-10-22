<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Core\Server;

use Swoole\Process;
use W7\App;
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Exception\CommandException;

abstract class ServerAbstract implements ServerInterface {
	/**
	 * @var \Swoole\Server
	 */
	public $server;

	/**
	 * 配置
	 * @var
	 */
	public $setting;

	public static $masterServer = true;
	public static $onlyFollowMasterServer = false;
	public static $aloneServer = false;

	/**
	 * ServerAbstract constructor.
	 * @throws CommandException
	 */
	public function __construct() {
		!App::$server && App::$server = $this;
		$setting = \iconfig()->getServer();
		if (!isset($setting[$this->getType()])) {
			throw new \RuntimeException(sprintf('缺少服务配置 %s', $this->getType()));
		}
		$this->setting = array_merge($this->getDefaultSetting(), $setting['common'], $setting[$this->getType()]);
		$this->setting['worker_num'] = (int)($this->setting['worker_num']);
		$this->setting['mode'] = (int)($this->setting['mode']);
		$this->setting['sock_type'] = (int)($this->setting['sock_type']);

		$this->checkSetting();
		$this->resetPidFile();
		$this->enableCoroutine();
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
			'host' => $this->setting['host'],
			'port' => $this->setting['port'],
			'type' => ServerEnum::SOCK_LIST[$this->setting['sock_type']] ?? 'Unknown',
			'mode' => ServerEnum::MODE_LIST[$this->setting['mode']] ?? 'Unknown',
			'workerNum' => $this->setting['worker_num'],
			'masterPid' => !empty($pids[0]) ? $pids[0] : 0,
			'managerPid' => !empty($pids[1]) ? $pids[1] : 0,
		];
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
			$result = true;
		} else {
			unlink($this->setting['pid_file']);
		}

		App::$server = null;
		return $result;
	}

	protected function checkSetting() {
		if (empty($this->setting['pid_file'])) {
			throw new \RuntimeException('server pid_file error');
		}
		if (empty($this->setting['host'])) {
			throw new \RuntimeException('server host error');
		}
		if (empty($this->setting['port'])) {
			throw new \RuntimeException('server port error');
		}
		if ($this->setting['worker_num'] <= 0) {
			throw new \RuntimeException('server worker_num error');
		}
		if ($this->setting['mode'] <= 0) {
			throw new \RuntimeException('server mode error');
		}
		if ($this->setting['sock_type'] <= 0) {
			throw new \RuntimeException('server sock_type error');
		}
	}

	protected function resetPidFile() {
		$pathInfo = pathinfo($this->setting['pid_file']);
		$pathInfo['basename'] = $this->getType() . '_' .  ($this->setting['port'] ?? '') . '_' . $pathInfo['basename'];
		$pidFile = rtrim($pathInfo['dirname'], '/') . '/' . $pathInfo['basename'];

		$this->setting['pid_file'] = $pidFile;
	}

	public function getServer() {
		return $this->server;
	}

	public function listener(\Swoole\Server $server) {
	}

	protected function enableCoroutine() {
		$this->setting['enable_coroutine'] = true;
		$this->setting['task_enable_coroutine'] = true;
		$this->setting['task_ipc_mode'] = 1;
		$this->setting['message_queue_key'] = '';
	}

	public function registerService() {
		$this->registerSwooleEventListener();
	}

	protected function registerSwooleEventListener() {
		iloader()->get(SwooleEvent::class)->register();

		$swooleEvents = iloader()->get(SwooleEvent::class)->getDefaultEvent();
		$eventTypes = [$this->getType(), 'task', 'manage'];
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
					iloader()->get(EventDispatcher::class)->dispatch(SwooleEvent::ON_REQUEST, [$server, $request, $response]);
				});
			} else {
				$this->server->on($eventName, function () use ($eventName) {
					iloader()->get(EventDispatcher::class)->dispatch($eventName, func_get_args());
				});
			}
		}
	}

	protected function getDefaultSetting() : array {
		return [
			'dispatch_mode' => 3,
			'worker_num' => swoole_cpu_num(),
			'log_file' => BASE_PATH . '/runtime/logs/run.log',
			'log_level' => 0,
			'request_slowlog_timeout' => 2,
			'request_slowlog_file' => BASE_PATH . '/runtime/logs/slow.log',
			'trace_event_worker' => true,
			'upload_tmp_dir' => BASE_PATH . '/runtime/upload',
			'document_root' => BASE_PATH . '/public',
			'enable_static_handler' => true,
			'task_tmpdir' => BASE_PATH . '/runtime/task',
			'open_http2_protocol' => false,
			'mode' => SWOOLE_PROCESS,
			'sock_type' => SWOOLE_SOCK_TCP
		];
	}
}
