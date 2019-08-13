<?php

namespace W7\Core\Session;

use W7\Core\Session\Channel\ChannelAbstract;
use W7\Core\Session\Channel\CookieChannel;
use W7\Core\Session\Handler\FileHandler;
use W7\Core\Session\Handler\HandlerAbstract;
use W7\Core\Session\Handler\HandlerInterface;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Contract\Session as SessionInterface;
use W7\Http\Message\Server\Response;

class Session implements SessionInterface {
	/**
	 * session name
	 * @var
	 */
	private $name;
	private $config;
	private $beginTime;
	private $expires;
	/**
	 * @var ChannelAbstract
	 */
	private $channel;
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
		$this->setName($this->config['name'] ?? session_name());
	}

	protected function initHandler(Request $request) {
		$handler = $this->config['handler'] ?? FileHandler::class;
		$handler = $this->getHandlerClass($handler);
		$this->handler = new $handler($this->config);
		if (!($this->handler instanceof HandlerAbstract)) {
			throw new \RuntimeException('session handler must instance of HandlerAbstract');
		}
		$this->handler->setId($this->initId($request));
	}

	private function initId(Request $request) {
		$channel = $this->config['channel'] ?? CookieChannel::class;
		$channel = $this->getChannelClass($channel);
		$this->channel = new $channel($request, $this->getName());
		if (!($this->channel instanceof ChannelAbstract)) {
			throw new \RuntimeException('session channel must instance of ChannelAbstract');
		}

		return $this->channel->getId();
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
		return $this->handler->getId(false);
	}

	public function getExpires($interval = false) {
		if ($this->expires === null) {
			$userExpires = (int)($this->config['expires'] ?? ini_get("session.gc_maxlifetime"));
			$this->beginTime = 0;
			if ($userExpires != 0) {
				$this->beginTime = time();
				$userExpires = $this->beginTime + $userExpires;
			}
			$this->expires = $userExpires;
		}
		return $interval ? $this->expires - $this->beginTime : $this->expires;
	}

	public function getConfig() {
		return $this->config;
	}

	public function replenishResponse(Response $response) {
		return $this->channel->replenishResponse($response, $this);
	}

	public function set($key, $value) {
		$this->handler->set($key, $value, $this->getExpires(true));
	}

	public function get($key, $default = '') {
		return $this->handler->get($key, $default);
	}

	public function has($key) {
		return $this->handler->has($key);
	}

	public function destroy() {
		return $this->handler->destroy();
	}

	private function getHandlerClass($handler) {
		$class = sprintf("\\W7\\Core\\Session\\Handler\\%sHandler", ucfirst($handler));
		if (!class_exists($class)) {
			$class = $handler;
		}
		if (!class_exists($class)) {
			throw new \RuntimeException('session not support this handler');
		}

		return $class;
	}

	private function getChannelClass($channel) {
		$class = sprintf("\\W7\\Core\\Session\\Channel\\%sChannel", ucfirst($channel));
		if (!class_exists($class)) {
			$class = $channel;
		}
		if (!class_exists($class)) {
			throw new \RuntimeException('session not support this channel');
		}

		return $class;
	}
}