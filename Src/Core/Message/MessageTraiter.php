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

namespace W7\Core\Message;

trait MessageTraiter {
	public static function unpack($data) {
		$dataTmp = unserialize($data);
		if (empty($dataTmp) || !is_array($dataTmp) || empty($dataTmp['class'])) {
			return null;
		}

		$message = new $dataTmp['class']($dataTmp);
		foreach ($dataTmp as $name => $value) {
			$message->$name = $value;
		}
		return $message;
	}
}
