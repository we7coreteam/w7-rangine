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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use W7\Contract\Session\SessionInterface;
use W7\Core\Session\Channel\ChannelAbstract;
use W7\Core\Session\Channel\CookieChannel;
use W7\Core\Session\Handler\FileHandler;
use W7\Core\Session\Handler\HandlerAbstract;

class Session implements SessionInterface {
	protected $config;
	protected $prefix;
	protected static $gcDivisor;
	protected static $gcProbability;
	/**
	 * @var ChannelAbstract
	 */
	protected $channel;
	/**
	 * @var HandlerAbstract
	 */
	protected $handler;
	protected $cache;
	protected $sessionId;
	protected $useBuiltInUsage = null;

	public function __construct($config = []) {
		$this->config = $config;
		$this->prefix = $this->config['prefix'] ?? session_name();
	}

	private function initChannel(ServerRequestInterface $request) {
		$channel = empty($this->config['channel']) ? CookieChannel::class : $this->config['channel'];
		$this->channel = new $channel($this->config, $request);
		if (!($this->channel instanceof ChannelAbstract)) {
			throw new \RuntimeException('session channel must instance of ChannelAbstract');
		}
	}

	private function initHandler() {
		if ($this->handler) {
			return true;
		}

		$handler = empty($this->config['handler']) ? FileHandler::class : $this->config['handler'];
		$handler = new $handler($this->config);
		if (!($handler instanceof HandlerAbstract)) {
			throw new \RuntimeException('session handler must instance of HandlerAbstract');
		}

		$this->handler = $handler;
	}

	public function getHandler() {
		return $this->handler;
	}

	public function start(ServerRequestInterface $request, $useBuiltUsage = false) {
		$this->cache = null;
		$this->useBuiltInUsage = $useBuiltUsage;

		$this->initChannel($request);
		$this->initHandler();

		if ($useBuiltUsage) {
			//第二个参数表示shudown后，保存session数据并执行close，释放session锁，不释放会导致同一个sessionid的请求处于等待状态(session_start被调用的时候，该文件是被锁住的)
			session_set_save_handler($this->getHandler(), true);
		}
	}

	private function builtSessionIsStart() {
		return session_status() == PHP_SESSION_ACTIVE;
	}

	public function getName() {
		return $this->channel->getSessionName();
	}

	public function getId() {
		if ($this->sessionId) {
			return $this->sessionId;
		}

		if (empty($sessionId = $this->channel->getSessionId())) {
			//加环境检测原因
			//１：fpm下session_start会自动触发create_sid,再次调用会报错
			//２：保证handler的create_sid功能单一
			//３：不在fpm下单独处理，涉及到channel setSessionId问题
			if (!$this->useBuiltInUsage) {
				$sessionId = $this->handler->create_sid();
			} else {
				$sessionId = session_id();
				if (!$sessionId) {
					$sessionId = $this->handler->create_sid();
				}
			}
		}
		$this->validateSessionId($sessionId);
		$this->sessionId = $sessionId;

		return $this->sessionId;
	}

	public function setId($sessionId) {
		$this->validateSessionId($sessionId);
		$this->sessionId = $sessionId;
		$this->cache = null;

		if ($this->useBuiltInUsage && !$this->builtSessionIsStart()) {
			session_id($sessionId);
		}
	}

	public function set($key, $value) {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			$_SESSION[$key] = $value;
			return true;
		}

		$data = $this->readSession();
		$data[$key] = $value;
		$this->cache[$key] = $value;
		return $this->handler->write($this->prefix . $this->getId(), $this->handler->pack($data));
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
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			foreach ($keys as $key) {
				if (isset($_SESSION[$key])) {
					unset($_SESSION[$key]);
				}
			}
			return true;
		}

		$sessionData = $this->readSession();
		foreach ($keys as $key) {
			if (isset($sessionData[$key])) {
				unset($sessionData[$key], $this->cache[$key]);
			}
		}

		if ($sessionData) {
			return $this->handler->write($this->prefix . $this->getId(), $this->handler->pack($sessionData));
		}

		return $this->destroy();
	}

	public function all() {
		return $this->readSession();
	}

	public function destroy() {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			session_destroy();
			return true;
		}

		$this->cache = null;
		return $this->handler->destroy($this->prefix . $this->getId());
	}

	public function close() {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			session_write_close();
			return true;
		}

		return $this->handler->close($this->prefix . $this->getId());
	}

	public function gc() {
		if ($this->satisfyGcCondition()) {
			igo(function () {
				if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
					return true;
				}
				$this->handler->gc($this->handler->getExpires());
			});
		}
	}

	public function replenishResponse(ResponseInterface $response) : ResponseInterface {
		return $this->channel->replenishResponse($response, $this->getId());
	}

	protected function readSession() {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			return $_SESSION;
		}

		if (isset($this->cache)) {
			return $this->cache;
		}

		try {
			$data = $this->handler->unpack($this->handler->read($this->prefix . $this->getId()));
			$data = !is_array($data) ? [] : $data;
		} catch (\Throwable $e) {
			$data = [];
		}
		$this->cache = $data;

		return $data;
	}

	protected function satisfyGcCondition() {
		if (!self::$gcDivisor) {
			$gcDivisor = (int)($this->config['gc_divisor'] ?? ini_get('session.gc_divisor'));
			self::$gcDivisor = $gcDivisor <= 0 ? 1 : $gcDivisor;
		}
		if (!self::$gcProbability) {
			$gcProbability = (int)($this->config['gc_probability'] ?? ini_get('session.gc_probability'));
			self::$gcProbability = $gcProbability <= 0 ? 1 : $gcProbability;
		}

		return random_int(1, self::$gcDivisor) <= self::$gcProbability;
	}

	public function validateSessionId($sessionId) {
		if (!is_string($sessionId) || !ctype_alnum($sessionId)) {
			throw new RuntimeException('Session_id can only be made up of letters or numbers');
		}
	}
}
