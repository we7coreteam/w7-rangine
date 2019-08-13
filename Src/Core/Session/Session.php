<?php

namespace W7\Core\Session;

use W7\Core\Session\Channel\ChannelAbstract;
use W7\Core\Session\Channel\CookieChannel;
use W7\Core\Session\Handler\FileHandler;
use W7\Core\Session\Handler\HandlerAbstract;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;
use W7\Http\Message\Contract\Session as SessionInterface;

class Session implements SessionInterface {
	private $prefix;
	private $config;
	/**
	 * @var ChannelAbstract
	 */
	private $channel;
	/**
	 * @var HandlerAbstract
	 */
	private $handler;


	public function __construct(Request $request) {
		$this->config = iconfig()->getUserAppConfig('session');

		$this->initPrefix();
		$this->initChannel($request);
		$this->initHandler();
	}

	private function initPrefix() {
		$this->prefix = $this->config['prefix'] ?? session_name();
	}

	private function initHandler() {
		$handler = $this->config['handler'] ?? FileHandler::class;
		$handler = $this->getHandlerClass($handler);
		$this->handler = new $handler($this->config);
		if (!($this->handler instanceof HandlerAbstract)) {
			throw new \RuntimeException('session handler must instance of HandlerAbstract');
		}
	}

	private function initChannel(Request $request) {
		$channel = $this->config['channel'] ?? CookieChannel::class;
		$channel = $this->getChannelClass($channel);
		$this->channel = new $channel($this->config, $request);
		if (!($this->channel instanceof ChannelAbstract)) {
			throw new \RuntimeException('session channel must instance of ChannelAbstract');
		}
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

	public function getId() {
		return $this->channel->getSessionId();
	}

	public function set($key, $value) {
		$data = unserialize($this->handler->read($this->prefix . $this->getId()));
		$data[$key] = $value;
		return $this->handler->write($this->prefix . $this->getId(), serialize($data));
	}

	public function get($key, $default = '') {
		try{
			$data = unserialize($this->handler->read($this->prefix . $this->getId()));
		} catch (\Throwable $e) {
			$data = [];
		}
		return $data[$key] ?? $default;
	}

	public function destroy() {
		return $this->handler->destroy($this->prefix . $this->getId());
	}

	public function replenishResponse(Response $response) {
		return $this->channel->replenishResponse($response);
	}
}