<?php
/**
 * 服务父类，实现一些公共操作
 * @author donknap
 * @date 18-7-20 上午9:32
 */

namespace W7\Core\Base;

use W7\Core\Exception\CommandException;
use W7\Core\Listener\ManageServerListener;

abstract class ServerAbstract implements ServerInterface {

	use ManageServerListener;

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

		$setting = \iconfig()->getServer();

		if (empty($setting[$this->type]) || empty($setting[$this->type]['host'])) {
			throw new CommandException(sprintf('缺少服务配置 %s', $this->type));
		}
		$this->setting = array_merge([], $setting['common']);
		$this->connection = $setting[$this->type];
	}

	public function getStatus() {
		return [
			'host' => $this->connection['host'],
			'port' => $this->connection['port'],
			'type' => $this->connection['sock_type'],
			'mode' => $this->connection['mode'],
			'workerNum' => $this->setting['worker_num'],
		];
	}

	public function getServer() {
		return $this->server;
	}

	public function isRun() {

	}

	protected function registerServerEvent() {
		$event = \iconfig()->getEvent()[$this->type];
		$this->registerEvent($event);
	}

	protected function registerTaskEvent() {
		$event = \iconfig()->getEvent()['task'];
		$this->registerEvent($event);
	}

	private function registerEvent($event) {
		if (empty($event)) {
			return true;
		}
		foreach ($event as $eventName => $class) {
			$object = \W7\App::getLoader()->singleton($class);
			$this->server->on($eventName, [$object, 'on' . ucfirst($eventName)]);
		}
	}
}