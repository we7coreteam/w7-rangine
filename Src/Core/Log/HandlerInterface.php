<?php
/**
 * @author donknap
 * @date 18-10-18 下午4:26
 */

namespace W7\Core\Log;


interface HandlerInterface {
	public function getHandler($config);
}