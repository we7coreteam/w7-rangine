<?php

namespace W7\Core\Session;

use W7\Core\Session\Handler\HandlerInterface;
use W7\Core\Session\Handler\RedisHandler;
use W7\Http\Message\Server\Request;

class Session {
	/**
	 * session name
	 * @var
	 */
	private $name;
	/**
	 * session id
	 * @var
	 */
	private $id;
	private $expires;
	/**
	 * @var HandlerInterface
	 */
	private $handler;


	public function __construct(Request $request, HandlerInterface $handler = null) {
		$this->init();

		$cookies = $request->getCookieParams();
		if (empty($cookies[$this->getName()])) {
			$cookies[$this->getName()] = $this->generateId();
		}
		$this->setId($cookies[$this->getName()]);

		$this->handler = $handler ?? new RedisHandler();
	}

	protected function init() {
		$config = iconfig()->getUserAppConfig('session');
		$this->setName($config['name'] ?? 'PHP_SESSION_ID');
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function getExpires() {
		return $this->expires;
	}

	private function generateId() {
		return \session_create_id();
	}

	private function getKey($key) {
		return $this->name . ':' . $this->id . ':' . $key;
	}

	public function set($key, $value, $expires = 0) {
		$this->expires = $expires;
		$this->handler->set($this->getKey($key), $value, $this->expires);
	}

	public function get($key) {
		return $this->handler->get($this->getKey($key));
	}

	public function clear() {
		return $this->handler->clear();
	}
}