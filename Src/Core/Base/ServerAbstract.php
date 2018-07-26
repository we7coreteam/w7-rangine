<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Base;

use W7\App;
use W7\Core\Exception\CommandException;
use W7\Core\Helper\Middleware;
use W7\Core\Process\ReloadProcess;
use W7\Http\Listener\BeforeStartListener;

abstract class ServerAbstract implements ServerInterface {

	/**
	 * @var SwooleHttpServer
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

	public function __construct() {

	    App::$server = $this;

		$setting = \iconfig()->getServer();
        /**
         * @var Middleware $middlewarehelper
         */
		$middlewarehelper = App::getLoader()->singleton( Middleware::class);
		$middlewarehelper->insertMiddlewareCached();
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
    public function getPname(): string
    {
        return $this->setting['pname'];
    }


	public function getStatus() {
		$pidFile = $this->setting['pid_file'];
		if (file_exists($pidFile)) {
			$pids = explode(',', file_get_contents($pidFile));
			$this->setting['masterPid'] = $pids[0];
			$this->setting['managerPid'] = $pids[1];
		}

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

    protected function registerProcesser() {
        $processName = iconfig()->getProcess();
        foreach ($processName as $name) {
            $checkInfo = call_user_func([$name, "check"]);
            if (!$checkInfo){
                continue;
            }
            $process = ProcessBuilder::create($name, App::$server);
            $this->server->addProcess($process);
        }
    }

	protected function registerEventListener() {
		$event = [$this->type, 'task', 'manage'];

		foreach ($event as $name) {
			$event = \iconfig()->getEvent()[$name];
			if (!empty($event)) {
				$this->registerEvent($event);
			}
		}
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
			$this->server->on($eventName, [$object, 'run']);
		}
	}
}