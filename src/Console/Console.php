<?php
/**
 * 控制台管理
 * 根据config中注册的服务配置，路由到相应的服务组件处理
 * @author donknap
 * @date 18-7-19 上午10:04
 */

namespace W7\Console;

use W7\Core\Base\CommendBase;
use W7\Core\Base\CommendInterface;
use W7\Core\Exception\CommendException;

class Console {
	private $allowServer;

	public function __construct() {

	}

	public function run() {
		$this->checkEnv();

		$input = iloader()->singleton(\W7\Console\Io\Input::class);
		$commend = $input->getCommend();

		$supportServer = $this->supportServer();
		if (!in_array($commend['server'], $supportServer)) {
			throw new CommendException(sprintf('暂不支持此服务 %s', $commend['server']));
			return false;
		}

		$server = $this->getServer($commend['server']);
		if (!method_exists($server, $commend['action'])) {
			throw new CommendException(sprintf('暂不支持该启动操作 %s ', $commend['action']));
		}

		call_user_func_array(array($server, $commend['action']), $commend['option']);
		return true;
	}

	private function checkEnv() {
		if (PHP_SAPI !== 'cli') {
			throw new \RuntimeException('服务必须运行在Cli模式下');
		}
		if (!version_compare(PHP_VERSION, '7.0')) {
			throw new \RuntimeException('当前Php版本必须是7.0.0及以上');
		}

		if (!\extension_loaded('swoole')) {
			throw new \RuntimeException('缺少Swoole扩展，请安装最新版');
		}

		if (!class_exists('Swoole\Coroutine')) {
			throw new \RuntimeException("未启用Swoole Coroutine，编译时请附加'--enable-coroutine'参数");
		}
	}

	private function getServer($name) {
		$className = sprintf("\\W7\\%s\\Console\\Commend", istudly($name));
		$object = new $className();
		if (!($object instanceof CommendInterface)) {
			throw new CommendException('启动命令必须继续CommendBase类');
		}
		return $object;
	}

	/**
	 * 获取当前支持哪些服务，主是看config/server.php中是否定义服务配置
	 * @return array
	 * @throws \Exception
	 */
	private function supportServer() {
		$result = [];
		$setting = \iconfig()->getServer();

		if (empty($setting)) {
			throw new \Exception('配置文件中未定义服务信息');
		}
		foreach ($setting as $serverName => $config) {
			if ($serverName == 'common' || empty($config['host']) || empty($config['port'])) {
				continue;
			}
			$result[] = $serverName;
		}
		return $result;
	}
}
