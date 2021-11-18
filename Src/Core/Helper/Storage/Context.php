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

namespace W7\Core\Helper\Storage;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Coroutine;
use W7\Http\Message\Server\Request;
use W7\Http\Message\Server\Response;

class Context {
	/**
	 * Key of request context share data
	 */
	public const DATA_KEY = 'data';

	/**
	 * Key of current Request
	 */
	public const REQUEST_KEY = 'request';

	/**
	 * Key of current Response
	 */
	public const RESPONSE_KEY = 'response';

	/**
	 * @var array Coroutine context
	 */
	private static array $context;

	private array $corDeferRegisterMap;

	/**
	 * The coroutine number last requested
	 * @var int
	 */
	private int $lastCoId;

	public function defer(Closure $closure): void {
		Coroutine::defer($closure);
	}

	/**
	 * @return Request|null
	 */
	public function getRequest(): ?Request {
		return $this->getCoroutineContext(self::REQUEST_KEY);
	}

	/**
	 * @return Response|null
	 */
	public function getResponse(): ?Response {
		return $this->getCoroutineContext(self::RESPONSE_KEY);
	}

	/**
	 * @return array|null
	 */
	public function getContextData(): ?array {
		return $this->getCoroutineContext(self::DATA_KEY);
	}

	/**
	 * Set the object of request
	 *
	 * @param RequestInterface $request
	 */
	public function setRequest(RequestInterface $request): void {
		$coroutineId = $this->getCoroutineId();
		self::$context[$coroutineId][self::REQUEST_KEY] = $request;
	}

	/**
	 * Set the object of response
	 *
	 * @param ResponseInterface $response
	 */
	public function setResponse(ResponseInterface $response): void {
		$coroutineId = $this->getCoroutineId();
		self::$context[$coroutineId][self::RESPONSE_KEY] = $response;
	}

	/**
	 * Set the context data
	 *
	 * @param array $contextData
	 */
	public function setContextData(array $contextData = []): void {
		$existContext = [];
		$coroutineId = $this->getCoroutineId();
		if (isset(self::$context[$coroutineId][self::DATA_KEY])) {
			$existContext = self::$context[$coroutineId][self::DATA_KEY];
		}
		self::$context[$coroutineId][self::DATA_KEY] = array_merge([], $contextData, $existContext);
	}

	/**
	 * Update context data by key
	 *
	 * @param string $key
	 * @param mixed $val
	 */
	public function setContextDataByKey(string $key, mixed $val): void {
		$coroutineId = $this->getCoroutineId();
		self::$context[$coroutineId][self::DATA_KEY][$key] = $val;
	}

	/**
	 * Get context data by key
	 *
	 * @param string $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function getContextDataByKey(string $key, mixed $default = null): mixed {
		$data = $this->getContextData();
		if ($data && isset($data[$key])) {
			return $data[$key];
		}

		return $default;
	}

	public function fork($parentCoId): void {
		self::$context[$this->getCoroutineId()] = self::$context[$parentCoId] ?? [];
	}

	/**
	 * Destroy all current coroutine context data
	 */
	public function destroy($coroutineId = null): void {
		if (!$coroutineId) {
			$coroutineId = $this->getCoroutineId();
		}
		
		if (isset(self::$context[$coroutineId])) {
			unset(self::$context[$coroutineId]);
		}
	}

	public function all(): array {
		return self::$context ?? [];
	}

	/**
	 * Get data from coroutine context by key
	 *
	 * @param string $key key of context
	 * @return mixed
	 */
	private function getCoroutineContext(string $key): mixed {
		$coroutineId = $this->getCoroutineId();
		if (!isset(self::$context[$coroutineId])) {
			return null;
		}

		$coroutineContext = self::$context[$coroutineId];
		return $coroutineContext[$key] ?? null;
	}

	/**
	 * Get current coroutine ID
	 *
	 * @return int Return null when in non-coroutine context
	 */
	public function getCoroutineId(): int {
		if (method_exists(Coroutine::class, 'getuid')) {
			$coId = Coroutine::getuid();
		} else {
			$coId = -1;
		}

		if ($coId > 0 && empty($this->corDeferRegisterMap[$coId])) {
			$this->corDeferRegisterMap[$coId] = true;
			$this->defer(function () {
				$this->destroy();
				unset($this->corDeferRegisterMap[Coroutine::getuid()]);
			});
		}
		if ($coId !== -1) {
			$this->lastCoId = $coId;
		}

		return $coId;
	}

	public function getLastCoId(): int {
		return $this->lastCoId;
	}
}
