<?php
/**
 * 控制台管理
 * 根据config中注册的服务配置，路由到相应的服务组件处理
 * @author donknap
 * @date 18-7-19 上午10:04
 */

namespace W7\Console;


use W7\Core\Base\CommandInterface;
use W7\Core\Exception\CommandException;

class Console {
	private $allowServer;

	public function __construct() {

	}

	public function run() {
		$this->checkEnv();
		/**
		 * @var \W7\Console\Io\Input $input
		 */
		$input = iloader()->singleton(\W7\Console\Io\Input::class);

		if ($input->isVersionCommand()) {
			$this->showVersionCommand();
			return false;
		}

		$command = $input->getCommand();
		if (empty($command['command']) && empty($command['action'])) {
			$this->showDefaultCommand();
			return false;
		}


		$supportServer = $this->supportServer();
		if (!in_array($command['command'], $supportServer)) {
			\ioutputer()->writeln(sprintf('暂不支持此服务 %s', $command['command']), true);
			$this->showDefaultCommand();
			return false;
		}

		$server = $this->getServer($command['command']);
		if (!method_exists($server, $command['action'])) {
			\ioutputer()->writeln(sprintf('暂不支持该启动操作 %s', $command['action']), true);
			$this->showDefaultCommand();
			return false;
		}

		call_user_func_array(array($server, $command['action']), $command['option']);
		return true;
	}

	private function showDefaultCommand() {


		$script = 'bin/server.php';
		$commandList = [
			'Usage:' => ["php $script {command} [arguments] [options]"],
			'Commands:' => [],
			'Arguments:' => [
				'start' => 'Start the service.',
				'stop' => 'Stop the service.',
				'restart' => 'Restart the service',
			],
			'Options:' => [
				'-h, --help'    => 'Display help information',
				'-v, --version' => 'Display version information',
			]
		];

		$server = $this->supportServer();
		foreach ($server as $item) {
			$commandList['Commands:'][$item] = 'Start the ' . $item . ' service.';
		}

		\ioutputer()->writeLogo();
		\ioutputer()->writeList($commandList, 'comment', 'info');
		return true;
	}

	private function showVersionCommand() {
		$frameworkVersion = \iconfig()::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;

		\ioutputer()->writeLogo();
		\ioutputer()->writeln("framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion\n", true);
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
		$className = sprintf("\\W7\\%s\\Console\\Command", istudly($name));
		$object = new $className();
		if (!($object instanceof CommandInterface)) {
			throw new CommandException('启动命令必须继续CommandInterface类');
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
