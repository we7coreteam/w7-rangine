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
	protected array $config;
	protected string $prefix;
	protected static int $gcDivisor;
	protected static int $gcProbability;
	protected ChannelAbstract $channel;
	protected HandlerAbstract $handler;
	protected ?array $cache;
	protected string $sessionId;
	protected bool $useBuiltInUsage;

	public function __construct($config = []) {
		$this->config = $config;
		$this->prefix = $this->config['prefix'] ?? session_name();
	}

	private function initChannel(ServerRequestInterface $request): void {
		$channel = empty($this->config['channel']) ? CookieChannel::class : $this->config['channel'];
		$this->channel = new $channel($this->config, $request);
		if (!($this->channel instanceof ChannelAbstract)) {
			throw new \RuntimeException('session channel must instance of ChannelAbstract');
		}
	}

	private function initHandler(): void {
		if ($this->handler) {
			return;
		}

		$handler = empty($this->config['handler']) ? FileHandler::class : $this->config['handler'];
		$handler = new $handler($this->config);
		if (!($handler instanceof HandlerAbstract)) {
			throw new \RuntimeException('session handler must instance of HandlerAbstract');
		}

		$this->handler = $handler;
	}

	public function getHandler(): HandlerAbstract {
		return $this->handler;
	}

	public function start(ServerRequestInterface $request, $useBuiltUsage = false): void {
		$this->cache = null;
		$this->useBuiltInUsage = $useBuiltUsage;

		$this->initChannel($request);
		$this->initHandler();

		if ($useBuiltUsage) {
			session_set_save_handler($this->getHandler(), true);
		}
	}

	private function builtSessionIsStart(): bool {
		return session_status() === PHP_SESSION_ACTIVE;
	}

	public function getName(): string {
		return $this->channel->getSessionName();
	}

	public function getId(): string {
		if ($this->sessionId) {
			return $this->sessionId;
		}

		if (empty($sessionId = $this->channel->getSessionId())) {
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

	public function setId($sessionId): void {
		$this->validateSessionId($sessionId);
		$this->sessionId = $sessionId;
		$this->cache = null;

		if ($this->useBuiltInUsage && !$this->builtSessionIsStart()) {
			session_id($sessionId);
		}
	}

	public function set($key, $value): bool {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			$_SESSION[$key] = $value;
			return true;
		}

		$data = $this->readSession();
		$data[$key] = $value;
		$this->cache[$key] = $value;
		return $this->handler->write($this->prefix . $this->getId(), $this->handler->pack($data));
	}

	public function has($key): bool {
		$data = $this->readSession();

		return isset($data[$key]);
	}

	public function get($key, $default = '') {
		$data = $this->readSession();

		return $data[$key] ?? $default;
	}

	public function delete($keys): bool {
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

	public function destroy(): bool {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			session_destroy();
			return true;
		}

		$this->cache = null;
		return $this->handler->destroy($this->prefix . $this->getId());
	}

	public function close(): bool {
		if ($this->useBuiltInUsage && $this->builtSessionIsStart()) {
			session_write_close();
			return true;
		}

		return $this->handler->close($this->prefix . $this->getId());
	}

	/**
	 * @throws \Exception
	 */
	public function gc(): void {
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

	/**
	 * @throws \Exception
	 */
	protected function satisfyGcCondition(): bool {
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

	public function validateSessionId($sessionId): void {
		if (!is_string($sessionId) || !ctype_alnum($sessionId)) {
			throw new RuntimeException('Session_id can only be made up of letters or numbers');
		}
	}
}
