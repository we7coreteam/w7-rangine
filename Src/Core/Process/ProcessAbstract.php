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

namespace W7\Core\Process;

use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Process;
use Swoole\Timer;
use W7\App;
use W7\Console\Io\Output;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Helper\Traiter\AppCommonTrait;
use W7\Core\Helper\Traiter\TaskDispatchTrait;
use W7\Core\Message\Message;
use W7\Core\Message\TaskMessage;

abstract class ProcessAbstract {
	use AppCommonTrait;
	use TaskDispatchTrait;

	protected string $name = 'process';
	protected int $num = 1;
	protected int $workerId;
	protected int $mqKey;
	protected ?Process $process;
	protected $pipe;
	protected Coroutine\Channel $channel;

	public function __construct($name, $num = 1, Process $process = null) {
		$this->name = $name;
		$this->num = $num;
		$this->process = $process;

		$this->init();
	}

	protected function init() {
	}

	public function getName(): string {
		return $this->name;
	}

	public function setWorkerId($workerId): void {
		$this->workerId = $workerId;
	}

	public function getWorkerId(): int {
		return $this->workerId;
	}

	public function setProcess(Process $process): void {
		$this->process = $process;
	}

	public function getProcess(): ?Process {
		return $this->process;
	}

	public function getProcessName(): string {
		$name = App::$server->getPname() . $this->name;
		if ($this->num > 1) {
			$name .= '-' . ($this->process->id % $this->num);
		}

		return $name;
	}

	public function setMq($key = 0, $mode = 2 | Process::IPC_NOWAIT): void {
		$this->mqKey = $key;
		$this->process->useQueue($key, $mode);
	}

	abstract public function check();

	protected function beforeStart() {
	}

	public function onStart(): void {
		$this->beforeStart();

		$this->channel = new Coroutine\Channel(1);

		$this->listen();
		$this->start();
	}

	private function listen(): void {
		Coroutine::create(function () {
			/**
			 * @var Coroutine\Socket $socket
			 */
			while ($this->channel->pop(0.01) !== true) {
				$socket = $this->getProcess()->exportSocket();
				try {
					$data = $socket->recv();
					if ($data === '') {
						throw new \Exception('process socket is closed', $socket->errCode);
					}

					if ($data === false && $socket->errCode !== SOCKET_ETIMEDOUT) {
						throw new \Exception('process socket is closed', $socket->errCode);
					}

					$message = Message::unpack($data);
					if ($message instanceof TaskMessage) {
						$this->dispatchNow($message);
					}
				} catch (\Throwable $e) {
					$this->getContainer()->get(HandlerExceptions::class)->getHandler()->report($e);
				}
			}
			$this->channel->close();
		});
	}

	private function start(): void {
		if (method_exists($this, 'read')) {
			$pipe = $this->pipe ?: $this->process->pipe;
			Event::add($pipe, function () {
				$this->doRun(function () {
					$data = $this->pipe ? '' : $this->process->read();
					if (!$this->read($data)) {
						Event::del($this->pipe ? $this->pipe : $this->process->pipe);
					}
				});
			});
		} else {
			$this->doRun(function () {
				$this->run($this->process);
			});
		}
	}

	private function doRun(\Closure $callback): void {
		try {
			$callback();
			$this->stopProcessIfNecessary();
		} catch (\Throwable $throwable) {
			if ((ENV & DEBUG) === DEBUG) {
				(new Output())->error($throwable->getMessage() . ' at file ' . $throwable->getFile() . ' line ' . $throwable->getLine());
			}
			$this->getContainer()->get(HandlerExceptions::class)->getHandler()->report($throwable);

			$this->stop();
		}
	}

	private function stopProcessIfNecessary(): void {
		$timerNum = 0;
		foreach (Timer::list() as $item) {
			++$timerNum;
		}
		if ($timerNum === 0) {
			$this->stop();
		}
	}

	/**
	 * @throws \Exception
	 */
	private function stop(): void {
		$this->getLogger()->debug('process ' . $this->getProcessName() . ' exit');

		$this->channel->push(true);
		Timer::clearAll();
		$this->getProcess()?->exit();
	}

	abstract protected function run(Process $process);

	/**
	 * process->push(msg) bug
	 * @param $msg
	 */
	public function sendMsg($msg): bool {
		if (version_compare(SWOOLE_VERSION, '4.4.5', '>=')) {
			$result = $this->process->push($msg);
		} else {
			if (!extension_loaded('sysvmsg')) {
				throw new \RuntimeException('extension sysvmsg is deletion');
			}
			$result = msg_send(msg_get_queue($this->mqKey), 1, $msg, false);
		}
		return $result;
	}

	public function readMsg($size = null) {
		return $this->getProcess()?->pop($size);
	}
}
