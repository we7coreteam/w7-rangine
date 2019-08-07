<?php

namespace W7\Core\Session;

use W7\Core\Session\Channel\ChannelAbstract;
use W7\Core\Session\Channel\CookieChannel;
use W7\Core\Session\Handler\HandlerAbstract;
use W7\Core\Session\Handler\HandlerInterface;
use W7\Core\Session\Handler\RedisHandler;
use W7\Http\Message\Server\Request;

class Session {
	/**
	 * session name
	 * @var
	 */
	private $name;
	private $config;
	/**
	 * 过期时间
	 * @var
	 */
	private $expires;
	/**
	 * @var HandlerInterface
	 */
	private $handler;


	public function __construct(Request $request) {
		$this->config = iconfig()->getUserAppConfig('session');

		$this->initName();
		$this->initHandler($request);
	}

	protected function initName() {
		$this->setName($this->config['name'] ?? 'PHPSESSID');
	}

	protected function initHandler(Request $request) {
		$handler = $this->config['handler'] ?? RedisHandler::class;
		$this->handler = new $handler();
		if (!($this->handler instanceof HandlerAbstract)) {
			throw new \Exception('session handler must instance of HandlerAbstract');
		}
		$this->handler->setId($this->initId($request));
	}

	private function initId(Request $request) {
		$channel = $this->config['channel'] ?? CookieChannel::class;
		$channel = new $channel($request, $this->getName());
		if (!($channel instanceof ChannelAbstract)) {
			throw new \Exception('session channel must instance of CookieChannel');
		}

		return $channel->getId();
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setId($id) {
		$this->handler->setId($id);
	}

	public function getId() {
		return $this->handler->getId();
	}

	public function getExpires() {
		return $this->expires;
	}

	public function set($key, $value, $expires = 0) {
		$this->expires = $expires;
		$this->handler->set($key, $value, $this->expires);
	}

	public function get($key, $default = '') {
		return $this->handler->get($key, $default);
	}

	public function clear() {
		return $this->handler->clear();
	}
}