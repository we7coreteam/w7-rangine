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

namespace W7\Core\Session;

use W7\Core\Session\Channel\ChannelAbstract;
use W7\Core\Session\Event\SessionStartEvent;
use W7\Core\Session\Handler\HandlerAbstract;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;
use W7\Http\Message\Contract\Session as SessionInterface;

class Session implements SessionInterface {
	private $config;
	private $prefix;
	private static $gcCondition;
	private static $channelClass;
	/**
	 * @var ChannelAbstract
	 */
	private $channel;
	/**
	 * @var HandlerAbstract
	 */
	private static $handler;
	private $cache;

	public function __construct() {
		$this->config = iconfig()->getUserAppConfig('session');

		$this->initPrefix();
	}

	private function initPrefix() {
		$this->prefix = $this->config['prefix'] ?? session_name();
	}

	private function initHandler() {
		if (self::$handler) {
			return true;
		}

		$handler = $this->getHandlerClass();
		$handler = new $handler($this->config);
		if (!($handler instanceof HandlerAbstract)) {
			throw new \RuntimeException('session handler must instance of HandlerAbstract');
		}
		self::$handler = $handler;
	}

	private function getHandlerClass() {
		$handler = $this->config['handler'] ?? 'file';
		$class = sprintf('\\W7\\Core\\Session\\Handler\\%sHandler', ucfirst($handler));
		if (!class_exists($class)) {
			$class = sprintf('\\W7\\App\\Handler\\Session\\%sHandler', ucfirst($handler));
		}
		if (!class_exists($class)) {
			throw new \RuntimeException('session handler ' . $handler . ' is not support');
		}

		return $class;
	}

	private function initChannel(Request $request) {
		$channel = $this->getChannelClass();
		$this->channel = new $channel($this->config, $request);
		if (!($this->channel instanceof ChannelAbstract)) {
			throw new \RuntimeException('session channel must instance of ChannelAbstract');
		}
	}

	private function getChannelClass() {
		if (!self::$channelClass) {
			$channel = $this->config['channel'] ?? 'cookie';
			$class = sprintf('\\W7\\Core\\Session\\Channel\\%sChannel', ucfirst($channel));
			if (!class_exists($class)) {
				$class = sprintf('\\W7\\App\\Channel\\Session\\%sChannel', ucfirst($channel));
			}
			if (!class_exists($class)) {
				throw new \RuntimeException('session not support this channel');
			}
			self::$channelClass = $class;
		}

		return self::$channelClass;
	}

	private function getGcCondition() {
		if (!self::$gcCondition) {
			$gcDivisor = (int)($this->config['gc_divisor'] ?? ini_get('session.gc_divisor'));
			$gcDivisor = $gcDivisor <= 0 ? 1 : $gcDivisor;
			$gcProbability = (int)($this->config['gc_probability'] ?? ini_get('session.gc_probability'));
			$gcProbability = $gcProbability <= 0 ? 1 : $gcProbability;

			self::$gcCondition = $gcDivisor / $gcProbability;
		}

		return self::$gcCondition;
	}

	public function getId() {
		return $this->channel->getSessionId();
	}

	public function setId($sessionId) {
		$this->channel->setSessionId($sessionId);
		$this->cache = null;
	}

	private function readSession() {
		//只读一次, 防止在临界点上,第一次读有数据,第二次读不到
		if (isset($this->cache)) {
			return $this->cache;
		}

		try {
			$data = self::$handler->unpack(self::$handler->read($this->prefix . $this->getId()));
			$data = !is_array($data) ? [] : $data;
		} catch (\Throwable $e) {
			$data = [];
		}
		$this->cache = $data;

		return $data;
	}

	public function start(Request $request) {
		$this->initChannel($request);
		$this->initHandler();

		ievent(new SessionStartEvent($this));
	}

	public function set($key, $value) {
		$data = $this->readSession();

		$data[$key] = $value;
		$this->cache[$key] = $value;
		return self::$handler->write($this->prefix . $this->getId(), self::$handler->pack($data));
	}

	public function has($key) {
		$data = $this->readSession();

		return isset($data[$key]);
	}

	public function get($key, $default = '') {
		$data = $this->readSession();

		return $data[$key] ?? $default;
	}

	public function delete($keys) {
		$keys = (array)$keys;
		$sessionData = $this->readSession();
		foreach ($keys as $key) {
			if (isset($sessionData[$key])) {
				unset($sessionData[$key], $this->cache[$key]);
			}
		}

		if ($sessionData) {
			return self::$handler->write($this->prefix . $this->getId(), self::$handler->pack($sessionData));
		}

		return $this->destroy();
	}

	public function all() {
		return $this->readSession();
	}

	public function destroy() {
		$this->cache = null;
		return self::$handler->destroy($this->prefix . $this->getId());
	}

	public function gc() {
		static $requestNum;
		++$requestNum;
		$condition = $this->getGcCondition();
		if ($requestNum > $condition) {
			$requestNum = 0;
			igo(function () {
				self::$handler->gc(self::$handler->getExpires());
			});
		}
	}

	public function replenishResponse(Response $response) {
		return $this->channel->replenishResponse($response);
	}
}
