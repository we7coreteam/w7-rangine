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
use W7\Core\Dispatcher\EventDispatcher;
use W7\Core\Dispatcher\TaskDispatcher;
use W7\Core\Exception\DumpException;
use W7\Core\Exception\ValidatorException;
use W7\Console\Io\Output;
use W7\Core\Message\TaskMessage;
use Illuminate\Database\Eloquent\Model;
use W7\Core\Route\Route;
use Swoole\Timer;

if (!function_exists('ieventDispatcher')) {
	function ieventDispatcher() {
		/**
		 * @var EventDispatcher $dispatcher
		 */
		$dispatcher = iloader()->get(EventDispatcher::class);
		return $dispatcher;
	}
}

if (!function_exists('ievent')) {
	/**
	 * 派发一个事件
	 * @param $eventName
	 * @param array $args
	 * @param bool $halt
	 * @return array|null
	 */
	function ievent($eventName, $args = [], $halt = false) {
		return ieventDispatcher()->dispatch($eventName, $args, $halt);
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
		$dispatcherMaker = iloader()->get(TaskDispatcher::class);
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
		$dispatcherMaker = iloader()->get(TaskDispatcher::class);
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
		$prefix = md5(substr(md5(icontext()->getCoroutineId()), $len));
		return uniqid($prefix);
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
	 * @return \W7\Core\Container\Container
	 */
	function icontainer() {
		return App::getApp()->getContainer();
	}
}

if (!function_exists('ioutputer')) {
	/**
	 * 获取输出对象
	 * @return W7\Console\Io\Output
	 */
	function ioutputer() {
		return iloader()->get(Output::class);
	}
}

if (!function_exists('iconfig')) {
	/**
	 * 输入对象
	 * @return W7\Core\Config\Config
	 */
	function iconfig() {
		return App::getApp()->getConfigger();
	}
}

if (!function_exists('ilogger')) {
	/**
	 * 返回logger对象
	 * @return \W7\Core\Log\Logger
	 */
	function ilogger() {
		return App::getApp()->getLogger();
	}
}

if (!function_exists('idb')) {
	/**
	 * 返回一个数据库连接对象
	 * @return \W7\Core\Database\DatabaseManager
	 */
	function idb() {
		return Model::getConnectionResolver();
	}
}

if (!function_exists('icontext')) {
	/**
	 * 返回logger对象
	 * @return \W7\Core\Helper\Storage\Context
	 */
	function icontext() {
		return App::getApp()->getContext();
	}
}

if (!function_exists('icache')) {
	/**
	 * @return \W7\Core\Cache\Cache
	 */
	function icache() {
		return App::getApp()->getCacher();
	}
}

if (!function_exists('irouter')) {
	/**
	 * @return \W7\Core\Route\Route
	 */
	function irouter() {
		return iloader()->get(Route::class);
	}
}

if (!function_exists('isCo')) {
	/**
	 * 是否是在协成
	 * @return bool
	 */
	function isCo():bool {
		return icontext()->getCoroutineId() > 0;
	}
}

if (!function_exists('getClientIp')) {
	function getClientIp() {
		$request = App::getApp()->getContext()->getRequest();

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
			$ip = $request->getHeader('X-Forwarded-For');
		} elseif ($request->getHeader('X-Real-IP')) {
			$ip = $request->getHeader('X-Real-IP');
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

		if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
			return substr($value, 1, -1);
		}

		return $value;
	}
}
if (!function_exists('igo')) {
	function igo(Closure $callback) {
		$coId = icontext()->getCoroutineId();
		Coroutine::create(function () use ($callback, $coId) {
			icontext()->fork($coId);
			try {
				$callback();
			} catch (Throwable $throwable) {
				ilogger()->debug('igo error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				icontext()->destroy();
			});
		});
	}
}
if (!function_exists('ivalidate')) {
	function ivalidate(array $data, array $rules, array $messages = [], array $customAttributes = []) {
		try {
			/**
			 * @var Factory $validate
			 */
			$validate = ivalidator();
			$result = $validate->make($data, $rules, $messages, $customAttributes)
				->validate();
		} catch (ValidationException $e) {
			$errorMessage = [];
			$errors = $e->errors();
			foreach ($errors as $field => $message) {
				$errorMessage[] = $field . ' : ' . $message[0];
			}
			throw new ValidatorException(implode('; ', $errorMessage), 403);
		}

		return $result;
	}
}
if (!function_exists('ivalidator')) {
	function ivalidator() : Factory {
		$validator = iloader()->get(Factory::class);
		return $validator;
	}
}
if (!function_exists('itimeTick')) {
	function itimeTick($ms, \Closure $callback) {
		Timer::tick($ms, function () use ($callback) {
			try {
				$callback();
			} catch (Throwable $throwable) {
				ilogger()->debug('timer-tick error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				icontext()->destroy();
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
				ilogger()->debug('time-after error with msg ' . $throwable->getMessage() . ' in file ' . $throwable->getFile() . ' at line ' . $throwable->getLine());
			}

			Coroutine::defer(function () {
				icontext()->destroy();
			});
		});
	}
}
