<?php
/**
 * 存储上下文数据，方便调用
 * @author donknap & Swoft\Core
 * @date 18-7-24 下午3:09
 */
namespace W7\Core\Helper;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Coroutine;
use w7\Http\Message\Server\Response;

class Context {
	/**
	 * Key of request context share data
	 */
	const DATA_KEY = 'data';

	/**
	 * Key of current Request
	 */
	const REQUEST_KEY = 'request';

	/**
	 * Key of current Response
	 */
	const RESPONSE_KEY = 'response';

	/**
	 * @var array Coroutine context
	 */
	private static $context;


    /**
     *
     */
    const MIDDLEWARE_KEY = 'middleware_memory';

    /**
     *
     */
    const ROUTE_KEY = "http-route";

	/**
	 * @return \Psr\Http\Message\ServerRequestInterface|null
	 */
	public  function getRequest() {
		return self::getCoroutineContext(self::REQUEST_KEY);
	}

	/**
	 * @return Response|null
	 */
	public  function getResponse() {
		return self::getCoroutineContext(self::RESPONSE_KEY);
	}

	/**
	 * @return array|null
	 */
	public  function getContextData() {
		return self::getCoroutineContext(self::DATA_KEY);
	}

	/**
	 * Set the object of request
	 *
	 * @param RequestInterface $request
	 */
	public  function setRequest(RequestInterface $request) {
		$coroutineId = self::getCoroutineId();
		self::$context[$coroutineId][self::REQUEST_KEY] = $request;
	}

	/**
	 * Set the object of response
	 *
	 * @param ResponseInterface $response
	 */
	public  function setResponse(ResponseInterface $response) {
		$coroutineId = self::getCoroutineId();
		self::$context[$coroutineId][self::RESPONSE_KEY] = $response;
	}

	/**
	 * Set the context data
	 *
	 * @param array $contextData
	 */
	public  function setContextData(array $contextData = []) {
		$existContext = [];
		$coroutineId = self::getCoroutineId();
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
	public  function setContextDataByKey(string $key, $val) {
		$coroutineId = self::getCoroutineId();
		self::$context[$coroutineId][self::DATA_KEY][$key] = $val;
	}

	/**
	 * Get context data by key
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public  function getContextDataByKey(string $key, $default = null) {
		$coroutineId = self::getCoroutineId();
		if (isset(self::$context[$coroutineId][self::DATA_KEY][$key])) {
			return self::$context[$coroutineId][self::DATA_KEY][$key];
		}

		return $default;
	}

    /**
     * Update share context data by key
     *
     * @param string $key
     * @param mixed $val
     */
    public  function setShareContextDataByKey(string $key, $val) {
        /**
         * @var Cache $cacheObj
         */
        $cacheObj = iloader()->singleton(Cache::class);
        if (is_string($val)) {
            $cacheObj->set($key, $val);
        }else{
            $cacheObj->set($key, json_encode($val));
        }
    }

    /**
     * Get share context data by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
	public  function getShareContextDataByKey(string $key, $default = null) {
        /**
         * @var Cache $cacheObj
         */
	    $cacheObj = iloader()->singleton(Cache::class);
        $default = $cacheObj->get($key);
        $default = json_decode($default, true);
        return $default;
    }

	/**
	 * Destroy all current coroutine context data
	 */
	public  function destroy() {
		$coroutineId = self::getCoroutineId();
		if (isset(self::$context[$coroutineId])) {
			unset(self::$context[$coroutineId]);
		}
	}

	/**
	 * Get data from coroutine context by key
	 *
	 * @param string $key key of context
	 * @return mixed|null
	 */
	private  function getCoroutineContext(string $key) {
		$coroutineId = self::getCoroutineId();
		if (!isset(self::$context[$coroutineId])) {
			return null;
		}

		$coroutineContext = self::$context[$coroutineId];
		if (isset($coroutineContext[$key])) {
			return $coroutineContext[$key];
		}
		return null;
	}

	/**
	 * Get current coroutine ID
	 *
	 * @return int|null Return null when in non-coroutine context
	 */
	private  function getCoroutineId() {
		return Coroutine::getuid();
	}
}
