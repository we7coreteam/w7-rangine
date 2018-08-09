<?php
/**
 * author: alex
 * date: 18-8-2 下午7:29
 */

namespace W7\Core\Log;

use W7\Core\Helper\Context;

class LogHelper
{
	/**
	 * @var Context $contextObj
	 */
	private $contextObj;

	public function __construct()
	{
		$this->contextObj = iloader()->singleton(Context::class);
	}

	public function beforeRequestInit()
	{
		/**
		 * @var Context $contextObj
		 */
		$contextObj = iloader()->singleton(Context::class);
		$requestLogContextData = $contextObj->getContextDataByKey(Context::LOG_REQUEST_KEY);

		$spanid = rand(1000000, 9999999);
		$logid = iuuid();
		ilogger()->addBasic("logid", $logid);
		ilogger()->addBasic("spanid", $spanid);
		ilogger()->addBasic("controller", $requestLogContextData['controller']);
		ilogger()->addBasic('method', $requestLogContextData['method']);
		$requestLogContextData['logid'] = $logid;
		$requestLogContextData['spanid'] = $spanid;
		$contextObj->setContextDataByKey(Context::LOG_REQUEST_KEY, $requestLogContextData);
	}

	public function beforeTask($logid, $spanid, $taskName, $taskMethod)
	{
		ilogger()->addBasic("logid", $logid);
		ilogger()->addBasic("spanid", $spanid);
		ilogger()->addBasic("controller", $taskName);
		ilogger()->addBasic('method', $taskMethod);
	}

	/**
	 * @param $errcode
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param $errcontext
	 */
	public function errorHandler($errcode, $errstr, $errfile, $errline, $errcontext)
	{
		ilogger()->fatal('errcode:%d, errstr:%s, errfile:%s, errline:%s', $errcode, $errstr, $errfile, $errline);
	}

	/**
	 * @param \Throwable $exception
	 */
	public function exceptionHandler(\Throwable $exception)
	{
		ilogger()->warning("exception msg is %s code is %s", $exception->getMessage(), $exception->getCode());
	}

	public function appendNoticeLog()
	{
		// php耗时单位ms毫秒
		$timeUsed = sprintf('%.2f', (microtime(true) - $this->getRequestTime()) * 1000);
		// php运行内存大小单位M
		$memUsed = sprintf('%.0f', memory_get_peak_usage() / (1024 * 1024));
		ilogger()->notice("memory_cross: %s(MB), request_cross cost: %d(ms), request_url: %s", $memUsed, $timeUsed, $this->getUri());
	}

	/**
	 * 请求开始时间
	 *
	 * @return int
	 */
	private function getRequestTime(): int
	{
		$contextData = $this->contextObj->getContextDataByKey(Context::LOG_REQUEST_KEY);
		return $contextData['requestTime'] ?? 0;
	}

	/**
	 * 请求开始时间
	 *
	 * @return int
	 */
	private function getUri(): string
	{
		$contextData = $this->contextObj->getContextDataByKey(Context::LOG_REQUEST_KEY);
		return $contextData['url'] ?? 0;
	}
}
