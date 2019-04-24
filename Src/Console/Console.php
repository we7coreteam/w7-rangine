<?php
/**
 * 控制台管理
 * 根据config中注册的服务配置，路由到相应的服务组件处理
 * @author donknap
 * @date 18-7-19 上午10:04
 */

namespace W7\Console;

use W7\Console\Command\CommandAbstract;
use W7\Console\Command\DefaultCommand;
use W7\Console\Io\Input;
use W7\Core\Helper\StringHelper;

class Console {
	public function run() {
		\ioutputer()->writeLogo();

		$input = iloader()->singleton(Input::class);
		$command = $input->getCommand();

		$commandInstance = $this->getCommandInstance($command['command']);
		$commandInstance->run($command);

		return true;
	}

	private function getCommandInstance($command) : CommandAbstract {
		if (!$command) {
			$command = 'default';
		}
		$className = sprintf("\\W7\\Console\\Command\\%s", StringHelper::studly($command) . 'Command');
		if (!class_exists($className)) {
			$className = DefaultCommand::class;
			\ioutputer()->writeln('The ' . $command . ' command not found');
		}
		
		return new $className();
	}
}
