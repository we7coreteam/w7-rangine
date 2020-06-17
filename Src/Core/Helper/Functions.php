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

use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Swoole\Coroutine;
use Symfony\Component\VarDumper\VarDumper;
use W7\App;
use W7\Core\Facades\Event;
use W7\Core\Dispatcher\TaskDispatcher;
use W7\Core\Exception\DumpException;
use W7\Core\Exception\ValidatorException;
use W7\Console\Io\Output;
use W7\Core\Facades\Container;
use W7\Core\Facades\Context;
use W7\Core\Facades\Logger;
use W7\Core\Facades\Router;
use W7\Core\Facades\DB;
use W7\Core\Message\TaskMessage;
use Swoole\Timer;

if (!function_exists('ieventDispatcher')) {
	function ieventDispatcher() {
		/**
		 * @deprecated
		 * @var \W7\Core\Events\Dispatcher $dispatcher
		 */
		return Event::getFacadeRoot();
	}
}

if (!function_exists('ievent')) {
	/**
	 * 派发一个事件
	 * @deprecated
	 * @param $eventName
	 * @param array $args
	 * @param bool $halt
	 * @return array|null
	 */
	function ievent($eventName, $args = [], $halt = false) {
		return Event::dispatch($eventName, $args, $halt);
	}
}
if (!function_exists('iloader')) {

	/**
	 * 别名
	 * @deprecated
	 * @return \W7\Core\Container\Container
	 */
	function iloader() {
		return icontainer();
	}

	/**
	 * 获取容器
	 * @deprecated
	 * @return \W7\Core\Container\Container
	 */
	function icontainer() {
		return Container::getFacadeRoot();
	}
}

if (!function_exists('ioutputer')) {
	/**
	 * 获取输出对象
	 * @deprecated
	 * @return W7\Console\Io\Output
	 */
	function ioutputer() {
		return Container::singleton(Output::class);
	}
}

if (!function_exists('iconfig')) {
	/**
	 * 输入对象
	 * @deprecated
	 * @return \W7\Core\Config\Config
	 */
	function iconfig() {
		return App::getApp()->getConfigger();
	}
}

if (!function_exists('ilogger')) {
	/**
	 * 返回logger对象
	 * @deprecated
	 * @return \W7\Core\Log\Logger
	 */
	function ilogger() {
		return App::getApp()->getLogger();
	}
}

if (!function_exists('idb')) {
	/**
	 * 返回一个数据库连接对象
	 * @deprecated
	 * @return \W7\Core\Database\DatabaseManager
	 */
	function idb() {
		return DB::getFacadeRoot();
	}
}

if (!function_exists('icontext')) {
	/**
	 * 返回logger对象
	 * @deprecated
	 * @return \W7\Core\Helper\Storage\Context
	 */
	function icontext() {
		return App::getApp()->getContext();
	}
}

if (!function_exists('icache')) {
	/**
	 * @deprecated
	 * @return \W7\Core\Cache\Cache
	 */
	function icache() {
		return App::getApp()->getCacher();
	}
}

if (!function_exists('irouter')) {
	/**
	 * @deprecated
	 * @return \W7\Core\Route\Router
	 */
	function irouter() {
		return Router::getFacadeRoot();
	}
}

if (!function_exists('itask')) {
	/**
	 * 派发一个异步任务
	 * @param string $taskName
	 * @param array $params
	 * @param int $timeout
	 * @return false|int
	 * @throws \W7\Core\Exception\TaskException
	 */
	function itask($taskName, $params = [], int $timeout = 3) {
		//构造一个任务消息
		$taskMessage = new TaskMessage();
		$taskMessage->task = $taskName;
		$taskMessage->params = $params;
		$taskMessage->timeout = $timeout;
		$taskMessage->type = TaskMessage::OPERATION_TASK_ASYNC;
		/**
		 * @var TaskDispatcher $dispatcherMaker
		 */
		$dispatcherMaker = Container::singleton(TaskDispatcher::class);
		return $dispatcherMaker->register($taskMessage);
	}

	function itaskCo($taskName, $params = [], int $timeout = 3) {
		//构造一个任务消息
		$taskMessage = new TaskMessage();
		$taskMessage->task = $taskName;
		$taskMessage->params = $params;
		$taskMessage->timeout = $timeout;
		$taskMessage->type = TaskMessage::OPERATION_TASK_CO;
		/**
		 * @var TaskDispatcher $dispatcherMaker
		 */
		$dispatcherMaker = Container::singleton(TaskDispatcher::class);
		return $dispatcherMaker->registerCo($taskMessage);
	}
}

if (!function_exists('iuuid')) {
	/**
	 * 获取UUID
	 * @return string
	 */
	function iuuid() {
		$len = rand(2, 16);
		$prefix = md5(substr(md5(Context::getCoroutineId()), $len));
		return uniqid($prefix);
	}
}

if (!function_exists('isCo')) {
	/**
	 * 是否是在协成
	 * @return bool
	 */
	function isCo():bool {
		return Context::getCoroutineId() > 0;
	}
}

if (!function_exists('getClientIp')) {
	function getClientIp() {
		$request = Context::getRequest();

		$serverParams = $request->getServerParams();
		if (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
			$arr = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);
			$pos = array_search('unknown', $arr);
			if (false !== $pos) {
				unset($arr[$pos]);
			}
			$ip = trim($arr[0]);
		} elseif (!empty($serverParams['HTTP_CLIENT_IP'])) {
			$ip = $serverParams['HTTP_CLIENT_IP'];
		} elseif (!empty($serverParams['REMOTE_ADDR'])) {
			$ip = $serverParams['REMOTE_ADDR'];
		} elseif ($request->getHeader('X-Forwarded-For')) {
			$ip = $request->getHeader('X-Forwarded-For')[0];
		} elseif ($request->getHeader('X-Real-IP')) {
			$ip = $request->getHeader('X-Real-IP')[0];
		} else {
			$ip = $request->getSwooleRequest()->server['remote_addr'];
		}

		return $ip;
	}
}

if (!function_exists('isWorkerStatus')) {
	function isWorkerStatus() {
		if (App::$server === null) {
			return false;
		}

		$server = App::$server->getServer();
		if ($server->manager_pid == 0) {
			return false;
		}
		if ($server && \property_exists($server, 'taskworker') && ($server->taskworker === false)) {
			return true;
		}

		return false;
	}
}

if (!function_exists('isetProcessTitle')) {
	function isetProcessTitle($title) {
		if (\stripos(PHP_OS, 'Darwin') !== false) {
			return true;
		}
		if (\function_exists('cli_set_process_title')) {
			return cli_set_process_title($title);
		}

		if (\function_exists('swoole_set_process_name')) {
			return swoole_set_process_name($title);
		}
		return true;
	}
}

if (!function_exists('irandom')) {
	function irandom($length, $numeric = false) {
		$seed = base_convert(md5(microtime()), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
		if ($numeric) {
			$hash = '';
		} else {
			$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
			$length--;
		}
		$max = strlen($seed) - 1;
		for ($i = 0; $i < $length; $i++) {
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}
}

if (!function_exists('idd')) {
	function idd(...$vars) {
		ob_start();
		if (class_exists(VarDumper::class)) {
			$_SERVER['VAR_DUMPER_FORMAT'] = 'html';
			foreach ($vars as $var) {
				VarDumper::dump($var);
			}
			VarDumper::setHandler(null);
		} else {
			foreach ($vars as $var) {
				echo '<pre>';
				print_r($var);
				echo '</pre>';
			}
		}
		$content = ob_get_clean();

		throw new DumpException($content);
	}
}

if (!function_exists('ienv')) {
	function ienv($key, $default = null) {
		$value = getenv($key);

		if ($value === false) {
			return value($default);
		}

		switch (strtolower($value)) {
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}

		//约定如要env中要写常量名称的话必须要大写
		if (preg_match('/([A-Z]+[\|\^\&])+/', $value)) {
			//常量解析
			$exec = 'return ' . $value . ';';
			try {
				$result = @eval($exec);
				if ($result !== false && $result !== "\0\0\0\0\0\0") {
					$value = $result;
				}
			} catch (Throwable $e) {
				//
			}
		}
		if (defined($value)) {
			$value = constant($value);
		}

		if (is_string($value) && ($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
			return substr($value, 1, -1);
		}

		return $value;
	}
}
if (!function_exists('ivalidate')) {
	function ivalidate(array $data, array $rules, array $messages = [], array $customAttributes = []) {
		try {
			/**
			 * @var Factory $validate
			 */
			$result = \W7\Core\Facades\Validator::make($data, $rules, $messages, $customAttributes)
				->validate();
		} catch (ValidationException $e) {
			$errorMessage = [];
			$errors = $e->errors();
			foreach ($errors as $field => $message) {
				$errorMessage[] = $message[0];
			}
			throw new ValidatorException(implode('; ', $errorMessage), 403);
		}

		return $result;
	}
}
if (!function_exists('ivalidator')) {
	/**
	 * @deprecated
	 * @return Factory
	 */
	function ivalidator() : Factory {
		$validator = Container::singleton(Factory::class);
		return $validator;
	}
}
if (!function_exists('igo')) {
	function igo(Closure $callback) {
		if (!isCo()) {
			$generatorFunc = function () use ($callback) {
				try {
					yield $callback();
				} catch (Throwable $e) {
					Logger::debug($e->getMessage(), ['exception' => $e]);
				}
			};
			Container::singleton(\W7\Core\Helper\Compate\Coroutine::class)->add($generatorFunc());
			return true;
		}

		$coId =Context::getCoroutineId();
		$result = null;
		Coroutine::create(function () use ($callback, $coId, &$result) {
			Context::fork($coId);
			try {
				$result = $callback();
			} catch (Throwable $throwable) {
				Logger::debug('igo error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				Context::destroy();
			});
		});
		return $result;
	}
}
if (!function_exists('isleep')) {
	function isleep($seconds) {
		if (!isCo()) {
			sleep($seconds);
			return true;
		}
		\Swoole\Coroutine\System::sleep($seconds);
	}
}
if (!function_exists('itimeTick')) {
	function itimeTick($ms, \Closure $callback) {
		Timer::tick($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				Logger::debug('timer-tick error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				Context::destroy();
			});
		});
	}
}
if (!function_exists('itimeAfter')) {
	function itimeAfter($ms, \Closure $callback) {
		Timer::after($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				Logger::debug('time-after error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				Context::destroy();
			});
		});
	}
}
if (!function_exists('isCli')) {
	function isCli() {
		return PHP_SAPI == 'cli';
	}
}
