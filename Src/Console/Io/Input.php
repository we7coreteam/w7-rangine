<?php
/**
 * 处理控制台的输入，分隔命令
 * @author donknap
 * @date 18-7-19 上午10:17
 */

namespace W7\Console\Io;

class Input {

	public function getCommand($argv = null) {
		$result = [];
		if (null === $argv) {
			$argv = $_SERVER['argv'];
		}
		/**
		 * @var \W7\Console\Io\Parser $parser
		 */
		$parser = iloader()->singleton(\W7\Console\Io\Parser::class);
		$command = $parser->parse($argv);
		list($temp, $result['command'], $result['action']) = $command[0];
		$result['option'] = array_merge([], $command[1], $command[2]);
		return $result;
	}
}