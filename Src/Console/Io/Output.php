<?php
/**
 * 处理控制台输出
 * @author Swoft\Console\Output
 * @date 18-7-19 上午10:18
 */

namespace W7\Console\Io;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

class Output extends SymfonyStyle {
	/**
	 * 间隙字符
	 */
	const GAP_CHAR = '  ';

	/**
	 * 左边字符
	 */
	const LEFT_CHAR = '  ';


	private function writeKey($key) {
		echo "\033[0;32m$key \e[0m";
	}

	public function table(array $headers, array $rows) {
		$table = new Table($this);
		$table->setHeaders($headers);
		$table->setRows($rows);
		$table->render();
	}

	/**
	 * 输出一个列表
	 *
	 * @param array	   $list	   列表数据
	 * @param string	  $titleStyle 标题样式
	 * @param string	  $cmdStyle   命令样式
	 * @param string|null $descStyle  描述样式
	 */
	public function writeList($list) {
		foreach ($list as $title => $items) {
			// 标题
			$title = "$title";
			$this->writeln($title);

			// 输出块内容
			$this->writeItems((array)$items);
			$this->writeln('');
		}
	}

	/**
	 * 显示命令列表一块数据
	 *
	 * @param array  $items	数据
	 */
	private function writeItems($items, $level = 1) {
		foreach ($items as $cmd => $desc) {
			// 没有命令，只是一行数据
			if (\is_int($cmd)) {
				$cmd = '';
			}

			$maxLength = $this->getCmdMaxLength(array_keys($items));
			$cmd = \str_pad($cmd, $maxLength, ' ');
			$this->writeKey($this->writeLeft($level) . $cmd);

			if (is_array($desc)) {
				if (!empty($desc)) {
					$this->writeln('');
					$this->writeItems($desc, $level + 1);
					continue;
				} else {
					$desc = '[]';
				}
			}

			if ($desc === false) {
				$desc = 'false';
			}
			if ($desc === true) {
				$desc = 'true';
			}

			$this->writeln(self::GAP_CHAR . $desc);
		}
	}

	private function writeLeft($level) {
		$left = '';
		for ($i = 0; $i < $level; $i++) {
			$left .= self::LEFT_CHAR;
		}
		return $left;
	}

	/**
	 * 所有命令最大宽度
	 *
	 * @param array $commands 所有命令
	 * @return int
	 */
	private function getCmdMaxLength($commands) {
		$max = 0;

		foreach ($commands as $cmd) {
			$length = \strlen($cmd);
			if ($length > $max) {
				$max = $length;
				continue;
			}
		}

		return $max;
	}
}
