<?php
/**
 * @author donknap
 * @date 18-11-24 下午9:40
 */

namespace W7\Core\Message;


abstract class MessageAbstract {
	abstract public function pack();
	static public function unpack($data) {
		return new static(unserialize($data));
	}
}