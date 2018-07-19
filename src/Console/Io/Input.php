<?php
/**
 * 处理控制台的输入，分隔命令
 * @author donknap
 * @date 18-7-19 上午10:17
 */

namespace W7\Console\Io;

class Input {
	public function getCommend($argv = null) {
		$result = [];
		if (null === $argv) {
			$argv = $_SERVER['argv'];
		}
		$parser = iloader()->singleton(\W7\Console\Io\Parser::class);
		$commend = $parser->parse($argv);
		list($temp, $result['server'], $result['action']) = $commend[0];
		$result['option'] = array_merge([], $commend[1], $commend[2]);
		return $result;
	}
}