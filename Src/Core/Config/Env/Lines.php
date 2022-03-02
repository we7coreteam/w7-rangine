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

namespace W7\Core\Config\Env;

class Lines extends \Dotenv\Lines {
	/**
	 * Process the array of lines of environment variables.
	 *
	 * This will produce an array of entries, one per variable.
	 *
	 * @param string[] $lines
	 *
	 * @return string[]
	 */
	public static function process(array $lines) {
		foreach ($lines as &$line) {
			$pattern = '/^include\(([\.\w]+)\)/';
			if (preg_match($pattern, $line, $result) && !empty($result[1])) {
				$line .= ' = ';
			}
		}

		return parent::process($lines);
	}
}
