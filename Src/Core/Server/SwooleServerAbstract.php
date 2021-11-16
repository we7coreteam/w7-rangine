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

use Illuminate\Contracts\Container\BindingResolutionException;
use JetBrains\PhpStorm\ArrayShape;
use Swoole\Process;
use Swoole\Server;
use W7\App;
use W7\Contract\Server\SwooleServerInterface;
use W7\Core\Helper\Compate\SwooleHelper;

abstract class SwooleServerAbstract extends ServerAbstract implements SwooleServerInterface {
	/**
	 * @var Server
	 */
	public mixed $server;
	public array $setting;

	protected static bool $isRegisterMasterServerEvent;
	protected static bool $isRegisterServerCommonEvent;
	protected array $masterServerEventType = ['manage', 'worker', 'task'];

	/**
	 * @throws \Exception
	 */
	public function __construct() {
		SwooleHelper::checkLoadSwooleExtension();

		parent::__construct();
		$setting = $this->getConfig()->get('server');
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
	public function getPname(): string {
		return $this->setting['pname'] . ' ';
	}

	#[ArrayShape(['host' => "mixed", 'port' => "mixed", 'type' => "string", 'mode' => "string", 'workerNum' => "mixed", 'masterPid' => "int|string", 'managerPid' => "int|string"])] public function getStatus(): array {
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

	public function isRun(): bool {
		$status = $this->getStatus();
		if (!empty($status['masterPid'])) {
			return true;
		}

		return false;
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

		return $result;
	}

	protected function getDefaultSetting() : array {
		$logLevel = SWOOLE_LOG_TRACE;
		if (SWOOLE_VERSION <= '4.4.16') {
			$logLevel = SWOOLE_LOG_INFO;
		}
		$runtimePath = App::getApp()->getRuntimePath();
		return [
			'pname' => App::NAME,
			'daemonize' => 0,
			'dispatch_mode' => 3,
			'worker_num' => swoole_cpu_num(),
			'log_file' => $runtimePath . '/logs/run.log',
			'log_level' => $logLevel,
			'request_slowlog_timeout' => 2,
			'request_slowlog_file' => $runtimePath . '/logs/slow.log',
			'trace_event_worker' => true,
			'upload_tmp_dir' => $runtimePath . '/upload',
			'document_root' => App::getApp()->getBasePath() . '/public',
			'enable_static_handler' => true,
			'task_tmpdir' => $runtimePath . '/task',
			'open_http2_protocol' => false,
			'mode' => SWOOLE_PROCESS,
			'sock_type' => SWOOLE_SOCK_TCP
		];
	}

	protected function checkSetting(): void {
		if (empty($this->setting['pid_file'])) {
			throw new \RuntimeException('server pid_file error');
		}
		if (empty($this->setting['pname'])) {
			throw new \RuntimeException('server pname error');
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
		if (!empty($this->setting['log_file']) && !is_dir(dirname($this->setting['log_file']))) {
			isafeMakeDir(dirname($this->setting['log_file']), 0777, true);
		}
		if (!empty($this->setting['log_file']) && !is_writeable(dirname($this->setting['log_file']))) {
			throw new \RuntimeException('path ' . dirname($this->setting['log_file']) . ' no write permission');
		}
		if (!empty($this->setting['request_slowlog_file']) && !is_dir(dirname($this->setting['request_slowlog_file']))) {
			isafeMakeDir(dirname($this->setting['request_slowlog_file']), 0777, true);
		}
		if (!empty($this->setting['request_slowlog_file']) && !is_writeable(dirname($this->setting['request_slowlog_file']))) {
			throw new \RuntimeException('path ' . dirname($this->setting['request_slowlog_file']) . ' no write permission');
		}
		if (!empty($this->setting['enable_static_handler']) && (empty($this->setting['document_root']) || !is_dir($this->setting['document_root']))) {
			throw new \RuntimeException('document_root path not exists');
		}
	}

	protected function resetPidFile(): void {
		$pathInfo = pathinfo($this->setting['pid_file']);
		$pathInfo['basename'] = $this->getType() . '_' .  ($this->setting['port'] ?? '') . '_' . $pathInfo['basename'];
		$pidFile = rtrim($pathInfo['dirname'], '/') . '/' . $pathInfo['basename'];

		$this->setting['pid_file'] = $pidFile;
	}

	protected function enableCoroutine(): void {
		$this->setting['enable_coroutine'] = true;
		$this->setting['task_enable_coroutine'] = true;
		$this->setting['task_ipc_mode'] = 1;
		$this->setting['message_queue_key'] = '';
	}

	protected function filterServerSetting(): array {
		if (version_compare(SWOOLE_VERSION, '4.5.5', '>=')) {
			$supportSettings = Server\Helper::GLOBAL_OPTIONS + Server\Helper::SERVER_OPTIONS + Server\Helper::PORT_OPTIONS + Server\Helper::HELPER_OPTIONS;
			return array_intersect_key($this->setting, $supportSettings);
		}

		return $this->setting;
	}

	/**
	 * @throws \ReflectionException
	 * @throws BindingResolutionException
	 * @throws \Exception
	 */
	protected function registerServerEvent($server): void {
		$eventTypes = [];
		/**
		 * @var ServerEvent $eventRegister
		 */
		$eventRegister = $this->getContainer()->get(ServerEvent::class);

		//Register Master Manager events, which are registered only once
		if (!self::$isRegisterMasterServerEvent && $server instanceof Server) {
			$eventTypes = $this->masterServerEventType;
			$eventRegister->registerServerEvent($eventTypes);
			self::$isRegisterMasterServerEvent = true;
		}

		//Register events for the service
		$eventRegister->registerServerEvent($this->getType());

		//Register the Server user event once
		if (!self::$isRegisterServerCommonEvent && $server instanceof Server) {
			$eventRegister->registerServerUserEvent();
			self::$isRegisterServerCommonEvent = true;
		}

		//Register for Server custom events, that is, events under each Server directory
		$eventRegister->registerServerCustomEvent($this->getType());
		$eventTypes[] = $this->getType();

		$swooleEvents = $this->getContainer()->get(ServerEvent::class)->getDefaultEvent();
		foreach ($eventTypes as $eventType) {
			$event = $swooleEvents[$eventType];
			if (!empty($event)) {
				$this->registerSwooleEvent($server, $event, $eventType);
			}
		}
	}

	protected function registerSwooleEvent($server, $event, $eventType): void {
		$masterServer = App::$server->getServer();
		foreach ($event as $eventName => $class) {
			if (empty($class)) {
				continue;
			}
			if ($eventName === ServerEvent::ON_REQUEST) {
				$server->on(ServerEvent::ON_REQUEST, function ($request, $response) use ($masterServer, $eventType) {
					$this->getEventDispatcher()->dispatch($this->getServerEventRealName(ServerEvent::ON_REQUEST, $eventType), [$masterServer, $request, $response]);
				});
			} else {
				$server->on($eventName, function () use ($eventName, $eventType) {
					$this->getEventDispatcher()->dispatch($this->getServerEventRealName($eventName, $eventType), func_get_args());
				});
			}
		}
	}

	protected function getServerEventRealName($eventName, $eventType): string {
		return $eventType . ':' . $eventName;
	}

	public function listener(\Swoole\Server $server) {
	}
}
