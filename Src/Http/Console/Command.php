<?php
/**
 * @author donknap
 * @date 18-7-19 下午3:56
 */

namespace W7\Http\Console;

use W7\Core\Command\CommandAbstract;
use W7\Http\Server\Server;

class Command extends CommandAbstract {
	public function createServer() {
		$server = new Server();
		return $server;
	}
}
