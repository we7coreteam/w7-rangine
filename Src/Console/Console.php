<?php
/**
 * 控制台管理
 * 根据config中注册的服务配置，路由到相应的服务组件处理
 * @author donknap
 * @date 18-7-19 上午10:04
 */

namespace W7\Console;

use Symfony\Component\Console\Application;
use W7\Console\Command\ConfigCommand;
use W7\Console\Command\DefaultCommand;
use W7\Console\Command\RouteCommand;
use W7\Console\Command\ServerCommand;

class Console {
	public function run() {
		$console = new Application();

		$commands = glob(__DIR__  . '/Command/*/' . '*Command.php');
		$systemCommands = [];
		foreach ($commands as $key => &$item) {
			$item = str_replace(__DIR__, '', $item);
			$item = str_replace('.php', '', $item);
			$item = str_replace('/', '\\', $item);

			$info = explode('\\', $item);
			$name = substr($info[3], 0, strlen($info[3]) - 7);
			$name = strtolower($info[2] . ':' . $name);

			$systemCommands[$name] = "\\W7\\Console" . $item;
		}

		$userCommands = iconfig()->getUserConfig('command');
		$commands = array_merge($systemCommands, $userCommands);

		foreach ($commands as $name => $class) {
			$commandObj = new $class($name);
			$console->add($commandObj);
		}

		$defaultCommand = new DefaultCommand('default');
		$console->add($defaultCommand);
		$console->setDefaultCommand('default');
		$console->run();

		return true;
	}
}
